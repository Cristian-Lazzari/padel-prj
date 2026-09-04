<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\Reservation;
use App\Models\Setting;
use App\Services\OpenMatchNotifier;
use App\Services\OpenMatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Partite aperte: pubblicazione di una prenotazione come "aperta"
 * e iscrizione/disiscrizione degli altri giocatori.
 */
class OpenMatchController extends Controller
{
    public function __construct(
        private OpenMatchNotifier $notifier,
        private OpenMatchService $openMatches
    ) {
    }

    // ==========================================================
    // Lettura
    // ==========================================================

    /**
     * GET api/open-matches
     * Lista paginata delle partite aperte con filtri.
     */
    public function index(Request $request)
    {
        $validator = validator($request->all(), [
            'category'  => ['nullable', Rule::in(Reservation::CATEGORIES)],
            'date'      => 'nullable|date_format:Y-m-d',
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to'   => 'nullable|date_format:Y-m-d',
            'field'     => 'nullable|string|max:255',
            'level'     => 'nullable|integer|min:1|max:5',
            'per_page'  => 'nullable|integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ]);
        }

        $query = Reservation::query()
            ->openPublished()
            // Una sola query per gli owner e una per i conteggi: niente N+1.
            ->with(['owner:id,name,surname,nickname,level,img'])
            ->withCount('acceptedPlayers')
            ->orderBySlot();

        if ($category = $request->input('category')) {
            $query->where('open_category', $category);
        }

        if ($field = $request->input('field')) {
            $query->where('field', $field);
        }

        if ($date = $request->input('date')) {
            $query->where('date_slot', 'LIKE', $date.'%');
        }

        if ($from = $request->input('date_from')) {
            $query->whereRaw(Reservation::SLOT_AS_DATETIME.' >= ?', [$from.' 00:00:00']);
        }

        if ($to = $request->input('date_to')) {
            $query->whereRaw(Reservation::SLOT_AS_DATETIME.' <= ?', [$to.' 23:59:59']);
        }

        // "Livello" filtra le partite a cui quel livello può iscriversi.
        if ($level = $request->input('level')) {
            $query->where(function ($q) use ($level) {
                $q->whereNull('level_min')->orWhere('level_min', '<=', $level);
            })->where(function ($q) use ($level) {
                $q->whereNull('level_max')->orWhere('level_max', '>=', $level);
            });
        }

        $page = $query->paginate($request->input('per_page', 12));
        $fieldSet = $this->fieldSet();
        $playerId = $request->input('user_id');

        $items = collect($page->items())->map(
            fn (Reservation $r) => $this->summary($r, $fieldSet, $playerId)
        );

        return response()->json([
            'success' => true,
            'data' => $items,
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    /**
     * GET api/open-matches/{reservation}
     * Dettaglio con l'elenco degli iscritti.
     */
    public function show(Request $request, Reservation $reservation)
    {
        if (! $reservation->is_open) {
            return response()->json([
                'success' => false,
                'message' => 'Questa partita non è aperta alle iscrizioni',
            ], 404);
        }

        $reservation->load([
            'owner:id,name,surname,nickname,level,img',
            'players:id,name,surname,nickname,level,img',
        ]);

        $playerId = $request->input('user_id');
        $detail = $this->summary($reservation, $this->fieldSet(), $playerId);
        $detail['participants'] = $this->participants($reservation);
        $detail['note'] = $reservation->open_note;
        $detail['message'] = $reservation->message;

        return response()->json([
            'success' => true,
            'data' => $detail,
        ]);
    }

    // ==========================================================
    // Iscrizione
    // ==========================================================

    /**
     * POST api/open-matches/{reservation}/join
     *
     * Tutte le verifiche che dipendono dai posti stanno dentro una
     * transazione con lock sulla prenotazione: due richieste concorrenti
     * sull'ultimo posto vengono serializzate e la seconda viene respinta.
     */
    public function join(Request $request, Reservation $reservation)
    {
        $player = Player::find($request->input('user_id'));

        if (! $player) {
            return response()->json([
                'success' => false,
                'message' => 'Utente non trovato',
            ]);
        }

        try {
            $result = DB::transaction(function () use ($reservation, $player) {
                /** @var Reservation $locked */
                $locked = Reservation::whereKey($reservation->id)->lockForUpdate()->first();

                if (! $locked || ! $locked->is_open || $locked->status !== Reservation::STATUS_CONFIRMED) {
                    return ['error' => 'Questa partita non è più aperta alle iscrizioni'];
                }

                if (! $locked->joinWindowIsOpen()) {
                    return ['error' => 'Le iscrizioni per questa partita sono chiuse'];
                }

                if (! $locked->levelAllows($player->level)) {
                    return ['error' => 'Il tuo livello non rientra in quello richiesto per questa partita ('.$locked->levelLabel().')'];
                }

                $existing = DB::table('player_reservation')
                    ->where('reservation_id', $locked->id)
                    ->where('player_id', $player->id)
                    ->first();

                if ($existing && $existing->join_status === 'accepted') {
                    return ['error' => 'Sei già iscritto a questa partita'];
                }

                // Il conteggio avviene sotto lock: è il punto che impedisce l'overbooking.
                $taken = DB::table('player_reservation')
                    ->where('reservation_id', $locked->id)
                    ->where('join_status', 'accepted')
                    ->count();

                if ($taken >= $locked->slots_total) {
                    return ['error' => 'Posti esauriti: qualcuno si è appena iscritto'];
                }

                if ($existing) {
                    // Riattiva una vecchia iscrizione annullata.
                    DB::table('player_reservation')
                        ->where('reservation_id', $locked->id)
                        ->where('player_id', $player->id)
                        ->update(['join_status' => 'accepted', 'joined_at' => now()]);
                } else {
                    DB::table('player_reservation')->insert([
                        'reservation_id' => $locked->id,
                        'player_id' => $player->id,
                        'join_status' => 'accepted',
                        'joined_at' => now(),
                        'is_owner' => false,
                    ]);
                }

                return ['taken' => $taken + 1, 'reservation' => $locked];
            });
        } catch (\Throwable $e) {
            Log::error('Iscrizione a partita aperta fallita', [
                'reservation_id' => $reservation->id,
                'player_id' => $player->id,
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Non siamo riusciti a completare l\'iscrizione. Riprova fra qualche istante.',
            ]);
        }

        if (isset($result['error'])) {
            return response()->json([
                'success' => false,
                'message' => $result['error'],
                'data' => $this->fresh($reservation),
            ]);
        }

        // Le notifiche restano fuori dalla transazione: un errore SMTP
        // non deve annullare un'iscrizione già valida.
        $this->notifier->playerJoined($result['reservation'], $player);

        if ($result['taken'] >= $result['reservation']->slots_total) {
            $this->notifier->matchIsFull($result['reservation']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Iscrizione confermata',
            'data' => $this->fresh($reservation),
        ]);
    }

    /**
     * DELETE api/open-matches/{reservation}/join
     * Disiscrizione, consentita fino a open_closes_at.
     */
    public function leave(Request $request, Reservation $reservation)
    {
        $player = Player::find($request->input('user_id'));

        if (! $player) {
            return response()->json([
                'success' => false,
                'message' => 'Utente non trovato',
            ]);
        }

        if ((int) $reservation->booking_subject === (int) $player->id) {
            return response()->json([
                'success' => false,
                'message' => 'Sei tu ad aver pubblicato questa partita: puoi solo chiuderla alle iscrizioni',
            ]);
        }

        if (! $reservation->joinWindowIsOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'Le iscrizioni sono chiuse: contatta la struttura per disdire',
            ]);
        }

        $updated = DB::table('player_reservation')
            ->where('reservation_id', $reservation->id)
            ->where('player_id', $player->id)
            ->where('join_status', 'accepted')
            ->update(['join_status' => 'cancelled']);

        if (! $updated) {
            return response()->json([
                'success' => false,
                'message' => 'Non risulti iscritto a questa partita',
                'data' => $this->fresh($reservation),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Iscrizione annullata',
            'data' => $this->fresh($reservation),
        ]);
    }

    // ==========================================================
    // Apertura / chiusura da parte dell'owner
    // ==========================================================

    /**
     * POST api/reservations/{reservation}/open
     */
    public function open(Request $request, Reservation $reservation)
    {
        if ((int) $reservation->booking_subject !== (int) $request->input('user_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Puoi aprire solo le prenotazioni che hai fatto tu',
            ], 403);
        }

        if ($reservation->status !== Reservation::STATUS_CONFIRMED) {
            return response()->json([
                'success' => false,
                'message' => 'Questa prenotazione non è confermata',
            ]);
        }

        if (! $reservation->slotStartsAt() || $reservation->slotStartsAt()->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Questa prenotazione è già passata',
            ]);
        }

        $validator = validator($request->all(), OpenMatchService::rules());

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ]);
        }

        $taken = $this->openMatches->ensureOwnerIsEnrolled($reservation);

        if ($request->input('slots_total') < $taken) {
            return response()->json([
                'success' => false,
                'message' => 'Ci sono già '.$taken.' giocatori: i posti totali non possono essere meno.',
            ]);
        }

        $this->openMatches->publish($reservation, [
            'slots_total' => $request->input('slots_total'),
            'category' => $request->input('category'),
            'level_min' => $request->input('level_min'),
            'level_max' => $request->input('level_max'),
            'note' => $request->input('note'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Partita pubblicata fra quelle aperte',
            'data' => $this->fresh($reservation),
        ]);
    }

    /**
     * DELETE api/reservations/{reservation}/open
     */
    public function close(Request $request, Reservation $reservation)
    {
        if ((int) $reservation->booking_subject !== (int) $request->input('user_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Puoi chiudere solo le prenotazioni che hai fatto tu',
            ], 403);
        }

        $reservation->is_open = false;
        $reservation->save();

        return response()->json([
            'success' => true,
            'message' => 'Partita rimossa dalle partite aperte',
            'data' => $this->fresh($reservation),
        ]);
    }

    // ==========================================================
    // Partite aperte dell'utente
    // ==========================================================

    /**
     * POST api/account/open-matches
     * Quelle pubblicate dall'utente e quelle a cui partecipa.
     */
    public function mine(Request $request)
    {
        $playerId = (int) $request->input('user_id');

        if (! $playerId) {
            return response()->json([
                'success' => false,
                'message' => 'Utente non identificato',
            ]);
        }

        $base = fn () => Reservation::query()
            ->where('is_open', true)
            ->where('status', Reservation::STATUS_CONFIRMED)
            ->with(['owner:id,name,surname,nickname,level,img'])
            ->withCount('acceptedPlayers')
            ->orderBySlot();

        $published = $base()->where('booking_subject', $playerId)->get();

        $joined = $base()
            ->where('booking_subject', '!=', $playerId)
            ->whereHas('acceptedPlayers', fn ($q) => $q->where('players.id', $playerId))
            ->get();

        $fieldSet = $this->fieldSet();

        return response()->json([
            'success' => true,
            'data' => [
                'published' => $published->map(fn ($r) => $this->summary($r, $fieldSet, $playerId)),
                'joined' => $joined->map(fn ($r) => $this->summary($r, $fieldSet, $playerId)),
            ],
        ]);
    }

    // ==========================================================
    // Helper
    // ==========================================================

    /**
     * Impostazioni dei campi (durata dello slot): servono a calcolare
     * l'orario di fine senza ricaricarle per ogni riga.
     */
    private function fieldSet(): array
    {
        return Setting::fieldSet();
    }

    /** Ricarica la prenotazione nel formato usato dal frontend. */
    private function fresh(Reservation $reservation): array
    {
        $reloaded = Reservation::whereKey($reservation->id)
            ->with([
                'owner:id,name,surname,nickname,level,img',
                'players:id,name,surname,nickname,level,img',
            ])
            ->withCount('acceptedPlayers')
            ->first();

        $summary = $this->summary($reloaded, $this->fieldSet(), request()->input('user_id'));
        $summary['participants'] = $this->participants($reloaded);

        return $summary;
    }

    /** Rappresentazione compatta usata in lista e in dettaglio. */
    private function summary(Reservation $r, array $fieldSet, $playerId = null): array
    {
        $start = $r->slotStartsAt();
        $minutes = ($fieldSet[$r->field]['m_during'] ?? 30) * (int) $r->duration;

        return [
            'id' => $r->id,
            'category' => $r->open_category ?: $this->openMatches->categoryFromLesson($r->lesson),
            'field' => $r->field,
            'type' => $r->type,
            'date_slot' => $r->date_slot,
            'starts_at' => $start?->format('Y-m-d H:i'),
            'ends_at' => $start?->copy()->addMinutes($minutes)->format('H:i'),
            'duration_minutes' => $minutes,
            'slots_total' => (int) $r->slots_total,
            'slots_taken' => $r->slots_taken,
            'slots_left' => $r->slots_left,
            'is_full' => $r->is_full,
            'level_min' => $r->level_min,
            'level_max' => $r->level_max,
            'level_label' => $r->levelLabel(),
            'closes_at' => optional($r->open_closes_at)->format('Y-m-d H:i'),
            'join_open' => $r->joinWindowIsOpen(),
            'owner' => $r->relationLoaded('owner') && $r->owner ? [
                'id' => $r->owner->id,
                'name' => $r->owner->name,
                'surname' => $r->owner->surname,
                'nickname' => $r->owner->nickname,
                'level' => $r->owner->level,
                'img_url' => $r->owner->img_url,
            ] : null,
            'is_owner' => $playerId ? (int) $r->booking_subject === (int) $playerId : false,
            'is_joined' => $playerId ? $this->isJoined($r, $playerId) : false,
        ];
    }

    /** True se il giocatore risulta iscritto e confermato. */
    private function isJoined(Reservation $r, $playerId): bool
    {
        if ($r->relationLoaded('players')) {
            return $r->getRelation('players')->contains(
                fn ($p) => (int) $p->id === (int) $playerId && $p->pivot->join_status === 'accepted'
            );
        }

        return DB::table('player_reservation')
            ->where('reservation_id', $r->id)
            ->where('player_id', $playerId)
            ->where('join_status', 'accepted')
            ->exists();
    }

    /** Elenco degli iscritti confermati. */
    private function participants(Reservation $r): array
    {
        if (! $r->relationLoaded('players')) {
            $r->load('players:id,name,surname,nickname,level,img');
        }

        return $r->getRelation('players')
            ->filter(fn ($p) => $p->pivot->join_status === 'accepted')
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'surname' => $p->surname,
                'nickname' => $p->nickname,
                'level' => $p->level,
                'img_url' => $p->img_url,
                'is_owner' => (bool) $p->pivot->is_owner,
            ])
            ->values()
            ->all();
    }
}
