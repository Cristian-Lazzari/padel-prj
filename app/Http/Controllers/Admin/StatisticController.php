<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\Setting;
use App\Models\Tournament;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Statistiche del circolo: andamento di campi, lezioni e tornei nel tempo.
 *
 * Tutto quello che sta in pagina nasce da poche query aggregate: date_slot è un
 * varchar 'Y-m-d H:i', quindi il raggruppamento si fa con substr() e non con le
 * funzioni di data del database. È più leggero di STR_TO_DATE su ogni riga e
 * funziona identico su MySQL e su SQLite (i test girano su SQLite).
 */
class StatisticController extends Controller
{
    /** Intervalli pronti nella barra dei filtri. */
    public const PRESETS = [
        '30'   => 'Ultimi 30 giorni',
        '90'   => 'Ultimi 90 giorni',
        '12m'  => 'Ultimi 12 mesi',
        'year' => 'Anno in corso',
    ];

    /** Oltre questa distanza la pagina diventa illeggibile e la query pesante. */
    private const MAX_DAYS = 1100;

    public function index(Request $request)
    {
        [$from, $to, $preset] = $this->period($request);

        $granularity = $this->granularity($from, $to);
        $buckets     = $this->buckets($from, $to, $granularity);
        $bucketSql   = $granularity === 'month' ? 'substr(date_slot, 1, 7)' : 'substr(date_slot, 1, 10)';

        $fieldSet = Setting::fieldSet();
        // La chiave può mancare o non essere una lista: una configurazione
        // incompleta deve degradare, non buttare giù la pagina
        $dayOff   = (array) (Setting::props('advanced')['day_off'] ?? []);

        $rows  = $this->reservationRows($from, $to, $bucketSql);
        $slots = count($buckets);

        // ---- Andamento di campi, lezioni e tornei -------------------------
        $trend = [
            'match'      => array_fill(0, $slots, 0),
            'lesson'     => array_fill(0, $slots, 0),
            'tournament' => array_fill(0, $slots, 0),
        ];
        $totals = ['match' => 0, 'lesson' => 0, 'tournament' => 0];

        // ---- Ore prenotate per campo --------------------------------------
        $bookedByField = [];   // [campo][bucket] = minuti prenotati
        $minutesTotal  = 0;

        foreach ($rows as $row) {
            $i = $this->bucketIndex($row->b, $granularity, $buckets);
            if ($i === null) {
                continue;
            }

            $category = $this->category($row->lesson);
            $trend[$category][$i] += (int) $row->n;
            $totals[$category]    += (int) $row->n;

            // duration conta le fasce del campo: i minuti dipendono dal campo
            $minutes = (int) $row->slots * (int) ($fieldSet[$row->field]['m_during'] ?? 30);
            $bookedByField[$row->field][$i] = ($bookedByField[$row->field][$i] ?? 0) + $minutes;
            $minutesTotal += $minutes;
        }

        $capacity = $this->capacity($from, $to, $granularity, $buckets, $fieldSet, $dayOff);
        $fields   = $this->fieldStats($fieldSet, $bookedByField, $capacity, $slots);

        // ---- Tornei --------------------------------------------------------
        $tournaments = Tournament::query()
            ->where('status', '!=', 'cancelled')
            ->whereBetween('starts_at', [$from, $to])
            ->withCount(['confirmedRegistrations', 'waitlistRegistrations'])
            ->orderBy('starts_at')
            ->get();

        $tournamentSeries = [
            'confirmed' => array_fill(0, $slots, 0),
            'waitlist'  => array_fill(0, $slots, 0),
        ];
        foreach ($tournaments as $tournament) {
            $i = $this->bucketIndex(
                $tournament->starts_at->format($granularity === 'month' ? 'Y-m' : 'Y-m-d'),
                $granularity,
                $buckets
            );
            if ($i === null) {
                continue;
            }
            $tournamentSeries['confirmed'][$i] += $tournament->confirmed_registrations_count;
            $tournamentSeries['waitlist'][$i]  += $tournament->waitlist_registrations_count;
        }

        return view('admin.statistics', [
            'from'             => $from,
            'to'               => $to,
            'preset'           => $preset,
            'presets'          => self::PRESETS,
            'granularity'      => $granularity,
            'buckets'          => $buckets,
            'trend'            => $trend,
            'totals'           => $totals,
            'hoursTotal'       => $minutesTotal / 60,
            'fields'           => $fields,
            'occupancy'        => $this->overallOccupancy($fields),
            'tournaments'      => $tournaments,
            'tournamentSeries' => $tournamentSeries,
            'byHour'           => $this->byHour($from, $to, $fieldSet),
            'byWeekday'        => $this->byWeekday($from, $to),
            'topPlayers'       => $this->topPlayers($from, $to),
            'newPlayers'       => Player::where('role', 'player')->whereBetween('created_at', [$from, $to])->count(),
        ]);
    }

    // ==========================================================
    // Periodo e intervalli
    // ==========================================================

    /** @return array{0: Carbon, 1: Carbon, 2: string} */
    private function period(Request $request): array
    {
        $preset = (string) $request->query('period', '12m');

        if ($preset === 'custom') {
            $from = $this->parseDate($request->query('from'));
            $to   = $this->parseDate($request->query('to'));

            if ($from && $to) {
                if ($to->lt($from)) {
                    [$from, $to] = [$to, $from];
                }
                $from = $from->startOfDay();
                $to   = $to->endOfDay();

                if ($from->diffInDays($to) > self::MAX_DAYS) {
                    $from = $to->copy()->subDays(self::MAX_DAYS)->startOfDay();
                }

                return [$from, $to, 'custom'];
            }

            $preset = '12m';
        }

        $to = Carbon::today()->endOfDay();

        switch ($preset) {
            case '30':
                return [$to->copy()->subDays(29)->startOfDay(), $to, '30'];
            case '90':
                return [$to->copy()->subDays(89)->startOfDay(), $to, '90'];
            case 'year':
                return [Carbon::today()->startOfYear(), $to, 'year'];
            default:
                return [$to->copy()->startOfMonth()->subMonths(11), $to, '12m'];
        }
    }

    private function parseDate(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Il passo dell'asse: giorni su periodi corti, mesi su periodi lunghi. */
    private function granularity(Carbon $from, Carbon $to): string
    {
        $days = $from->diffInDays($to);

        if ($days <= 45) {
            return 'day';
        }

        return $days <= 200 ? 'week' : 'month';
    }

    /**
     * Le colonne dell'asse X, già etichettate.
     *
     * @return array<int, array{key: string, label: string, full: string}>
     */
    private function buckets(Carbon $from, Carbon $to, string $granularity): array
    {
        $buckets = [];

        if ($granularity === 'month') {
            $cursor = $from->copy()->startOfMonth();
            while ($cursor->lte($to)) {
                $buckets[] = [
                    'key'   => $cursor->format('Y-m'),
                    'label' => ucfirst($cursor->locale('it')->translatedFormat('M')),
                    'full'  => ucfirst($cursor->locale('it')->translatedFormat('F Y')),
                ];
                $cursor->addMonth();
            }

            return $buckets;
        }

        if ($granularity === 'week') {
            $cursor = $from->copy()->startOfWeek();
            while ($cursor->lte($to)) {
                $buckets[] = [
                    'key'   => $cursor->format('Y-m-d'),
                    'label' => $cursor->format('j/n'),
                    'full'  => 'Settimana del '.$cursor->locale('it')->translatedFormat('j M Y'),
                ];
                $cursor->addWeek();
            }

            return $buckets;
        }

        $cursor = $from->copy()->startOfDay();
        while ($cursor->lte($to)) {
            $buckets[] = [
                'key'   => $cursor->format('Y-m-d'),
                'label' => $cursor->format('j/n'),
                'full'  => ucfirst($cursor->locale('it')->translatedFormat('D j M Y')),
            ];
            $cursor->addDay();
        }

        return $buckets;
    }

    /** Posizione di una chiave grezza ('Y-m' o 'Y-m-d') sull'asse. */
    private function bucketIndex(?string $raw, string $granularity, array $buckets): ?int
    {
        if (! $raw) {
            return null;
        }

        // La cache evita di ricostruire la mappa chiave => colonna a ogni riga
        static $maps = [];
        $signature = $granularity.'|'.count($buckets).'|'.($buckets[0]['key'] ?? '');

        if (! isset($maps[$signature])) {
            $maps[$signature] = array_flip(array_column($buckets, 'key'));
        }

        if ($granularity === 'week') {
            try {
                $raw = Carbon::createFromFormat('Y-m-d', substr($raw, 0, 10))->startOfWeek()->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        return $maps[$signature][$raw] ?? null;
    }

    // ==========================================================
    // Query
    // ==========================================================

    /** Una sola aggregata per colonna, campo e categoria. */
    private function reservationRows(Carbon $from, Carbon $to, string $bucketSql)
    {
        return DB::table('reservations')
            ->selectRaw($bucketSql.' as b, field, lesson, count(*) as n, sum(duration) as slots')
            ->where('status', '!=', 0)
            ->whereBetween('date_slot', [$from->format('Y-m-d').' 00:00', $to->format('Y-m-d').' 23:59'])
            ->groupByRaw($bucketSql.', field, lesson')
            ->get();
    }

    /** lesson: 1 lezione, 2 partita di torneo, altrimenti campo prenotato. */
    private function category($lesson): string
    {
        return match ((int) $lesson) {
            1 => 'lesson',
            2 => 'tournament',
            default => 'match',
        };
    }

    /** Prenotazioni per ora di inizio, sull'arco di apertura del circolo. */
    private function byHour(Carbon $from, Carbon $to, array $fieldSet): array
    {
        $rows = DB::table('reservations')
            ->selectRaw('substr(date_slot, 12, 2) as h, count(*) as n')
            ->where('status', '!=', 0)
            ->whereBetween('date_slot', [$from->format('Y-m-d').' 00:00', $to->format('Y-m-d').' 23:59'])
            ->groupByRaw('substr(date_slot, 12, 2)')
            ->get()
            ->pluck('n', 'h');

        [$first, $last] = $this->openingHours($fieldSet, $rows->keys()->all());

        $out = [];
        for ($h = $first; $h <= $last; $h++) {
            $key = str_pad((string) $h, 2, '0', STR_PAD_LEFT);
            $out[$key] = (int) ($rows[$key] ?? 0);
        }

        return $out;
    }

    /** Arco orario da mostrare: quello dei campi, allargato ai dati fuori orario. */
    private function openingHours(array $fieldSet, array $found): array
    {
        $first = null;
        $last  = null;

        foreach ($fieldSet as $field) {
            $start = (int) substr((string) ($field['h_start'] ?? '09:00'), 0, 2);
            $end   = (int) ceil($start + ((int) ($field['m_during_client'] ?? 30) * (int) ($field['n_slot'] ?? 0)) / 60);

            $first = $first === null ? $start : min($first, $start);
            $last  = $last === null ? $end : max($last, $end);
        }

        foreach ($found as $hour) {
            $hour  = (int) $hour;
            $first = $first === null ? $hour : min($first, $hour);
            $last  = $last === null ? $hour : max($last, $hour);
        }

        if ($first === null) {
            return [9, 22];
        }

        return [max(0, $first), min(23, max($last, $first))];
    }

    /** Prenotazioni per giorno della settimana (lunedì -> domenica). */
    private function byWeekday(Carbon $from, Carbon $to): array
    {
        $rows = DB::table('reservations')
            ->selectRaw('substr(date_slot, 1, 10) as d, count(*) as n')
            ->where('status', '!=', 0)
            ->whereBetween('date_slot', [$from->format('Y-m-d').' 00:00', $to->format('Y-m-d').' 23:59'])
            ->groupByRaw('substr(date_slot, 1, 10)')
            ->get();

        $out = array_fill(1, 7, 0);

        foreach ($rows as $row) {
            try {
                $day = Carbon::createFromFormat('Y-m-d', $row->d);
            } catch (\Throwable $e) {
                continue;
            }
            $out[$day->dayOfWeekIso] += (int) $row->n;
        }

        return $out;
    }

    /** Chi ha prenotato di più nel periodo. */
    private function topPlayers(Carbon $from, Carbon $to)
    {
        return DB::table('reservations')
            ->join('players', 'players.id', '=', 'reservations.booking_subject')
            ->selectRaw('players.id, players.name, players.surname, players.nickname, players.level, count(*) as n')
            ->where('reservations.status', '!=', 0)
            ->whereBetween('reservations.date_slot', [$from->format('Y-m-d').' 00:00', $to->format('Y-m-d').' 23:59'])
            ->groupBy('players.id', 'players.name', 'players.surname', 'players.nickname', 'players.level')
            ->orderByDesc('n')
            ->limit(8)
            ->get();
    }

    // ==========================================================
    // Occupazione
    // ==========================================================

    /**
     * Minuti disponibili per campo e per colonna: un campo è aperto se il
     * giorno non è di chiusura del circolo né uno dei suoi giorni chiusi.
     *
     * @return array<string, array<int, int>>
     */
    private function capacity(Carbon $from, Carbon $to, string $granularity, array $buckets, array $fieldSet, array $dayOff): array
    {
        $capacity = [];
        $cursor   = $from->copy()->startOfDay();

        while ($cursor->lte($to)) {
            $date = $cursor->format('Y-m-d');
            $i    = $this->bucketIndex($granularity === 'month' ? substr($date, 0, 7) : $date, $granularity, $buckets);

            if ($i !== null && ! in_array($date, $dayOff)) {
                foreach ($fieldSet as $key => $field) {
                    if (in_array($cursor->format('N'), $field['closed_days'] ?? [])) {
                        continue;
                    }
                    $open = (int) ($field['m_during_client'] ?? 30) * (int) ($field['n_slot'] ?? 0);
                    $capacity[$key][$i] = ($capacity[$key][$i] ?? 0) + $open;
                }
            }

            $cursor->addDay();
        }

        return $capacity;
    }

    /**
     * Ore prenotate e occupazione, campo per campo.
     *
     * @return array<int, array{name: string, slot: int, hours: array<int, float>, booked: float, capacity: float, rate: float}>
     */
    private function fieldStats(array $fieldSet, array $bookedByField, array $capacity, int $slots): array
    {
        // I campi cancellati dalle impostazioni restano in coda: le loro ore
        // sono state giocate davvero e sparire falserebbe i totali.
        $names = array_values(array_unique(array_merge(array_keys($fieldSet), array_keys($bookedByField))));
        $stats = [];

        foreach ($names as $posizione => $name) {
            $hours     = [];
            $booked    = 0;
            $available = 0;

            for ($i = 0; $i < $slots; $i++) {
                $minutes = $bookedByField[$name][$i] ?? 0;
                $hours[] = round($minutes / 60, 1);
                $booked += $minutes;
                $available += $capacity[$name][$i] ?? 0;
            }

            $stats[] = [
                'name'     => (string) $name,
                // Il colore segue il campo, non la sua posizione in classifica:
                // cambiando periodo un campo non deve cambiare tinta
                'slot'     => ($posizione % 5) + 1,
                'hours'    => $hours,
                'booked'   => $booked / 60,
                'capacity' => $available / 60,
                'rate'     => $available > 0 ? (int) round($booked / $available * 100) : 0,
            ];
        }

        // Il campo più usato in cima: la lista si legge come una classifica
        usort($stats, fn ($a, $b) => $b['booked'] <=> $a['booked']);

        return $stats;
    }

    private function overallOccupancy(array $fields): int
    {
        $booked   = array_sum(array_column($fields, 'booked'));
        $capacity = array_sum(array_column($fields, 'capacity'));

        return $capacity > 0 ? (int) round($booked / $capacity * 100) : 0;
    }
}
