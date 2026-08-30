<?php

namespace App\Services;

use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

/**
 * Pubblicazione di una prenotazione fra le partite aperte.
 *
 * Vive in un servizio perché serve sia all'endpoint dedicato
 * (l'owner apre una prenotazione già esistente) sia al flusso di
 * prenotazione, che può creare la partita già aperta.
 */
class OpenMatchService
{
    /** Massimo numero di posti configurabile su una partita aperta. */
    public const MAX_SLOTS = 16;

    /** Regole di validazione condivise dai due punti di ingresso. */
    public static function rules(string $prefix = ''): array
    {
        return [
            $prefix.'slots_total' => 'required|integer|min:2|max:'.self::MAX_SLOTS,
            $prefix.'category'    => 'nullable|in:'.implode(',', Reservation::CATEGORIES),
            $prefix.'level_min'   => 'nullable|integer|min:1|max:5',
            $prefix.'level_max'   => 'nullable|integer|min:1|max:5|gte:'.$prefix.'level_min',
            $prefix.'note'        => 'nullable|string|max:500',
        ];
    }

    /**
     * Marca la prenotazione come aperta e ne imposta i parametri.
     * Chi ha prenotato occupa sempre uno dei posti.
     */
    public function publish(Reservation $reservation, array $options): Reservation
    {
        $this->ensureOwnerIsEnrolled($reservation);

        $reservation->is_open = true;
        $reservation->slots_total = (int) $options['slots_total'];
        $reservation->open_category = $options['category'] ?: $this->categoryFromLesson($reservation->lesson);
        $reservation->level_min = $options['level_min'] ?? null;
        $reservation->level_max = $options['level_max'] ?? null;
        $reservation->open_note = $options['note'] ?? null;
        // Le iscrizioni si chiudono all'inizio dello slot.
        $reservation->open_closes_at = $reservation->slotStartsAt();
        $reservation->save();

        return $reservation;
    }

    /**
     * Assicura che chi pubblica la partita occupi un posto e
     * restituisce il numero di posti attualmente occupati.
     */
    public function ensureOwnerIsEnrolled(Reservation $reservation): int
    {
        $existing = DB::table('player_reservation')
            ->where('reservation_id', $reservation->id)
            ->where('player_id', $reservation->booking_subject)
            ->first();

        if (! $existing) {
            DB::table('player_reservation')->insert([
                'reservation_id' => $reservation->id,
                'player_id' => $reservation->booking_subject,
                'join_status' => 'accepted',
                'joined_at' => now(),
                'is_owner' => true,
            ]);
        } elseif ($existing->join_status !== 'accepted' || ! $existing->is_owner) {
            DB::table('player_reservation')
                ->where('reservation_id', $reservation->id)
                ->where('player_id', $reservation->booking_subject)
                ->update(['join_status' => 'accepted', 'is_owner' => true]);
        }

        return $this->takenCount($reservation);
    }

    public function takenCount(Reservation $reservation): int
    {
        return DB::table('player_reservation')
            ->where('reservation_id', $reservation->id)
            ->where('join_status', 'accepted')
            ->count();
    }

    /** La colonna lesson è già un tri-stato 0=match, 1=lezione, 2=torneo. */
    public function categoryFromLesson($lesson): string
    {
        return [0 => 'match', 1 => 'lesson', 2 => 'tournament'][(int) $lesson] ?? 'match';
    }
}
