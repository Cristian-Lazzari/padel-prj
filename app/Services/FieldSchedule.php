<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * Orari di apertura di un campo, giorno per giorno.
 *
 * Lavora sull'array che restituisce `Setting::fieldSet()`, non sui model:
 * il calendario ci passa dentro trentun giorni per quattro campi e non deve
 * fare una query per casella. Se l'array arriva nella vecchia forma (senza
 * la chiave `hours`) si ricade sul calcolo di prima — apertura più
 * `m_during_client × n_slot` — così niente si rompe durante il passaggio.
 */
class FieldSchedule
{
    /**
     * Apertura e chiusura di un campo in un giorno: ['h_start' => '08:00',
     * 'h_end' => '23:00']. Null se quel giorno il campo è chiuso.
     *
     * @param  Carbon|int  $day  una data, oppure il giorno ISO (1 = lunedì)
     */
    public static function hours(array $field, $day): ?array
    {
        $weekday = $day instanceof Carbon ? (int) $day->format('N') : (int) $day;

        if (isset($field['hours'])) {
            $h = $field['hours'][$weekday] ?? null;

            if (! $h || ! empty($h['closed']) || empty($h['h_start']) || empty($h['h_end'])) {
                return null;
            }

            return ['h_start' => $h['h_start'], 'h_end' => $h['h_end']];
        }

        // Vecchia forma: un solo orario per tutti i giorni aperti.
        if (in_array($weekday, array_map('intval', (array) ($field['closed_days'] ?? [])), true)) {
            return null;
        }

        $open = substr((string) ($field['h_start'] ?? ''), 0, 5);
        $span = (int) ($field['m_during_client'] ?? 0) * (int) ($field['n_slot'] ?? 0);

        if ($open === '' || $span < 1) {
            return null;
        }

        try {
            $close = Carbon::createFromFormat('H:i', $open)->addMinutes($span)->format('H:i');
        } catch (\Throwable $e) {
            return null;
        }

        return ['h_start' => $open, 'h_end' => $close];
    }

    /**
     * L'arco più largo della settimana: dalla prima apertura all'ultima
     * chiusura, su tutti i giorni in cui il campo è aperto. Serve a chi
     * deve mostrare un elenco di orari che valga per l'intera settimana,
     * come le fasce degli istruttori. Null se il campo non apre mai.
     *
     * @return array{h_start:string, h_end:string}|null
     */
    public static function span(array $field): ?array
    {
        $open = null;
        $close = null;

        foreach ([1, 2, 3, 4, 5, 6, 7] as $weekday) {
            $hours = self::hours($field, $weekday);

            if (! $hours) {
                continue;
            }

            $open = $open === null ? $hours['h_start'] : min($open, $hours['h_start']);
            $close = $close === null ? $hours['h_end'] : max($close, $hours['h_end']);
        }

        return $open === null ? null : ['h_start' => $open, 'h_end' => $close];
    }

    /** Quanto dura quell'arco, in minuti. */
    public static function spanMinutes(array $field): int
    {
        $span = self::span($field);

        if (! $span) {
            return 0;
        }

        [$open, $close] = self::range($span);

        return $open ? $open->diffInMinutes($close) : 0;
    }

    /** Minuti di apertura di un campo in un giorno. Zero se è chiuso. */
    public static function minutes(array $field, $day): int
    {
        $hours = self::hours($field, $day);

        if (! $hours) {
            return 0;
        }

        [$open, $close] = self::range($hours);

        return $open ? $open->diffInMinutes($close) : 0;
    }

    /** True se il campo è aperto in quel giorno. */
    public static function isOpen(array $field, $day): bool
    {
        return self::hours($field, $day) !== null;
    }

    /**
     * I punti della griglia di quel giorno, dall'apertura alla chiusura
     * comprese, un punto ogni `m_during` minuti.
     *
     * @return string[] orari 'H:i'
     */
    public static function points(array $field, $day): array
    {
        $hours = self::hours($field, $day);

        if (! $hours) {
            return [];
        }

        $step = max(1, (int) ($field['m_during'] ?? 30));

        [$cursor, $close] = self::range($hours);

        if (! $cursor) {
            return [];
        }

        $points = [];

        while ($cursor->lte($close) && count($points) < 500) {
            $points[] = $cursor->format('H:i');
            $cursor->addMinutes($step);
        }

        return $points;
    }

    /**
     * Apertura e chiusura come istanti di una certa data: comodo per chi
     * deve confrontarle con un `date_slot`. Null se il campo è chiuso.
     *
     * @return array{0:Carbon, 1:Carbon}|null
     */
    public static function window(array $field, Carbon $date): ?array
    {
        $hours = self::hours($field, $date);

        if (! $hours) {
            return null;
        }

        $open = $date->copy()->setTimeFromTimeString($hours['h_start']);
        $close = $date->copy()->setTimeFromTimeString($hours['h_end']);

        // Chiusura dopo la mezzanotte.
        if ($close->lte($open)) {
            $close->addDay();
        }

        return [$open, $close];
    }

    /** @return array{0:?Carbon, 1:?Carbon} apertura e chiusura come orari sciolti */
    private static function range(array $hours): array
    {
        try {
            $open = Carbon::createFromFormat('H:i', $hours['h_start']);
            $close = Carbon::createFromFormat('H:i', $hours['h_end']);
        } catch (\Throwable $e) {
            return [null, null];
        }

        if ($close->lte($open)) {
            $close->addDay();
        }

        return [$open, $close];
    }
}
