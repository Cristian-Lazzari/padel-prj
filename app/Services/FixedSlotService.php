<?php

namespace App\Services;

use App\Models\FixedSlot;
use App\Models\Reservation;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Materializzazione dei campi fissi in prenotazioni reali.
 *
 * Scelta architetturale: le occorrenze vengono scritte in `reservations`
 * invece di essere calcolate al volo. L'occupazione dei campi è letta da
 * quattro punti indipendenti (disponibilità pubblica, controllo conflitti
 * in prenotazione, calendario admin, elenco prenotazioni) che leggono tutti
 * quella tabella: materializzando, tutti li vedono occupati senza toccare
 * l'algoritmo di disponibilità.
 */
class FixedSlotService
{
    /** Quante settimane in avanti materializzare a ogni esecuzione. */
    public const HORIZON_WEEKS = 8;

    /**
     * Date in cui il campo fisso ricorre nella finestra indicata.
     *
     * @return Carbon[]
     */
    public function occurrences(FixedSlot $slot, Carbon $from, Carbon $to): array
    {
        $exceptions = $slot->relationLoaded('exceptions')
            ? $slot->getRelation('exceptions')
            : $slot->exceptions()->get();

        $skip = $exceptions->map(fn ($e) => Carbon::parse($e->date)->format('Y-m-d'))->all();

        $dates = [];
        $cursor = $from->copy()->startOfDay();
        $limit = $to->copy()->endOfDay();

        // Salta al primo giorno della settimana corrispondente.
        while ($cursor->dayOfWeek !== $slot->weekday && $cursor->lte($limit)) {
            $cursor->addDay();
        }

        while ($cursor->lte($limit)) {
            if ($slot->occursOn($cursor, $skip)) {
                $dates[] = $cursor->copy();
            }

            $cursor->addWeek();
        }

        return $dates;
    }

    /**
     * Crea le prenotazioni mancanti per tutti i campi fissi attivi.
     *
     * @return array{created:int, skipped:int, conflicts:array}
     */
    public function materializeAll(?Carbon $from = null, ?Carbon $to = null): array
    {
        $from = $from ?: Carbon::today();
        $to = $to ?: Carbon::today()->addWeeks(self::HORIZON_WEEKS);

        $slots = FixedSlot::active()->with('exceptions')->get();

        $created = 0;
        $skipped = 0;
        $conflicts = [];

        foreach ($slots as $slot) {
            $result = $this->materializeSlot($slot, $from, $to);
            $created += $result['created'];
            $skipped += $result['skipped'];
            $conflicts = array_merge($conflicts, $result['conflicts']);
        }

        return compact('created', 'skipped', 'conflicts');
    }

    /**
     * Materializza un singolo campo fisso nella finestra indicata.
     */
    public function materializeSlot(FixedSlot $slot, Carbon $from, Carbon $to): array
    {
        $created = 0;
        $skipped = 0;
        $conflicts = [];

        $fieldSet = $this->fieldSet();
        $type = $fieldSet[$slot->field]['type'] ?? 'Padel';
        $minutes = $fieldSet[$slot->field]['m_during'] ?? 30;

        foreach ($this->occurrences($slot, $from, $to) as $date) {
            $dateSlot = $date->format('Y-m-d').' '.$slot->start_time;

            // Già materializzata: niente da fare.
            $already = Reservation::where('fixed_slot_id', $slot->id)
                ->where('date_slot', $dateSlot)
                ->exists();

            if ($already) {
                $skipped++;
                continue;
            }

            // Qualcun altro ha già prenotato quello slot: non si sovrascrive,
            // il gestore lo risolve a mano.
            $conflict = $this->findConflict($slot->field, $dateSlot, $slot->duration, $minutes);

            if ($conflict) {
                $conflicts[] = [
                    'fixed_slot_id' => $slot->id,
                    'date_slot' => $dateSlot,
                    'reservation_id' => $conflict->id,
                ];
                $skipped++;
                continue;
            }

            $reservation = new Reservation();
            $reservation->date_slot = $dateSlot;
            $reservation->field = $slot->field;
            $reservation->status = '1';
            $reservation->duration = $slot->duration;
            $reservation->type = $type;
            $reservation->dinner = json_encode(['status' => false, 'guests' => 0, 'time' => '']);
            $reservation->message = 'Campo fisso';
            $reservation->booking_subject = $slot->player_id;
            $reservation->fixed_slot_id = $slot->id;
            $reservation->save();

            // Il titolare del campo fisso è anche il giocatore in squadra.
            DB::table('player_reservation')->insertOrIgnore([
                'reservation_id' => $reservation->id,
                'player_id' => $slot->player_id,
                'join_status' => 'accepted',
                'joined_at' => now(),
                'is_owner' => true,
            ]);

            $created++;
        }

        if ($conflicts) {
            Log::warning('Campi fissi: occorrenze non materializzate per conflitto', $conflicts);
        }

        return compact('created', 'skipped', 'conflicts');
    }

    /**
     * Rimuove le occorrenze future non ancora giocate di un campo fisso.
     * Serve quando lo slot viene modificato, sospeso, chiuso o quando si
     * aggiunge un'eccezione: le occorrenze passate restano come storico.
     */
    public function clearFuture(FixedSlot $slot, ?Carbon $from = null): int
    {
        $from = $from ?: Carbon::now();

        $ids = Reservation::where('fixed_slot_id', $slot->id)
            ->whereRaw(Reservation::SLOT_AS_DATETIME.' >= ?', [$from->format('Y-m-d H:i:s')])
            ->pluck('id');

        return $this->deleteReservations($ids);
    }

    /** Rimuove l'occorrenza materializzata in una singola data. */
    public function clearDate(FixedSlot $slot, $date): int
    {
        $day = Carbon::parse($date)->format('Y-m-d');

        $ids = Reservation::where('fixed_slot_id', $slot->id)
            ->where('date_slot', 'LIKE', $day.'%')
            ->pluck('id');

        return $this->deleteReservations($ids);
    }

    /**
     * Elimina le prenotazioni staccando prima le righe del pivot:
     * player_reservation ha una foreign key senza cascade.
     */
    private function deleteReservations($ids): int
    {
        if ($ids->isEmpty()) {
            return 0;
        }

        DB::table('player_reservation')->whereIn('reservation_id', $ids)->delete();

        return Reservation::whereIn('id', $ids)->delete();
    }

    /**
     * Rigenera da zero le occorrenze future di un campo fisso.
     * Usato dopo ogni modifica dal back office.
     */
    public function refresh(FixedSlot $slot): array
    {
        $this->clearFuture($slot);

        if ($slot->status !== 'active') {
            return ['created' => 0, 'skipped' => 0, 'conflicts' => []];
        }

        return $this->materializeSlot(
            $slot->fresh('exceptions'),
            Carbon::today(),
            Carbon::today()->addWeeks(self::HORIZON_WEEKS)
        );
    }

    /**
     * Campo fisso attivo che occupa lo slot richiesto.
     *
     * Rete di sicurezza sul percorso di scrittura: fra due esecuzioni del
     * comando schedulato un cliente potrebbe prenotare un'occorrenza non
     * ancora materializzata.
     */
    public function conflictingSlot(string $field, string $dateSlot, int $duration): ?FixedSlot
    {
        $start = $this->parseSlot($dateSlot);

        if (! $start) {
            return null;
        }

        $fieldSet = $this->fieldSet();
        $minutes = $fieldSet[$field]['m_during'] ?? 30;

        $requestStart = $start->copy();
        $requestEnd = $start->copy()->addMinutes($minutes * $duration);

        $slots = FixedSlot::active()
            ->validOn($start)
            ->where('field', $field)
            ->where('weekday', $start->dayOfWeek)
            ->with('exceptions')
            ->get();

        foreach ($slots as $slot) {
            if (! $slot->occursOn($start->copy()->startOfDay())) {
                continue;
            }

            $slotStart = $start->copy()->setTimeFromTimeString($slot->start_time);
            $slotEnd = $slotStart->copy()->addMinutes($minutes * $slot->duration);

            // Sovrapposizione fra i due intervalli.
            if ($requestStart->lt($slotEnd) && $slotStart->lt($requestEnd)) {
                return $slot;
            }
        }

        return null;
    }

    /** Prossime date del campo fisso, per l'area cliente. */
    public function nextDates(FixedSlot $slot, int $count = 5): array
    {
        $dates = $this->occurrences(
            $slot,
            Carbon::today(),
            Carbon::today()->addWeeks(self::HORIZON_WEEKS * 2)
        );

        return array_slice($dates, 0, $count);
    }

    // ==========================================================
    // Interni
    // ==========================================================

    private function fieldSet(): array
    {
        $setting = Setting::where('name', 'advanced')->first();

        if (! $setting) {
            return [];
        }

        return json_decode($setting->property, true)['field_set'] ?? [];
    }

    private function parseSlot(string $dateSlot): ?Carbon
    {
        try {
            return Carbon::createFromFormat('Y-m-d H:i', trim($dateSlot));
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Prenotazione già esistente che si sovrappone allo slot indicato.
     */
    private function findConflict(string $field, string $dateSlot, int $duration, int $minutes): ?Reservation
    {
        $start = $this->parseSlot($dateSlot);

        if (! $start) {
            return null;
        }

        $end = $start->copy()->addMinutes($minutes * $duration);
        $day = $start->format('Y-m-d');

        $existing = Reservation::where('field', $field)
            ->where('status', '!=', '0')
            ->where('date_slot', 'LIKE', $day.'%')
            ->get(['id', 'date_slot', 'duration']);

        foreach ($existing as $reservation) {
            $otherStart = $this->parseSlot($reservation->date_slot);

            if (! $otherStart) {
                continue;
            }

            $otherEnd = $otherStart->copy()->addMinutes($minutes * (int) $reservation->duration);

            if ($start->lt($otherEnd) && $otherStart->lt($end)) {
                return $reservation;
            }
        }

        return null;
    }
}
