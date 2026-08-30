<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\Tournament;
use App\Models\TournamentRegistration;
use App\Services\TournamentRegistrar;
use App\Services\TournamentStandings;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Vetrina pubblica dei tornei e iscrizioni dal sito clienti.
 */
class TournamentController extends Controller
{
    public function __construct(
        private TournamentRegistrar $registrar,
        private TournamentStandings $standings
    ) {
    }

    // ==========================================================
    // Lettura
    // ==========================================================

    /**
     * GET api/tournaments
     */
    public function index(Request $request)
    {
        $validator = validator($request->all(), [
            'status'    => ['nullable', Rule::in(Tournament::PUBLIC_STATUSES)],
            'format'    => ['nullable', Rule::in(Tournament::FORMATS)],
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to'   => 'nullable|date_format:Y-m-d',
            'per_page'  => 'nullable|integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ]);
        }

        $query = Tournament::query()
            ->published()
            // Un solo conteggio aggregato: niente query per riga.
            ->withCount(['confirmedRegistrations'])
            ->orderBy('starts_at');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($format = $request->input('format')) {
            $query->where('format', $format);
        }

        if ($from = $request->input('date_from')) {
            $query->whereDate('starts_at', '>=', $from);
        }

        if ($to = $request->input('date_to')) {
            $query->whereDate('starts_at', '<=', $to);
        }

        $page = $query->paginate($request->input('per_page', 12));
        $playerId = $request->input('user_id');

        return response()->json([
            'success' => true,
            'data' => collect($page->items())->map(fn ($t) => $this->summary($t, $playerId)),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    /**
     * GET api/tournaments/{slug}
     * Dettaglio con iscritti confermati, calendario e classifica.
     */
    public function show(Request $request, string $slug)
    {
        $tournament = Tournament::published()->where('slug', $slug)->first();

        if (! $tournament) {
            return response()->json([
                'success' => false,
                'message' => 'Torneo non trovato',
            ], 404);
        }

        $tournament->loadCount('confirmedRegistrations');
        $tournament->load([
            'registrations' => fn ($q) => $q->whereIn('status', ['confirmed', 'waitlist'])
                ->with(['player:id,name,surname,nickname,level,img', 'partner:id,name,surname,nickname,level,img'])
                ->orderBy('created_at'),
            'matches' => fn ($q) => $q->with([
                'teamA.player:id,nickname', 'teamA.partner:id,nickname',
                'teamB.player:id,nickname', 'teamB.partner:id,nickname',
                'reservation:id,date_slot,field',
            ]),
        ]);

        $playerId = $request->input('user_id');

        $detail = $this->summary($tournament, $playerId);
        $detail['description'] = $tournament->description;
        $detail['regulation'] = $tournament->regulation;
        $detail['note'] = $tournament->note;
        $detail['teams'] = $this->teams($tournament);
        $detail['schedule'] = $this->schedule($tournament);
        $detail['standings'] = $this->standings->forTournament($tournament);
        $detail['my_registration'] = $this->myRegistration($tournament, $playerId);

        return response()->json([
            'success' => true,
            'data' => $detail,
        ]);
    }

    // ==========================================================
    // Iscrizione
    // ==========================================================

    /**
     * POST api/tournaments/{slug}/register
     */
    public function register(Request $request, string $slug)
    {
        $tournament = Tournament::where('slug', $slug)->first();

        if (! $tournament) {
            return response()->json(['success' => false, 'message' => 'Torneo non trovato'], 404);
        }

        $player = Player::find($request->input('user_id'));

        if (! $player) {
            return response()->json(['success' => false, 'message' => 'Utente non trovato']);
        }

        $validator = validator($request->all(), [
            'partner_nickname' => 'nullable|string|max:50',
            'team_name' => 'nullable|string|max:80',
            'note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ]);
        }

        $partner = null;

        if ($nickname = $request->input('partner_nickname')) {
            $partner = Player::where('nickname', $nickname)->first();

            if (! $partner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nessun giocatore trovato con il nickname "'.$nickname.'". Deve essere già registrato.',
                ]);
            }
        }

        $result = $this->registrar->register(
            $tournament,
            $player,
            $partner,
            $request->input('team_name'),
            $request->input('note')
        );

        if (isset($result['error'])) {
            return response()->json([
                'success' => false,
                'message' => $result['error'],
            ]);
        }

        $registration = $result['registration'];

        return response()->json([
            'success' => true,
            'waitlist' => $registration->status === 'waitlist',
            'message' => $registration->status === 'waitlist'
                ? 'Il torneo è al completo: sei in lista d\'attesa e ti avvisiamo se si libera un posto.'
                : 'Iscrizione registrata. Ti aspettiamo!',
            'data' => $this->registrationPayload($registration),
        ]);
    }

    /**
     * DELETE api/tournaments/{slug}/register
     * Ritiro consentito entro registration_closes_at.
     */
    public function withdraw(Request $request, string $slug)
    {
        $tournament = Tournament::where('slug', $slug)->first();

        if (! $tournament) {
            return response()->json(['success' => false, 'message' => 'Torneo non trovato'], 404);
        }

        $playerId = (int) $request->input('user_id');

        $registration = TournamentRegistration::where('tournament_id', $tournament->id)
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->where(function ($q) use ($playerId) {
                $q->where('player_id', $playerId)->orWhere('partner_player_id', $playerId);
            })
            ->first();

        if (! $registration) {
            return response()->json([
                'success' => false,
                'message' => 'Non risulti iscritto a questo torneo',
            ]);
        }

        if ($tournament->registration_closes_at && $tournament->registration_closes_at->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Le iscrizioni sono chiuse: contatta la struttura per ritirarti',
            ]);
        }

        $registration->setRelation('tournament', $tournament);
        $this->registrar->withdraw($registration);

        return response()->json([
            'success' => true,
            'message' => 'Iscrizione ritirata',
        ]);
    }

    /**
     * POST api/account/tournaments
     * I tornei a cui l'utente è iscritto, con il prossimo incontro.
     */
    public function mine(Request $request)
    {
        $playerId = (int) $request->input('user_id');

        if (! $playerId) {
            return response()->json(['success' => false, 'message' => 'Utente non identificato']);
        }

        $registrations = TournamentRegistration::whereNotIn('status', ['cancelled', 'rejected'])
            ->where(function ($q) use ($playerId) {
                $q->where('player_id', $playerId)->orWhere('partner_player_id', $playerId);
            })
            ->with([
                'player:id,nickname', 'partner:id,nickname',
                'tournament' => fn ($q) => $q->withCount('confirmedRegistrations'),
            ])
            ->get();

        $ids = $registrations->pluck('id');

        // Prossimo incontro per ciascuna squadra: una sola query per tutte.
        $nextMatches = \App\Models\TournamentMatch::where('status', 'scheduled')
            ->where(fn ($q) => $q->whereIn('team_a_id', $ids)->orWhereIn('team_b_id', $ids))
            ->where(fn ($q) => $q->whereNull('played_at')->orWhere('played_at', '>=', now()))
            ->with(['teamA.player:id,nickname', 'teamA.partner:id,nickname', 'teamB.player:id,nickname', 'teamB.partner:id,nickname'])
            ->orderBy('played_at')
            ->get();

        $data = $registrations
            ->filter(fn ($r) => $r->tournament && in_array($r->tournament->status, Tournament::PUBLIC_STATUSES, true))
            ->map(function ($r) use ($nextMatches, $playerId) {
                $next = $nextMatches->first(fn ($m) => $m->team_a_id === $r->id || $m->team_b_id === $r->id);

                return [
                    'registration' => $this->registrationPayload($r),
                    'tournament' => $this->summary($r->tournament, $playerId),
                    'next_match' => $next ? $this->matchPayload($next) : null,
                ];
            })
            ->sortBy(fn ($row) => $row['tournament']['starts_at'])
            ->values();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    // ==========================================================
    // Serializzazione
    // ==========================================================

    private function summary(Tournament $t, $playerId = null): array
    {
        return [
            'id' => $t->id,
            'name' => $t->name,
            'slug' => $t->slug,
            'cover_url' => $t->cover_url,
            'type' => $t->type,
            'format' => $t->format,
            'format_label' => $t->formatLabel(),
            'status' => $t->status,
            'status_label' => $t->statusLabel(),
            'is_pair' => $t->is_pair,
            'teams_max' => $t->teams_max,
            'teams_count' => $t->teams_count,
            'spots_left' => $t->spots_left,
            'is_full' => $t->is_full,
            'registration_open' => $t->registration_open,
            'level_min' => $t->level_min,
            'level_max' => $t->level_max,
            'level_label' => $t->levelLabel(),
            'price' => $t->price !== null ? (float) $t->price : null,
            'location' => $t->location,
            'starts_at' => optional($t->starts_at)->format('Y-m-d H:i'),
            'ends_at' => optional($t->ends_at)->format('Y-m-d H:i'),
            'registration_opens_at' => optional($t->registration_opens_at)->format('Y-m-d H:i'),
            'registration_closes_at' => optional($t->registration_closes_at)->format('Y-m-d H:i'),
        ];
    }

    /** Squadre iscritte, confermate e in lista d'attesa. */
    private function teams(Tournament $t): array
    {
        return $t->getRelation('registrations')->map(fn ($r) => [
            'id' => $r->id,
            'name' => $r->displayName(),
            'status' => $r->status,
            'status_label' => $r->statusLabel(),
            'players' => collect([$r->player, $r->partner])->filter()->map(fn ($p) => [
                'id' => $p->id,
                'nickname' => $p->nickname,
                'name' => $p->name,
                'surname' => $p->surname,
                'level' => $p->level,
                'img_url' => $p->img_url,
            ])->values(),
        ])->values()->all();
    }

    /** Calendario raggruppato per round. */
    private function schedule(Tournament $t): array
    {
        return $t->getRelation('matches')
            ->groupBy(fn ($m) => $m->round ?: 'Calendario')
            ->map(fn ($group, $round) => [
                'round' => $round,
                'matches' => $group->map(fn ($m) => $this->matchPayload($m))->values()->all(),
            ])
            ->values()
            ->all();
    }

    private function matchPayload($m): array
    {
        return [
            'id' => $m->id,
            'round' => $m->round,
            'group' => $m->group_name,
            'team_a' => $m->teamA?->displayName(),
            'team_b' => $m->teamB?->displayName(),
            'team_a_id' => $m->team_a_id,
            'team_b_id' => $m->team_b_id,
            'score' => $m->scoreLabel(),
            'winner' => $m->winner,
            'status' => $m->status,
            'played_at' => optional($m->played_at)->format('Y-m-d H:i'),
            'field' => $m->relationLoaded('reservation') ? $m->reservation?->field : null,
        ];
    }

    /** Iscrizione dell'utente corrente, se presente. */
    private function myRegistration(Tournament $t, $playerId): ?array
    {
        if (! $playerId) {
            return null;
        }

        $registration = TournamentRegistration::where('tournament_id', $t->id)
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->where(function ($q) use ($playerId) {
                $q->where('player_id', $playerId)->orWhere('partner_player_id', $playerId);
            })
            ->with(['player:id,nickname', 'partner:id,nickname'])
            ->first();

        return $registration ? $this->registrationPayload($registration) : null;
    }

    private function registrationPayload(TournamentRegistration $r): array
    {
        return [
            'id' => $r->id,
            'team_name' => $r->displayName(),
            'status' => $r->status,
            'status_label' => $r->statusLabel(),
            'paid' => (bool) $r->paid,
            'partner' => $r->partner ? [
                'id' => $r->partner->id,
                'nickname' => $r->partner->nickname,
            ] : null,
        ];
    }
}
