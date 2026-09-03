<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\Reservation;
use App\Models\Setting;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\TournamentRegistration;
use App\Services\ImageResizer;
use App\Services\TournamentNotifier;
use App\Services\TournamentRegistrar;
use App\Services\TournamentStandings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Gestione dei tornei dal back office: anagrafica, iscritti,
 * calendario incontri e risultati.
 */
class TournamentController extends Controller
{
    /** Lato massimo della copertina, in pixel. */
    private const COVER_MAX = 1400;

    private const COVER_FOLDER = 'tournaments';

    public function __construct(
        private TournamentRegistrar $registrar,
        private TournamentStandings $standings,
        private TournamentNotifier $notifier,
        private ImageResizer $resizer
    ) {
    }

    /** I campi configurati in impostazioni: le chiavi di field_set. */
    private function fieldSet(): array
    {
        return Setting::fieldSet();
    }

    /**
     * Due tornei possono stare negli stessi giorni solo su campi diversi.
     * Blocca il salvataggio spiegando con chi e su quali campi si accavalla.
     */
    private function guardAgainstClashes(array $data, ?Tournament $tournament = null): void
    {
        $clashes = Tournament::clashes(
            $data['fields'],
            $data['starts_at'],
            $data['ends_at'] ?? null,
            $tournament?->id
        );

        if ($clashes->isEmpty()) {
            return;
        }

        $messaggi = $clashes->map(function (Tournament $t) use ($data) {
            $comuni = implode(', ', array_intersect($t->occupiedFields(), $data['fields']));
            $dal = $t->starts_at?->format('d/m/Y');
            $al = $t->ends_at?->format('d/m/Y');

            return '"'.$t->name.'" occupa già '.$comuni.' '
                .($al && $al !== $dal ? 'dal '.$dal.' al '.$al : 'il '.$dal).'.';
        })->all();

        throw ValidationException::withMessages([
            'fields' => array_merge(
                ['In queste date i campi scelti sono già impegnati da un altro torneo.'],
                $messaggi
            ),
        ]);
    }

    private function rules(?Tournament $tournament = null): array
    {
        return [
            'name' => 'required|string|min:3|max:120',
            'description' => 'nullable|string|max:5000',
            'regulation' => 'nullable|string|max:5000',
            'type' => 'nullable|string|max:40',
            'format' => 'required|in:'.implode(',', Tournament::FORMATS),
            'level_min' => 'nullable|integer|min:1|max:5',
            'level_max' => 'nullable|integer|min:1|max:5|gte:level_min',
            'teams_max' => 'required|integer|min:2|max:128',
            'is_pair' => 'nullable|boolean',
            'price' => 'nullable|numeric|min:0|max:99999',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'registration_opens_at' => 'nullable|date',
            'registration_closes_at' => 'nullable|date|after_or_equal:registration_opens_at',
            'status' => 'required|in:'.implode(',', Tournament::STATUSES),
            'location' => 'nullable|string|max:255',
            'fields' => 'required|array|min:1',
            'fields.*' => ['required', 'string', Rule::in(array_keys($this->fieldSet()))],
            'note' => 'nullable|string|max:2000',
            'cover' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:6144',
        ];
    }

    // ==========================================================
    // CRUD
    // ==========================================================

    public function index()
    {
        $tournaments = Tournament::withCount([
                'registrations',
                'confirmedRegistrations',
                'waitlistRegistrations',
            ])
            ->orderByDesc('starts_at')
            ->get();

        return view('admin.Tournaments.index', compact('tournaments'));
    }

    public function create()
    {
        $tournament = new Tournament([
            'format' => 'gironi',
            'status' => 'draft',
            'teams_max' => 8,
            'is_pair' => true,
            'type' => 'Padel',
        ]);

        return view('admin.Tournaments.create', [
            'tournament' => $tournament,
            'field_set' => $this->fieldSet(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $this->guardAgainstClashes($data);

        $tournament = new Tournament();
        $this->fill($tournament, $data, $request);
        $tournament->slug = Tournament::makeSlug($data['name']);
        $tournament->save();

        return to_route('admin.tournaments.show', $tournament)
            ->with('message', 'Torneo "'.$tournament->name.'" creato correttamente');
    }

    public function show(Tournament $tournament)
    {
        $tournament->load([
            'registrations' => fn ($q) => $q->with(['player', 'partner'])->orderBy('created_at'),
            'matches' => fn ($q) => $q->with([
                'teamA.player', 'teamA.partner', 'teamB.player', 'teamB.partner', 'reservation',
            ]),
        ]);

        $standings = $this->standings->forTournament($tournament);

        // Squadre selezionabili nel form del calendario.
        $teams = $tournament->registrations
            ->whereIn('status', ['confirmed', 'waitlist'])
            ->values();

        // Slot campo agganciabili: dal giorno prima dell'inizio in poi.
        $from = $tournament->starts_at ? $tournament->starts_at->copy()->subDay()->format('Y-m-d') : now()->format('Y-m-d');
        $reservations = Reservation::where('status', '1')
            ->where('date_slot', '>=', $from)
            ->orderBy('date_slot')
            ->limit(200)
            ->get(['id', 'date_slot', 'field']);

        // Elenco per i form di iscrizione manuale.
        $all_players = Player::orderBy('nickname')->get(['id', 'nickname', 'name', 'surname', 'level']);

        return view('admin.Tournaments.show', compact(
            'tournament', 'standings', 'teams', 'reservations', 'all_players'
        ));
    }

    public function edit(Tournament $tournament)
    {
        return view('admin.Tournaments.edit', [
            'tournament' => $tournament,
            'field_set' => $this->fieldSet(),
        ]);
    }

    public function update(Request $request, Tournament $tournament)
    {
        $data = $request->validate($this->rules($tournament));
        $this->guardAgainstClashes($data, $tournament);

        $this->fill($tournament, $data, $request);

        // Lo slug segue il nome solo finché il torneo non è pubblico:
        // dopo cambierebbe gli URL già condivisi.
        if ($tournament->status === 'draft') {
            $tournament->slug = Tournament::makeSlug($data['name'], $tournament->id);
        }

        if ($request->boolean('remove_cover')) {
            $tournament->deleteCoverFile();
            $tournament->cover = null;
        }

        $tournament->save();

        return to_route('admin.tournaments.show', $tournament)
            ->with('message', 'Torneo aggiornato correttamente');
    }

    public function destroy(Tournament $tournament)
    {
        $name = $tournament->name;
        $tournament->deleteCoverFile();
        // Iscrizioni e incontri hanno cascadeOnDelete a livello di FK.
        $tournament->delete();

        return to_route('admin.tournaments.index')
            ->with('message', 'Torneo "'.$name.'" eliminato');
    }

    // ==========================================================
    // Iscrizioni
    // ==========================================================

    /** Cambio di stato: conferma, rifiuta, lista d'attesa, ritiro. */
    public function registrationStatus(Request $request, Tournament $tournament, TournamentRegistration $registration)
    {
        abort_unless($registration->tournament_id === $tournament->id, 404);

        $request->validate(['status' => 'required|in:pending,confirmed,waitlist,rejected,cancelled']);

        $registration->setRelation('tournament', $tournament);
        $result = $this->registrar->changeStatus($registration, $request->input('status'));

        if (isset($result['error'])) {
            return back()->with('error_message', $result['error']);
        }

        return back()->with('message', 'Iscrizione aggiornata: '.$registration->statusLabel());
    }

    /** Segna la quota come incassata in struttura. */
    public function registrationPaid(Request $request, Tournament $tournament, TournamentRegistration $registration)
    {
        abort_unless($registration->tournament_id === $tournament->id, 404);

        $registration->paid = $request->boolean('paid');
        $registration->save();

        return back()->with('message', $registration->paid ? 'Quota segnata come pagata' : 'Quota segnata come non pagata');
    }

    /** Iscrizione aggiunta a mano dal gestore. */
    public function registrationStore(Request $request, Tournament $tournament)
    {
        $request->validate([
            'player_id' => 'required|exists:players,id',
            'partner_player_id' => 'nullable|exists:players,id|different:player_id',
            'team_name' => 'nullable|string|max:80',
        ]);

        $player = Player::find($request->input('player_id'));
        $partner = $request->input('partner_player_id') ? Player::find($request->input('partner_player_id')) : null;

        $result = $this->registrar->register(
            $tournament,
            $player,
            $partner,
            $request->input('team_name')
        );

        if (isset($result['error'])) {
            return back()->with('error_message', $result['error']);
        }

        return back()->with('message', 'Iscrizione aggiunta');
    }

    // ==========================================================
    // Calendario incontri
    // ==========================================================

    public function matchStore(Request $request, Tournament $tournament)
    {
        $request->validate([
            'round' => 'nullable|string|max:40',
            'group_name' => 'nullable|string|max:40',
            'team_a_id' => 'nullable|exists:tournament_registrations,id',
            'team_b_id' => 'nullable|exists:tournament_registrations,id|different:team_a_id',
            'reservation_id' => 'nullable|exists:reservations,id',
            'played_at' => 'nullable|date',
        ]);

        $match = new TournamentMatch($request->only([
            'round', 'group_name', 'team_a_id', 'team_b_id', 'reservation_id', 'played_at',
        ]));
        $match->tournament_id = $tournament->id;
        $match->position = (int) $tournament->matches()->max('position') + 1;
        $match->status = 'scheduled';

        // Se l'incontro è agganciato a uno slot campo, l'orario lo eredita.
        if (! $match->played_at && $match->reservation_id) {
            $reservation = Reservation::find($match->reservation_id);
            $match->played_at = $reservation?->slotStartsAt();
        }

        $match->save();

        return back()->with('message', 'Incontro aggiunto al calendario');
    }

    public function matchUpdate(Request $request, Tournament $tournament, TournamentMatch $match)
    {
        abort_unless($match->tournament_id === $tournament->id, 404);

        $request->validate([
            'score' => 'nullable|string|max:60',
            'status' => 'nullable|in:scheduled,played,cancelled',
            'played_at' => 'nullable|date',
            'reservation_id' => 'nullable|exists:reservations,id',
        ]);

        if ($request->filled('played_at')) {
            $match->played_at = $request->input('played_at');
        }

        if ($request->has('reservation_id')) {
            $match->reservation_id = $request->input('reservation_id') ?: null;
        }

        if ($request->has('score')) {
            $sets = TournamentMatch::parseScore($request->input('score'));

            if ($request->filled('score') && ! $sets) {
                return back()->with('error_message', 'Punteggio non riconosciuto. Usa il formato "6-4 6-3".');
            }

            $match->score = $sets;
            $match->winner = TournamentMatch::winnerFromScore($sets);
            // Un punteggio inserito segna l'incontro come giocato.
            $match->status = $sets ? 'played' : 'scheduled';
        }

        if ($request->filled('status')) {
            $match->status = $request->input('status');
        }

        $match->save();

        return back()->with('message', 'Incontro aggiornato');
    }

    public function matchDestroy(Tournament $tournament, TournamentMatch $match)
    {
        abort_unless($match->tournament_id === $tournament->id, 404);

        $match->delete();

        return back()->with('message', 'Incontro rimosso dal calendario');
    }

    // ==========================================================
    // Promemoria
    // ==========================================================

    public function reminder(Tournament $tournament)
    {
        $sent = $this->notifier->reminder($tournament);

        return back()->with('message', $sent
            ? 'Promemoria inviato a '.$sent.' squadre confermate'
            : 'Nessuna squadra confermata a cui inviare il promemoria');
    }

    // ==========================================================
    // Helper
    // ==========================================================

    private function fill(Tournament $tournament, array $data, Request $request): void
    {
        $tournament->fill([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'regulation' => $data['regulation'] ?? null,
            'type' => $data['type'] ?? 'Padel',
            'format' => $data['format'],
            'level_min' => $data['level_min'] ?? null,
            'level_max' => $data['level_max'] ?? null,
            'teams_max' => $data['teams_max'],
            'is_pair' => $request->boolean('is_pair'),
            'price' => $data['price'] ?? null,
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'] ?? null,
            'registration_opens_at' => $data['registration_opens_at'] ?? null,
            'registration_closes_at' => $data['registration_closes_at'] ?? null,
            'status' => $data['status'],
            'location' => $data['location'] ?? null,
            'note' => $data['note'] ?? null,
            'fields' => array_values($data['fields']),
        ]);

        if ($request->hasFile('cover')) {
            $binary = $this->resizer->resizeToJpeg($request->file('cover'), self::COVER_MAX);
            $path = self::COVER_FOLDER.'/'.Str::random(16).'.jpg';

            Storage::disk('public')->put($path, $binary, 'public');

            $tournament->deleteCoverFile();
            $tournament->cover = $path;
        }
    }
}
