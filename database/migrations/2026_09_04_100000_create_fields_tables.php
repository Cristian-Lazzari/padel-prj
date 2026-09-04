<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * I campi escono dal JSON di `settings.advanced` e diventano tabelle.
 *
 * Motivo: gli orari diventano diversi giorno per giorno, e sette righe per
 * campo dentro una `property` che `SettingController::updateAll()` riscrive
 * per intero a ogni salvataggio sono un dato fragile e non validabile.
 *
 * Il JSON non viene cancellato: resta com'è, congelato al momento della
 * conversione. Se si torna indietro con il codice, `Setting::fieldSet()`
 * ricomincia a leggerlo e il gestionale continua a funzionare con gli
 * orari che aveva prima.
 */
return new class extends Migration
{
    /** 1 = lunedì … 7 = domenica, come `closed_days` e `day_w` già in giro. */
    private const WEEKDAYS = [1, 2, 3, 4, 5, 6, 7];

    public function up(): void
    {
        Schema::create('fields', function (Blueprint $table) {
            $table->id();
            // Il nome resta la chiave naturale: `reservations.field` e
            // `fixed_slots.field` lo contengono come stringa.
            $table->string('name')->unique();
            $table->string('type');
            $table->unsignedSmallInteger('m_during')->default(30);
            $table->unsignedSmallInteger('m_during_client')->default(90);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('field_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday');
            $table->boolean('closed')->default(false);
            $table->string('h_start', 5)->nullable();
            $table->string('h_end', 5)->nullable();
            $table->timestamps();

            $table->unique(['field_id', 'weekday']);
        });

        $this->convert();
    }

    public function down(): void
    {
        Schema::dropIfExists('field_hours');
        Schema::dropIfExists('fields');
    }

    /**
     * Porta dentro le tabelle quello che c'è nel JSON.
     *
     * Ogni campo diventa una riga più sette righe di orario: l'apertura è
     * quella di prima, la chiusura è quella che il gestionale calcolava
     * (`h_start + m_during_client × n_slot`) e i giorni in `closed_days`
     * nascono chiusi. Nessuna disponibilità cambia il giorno del passaggio.
     */
    private function convert(): void
    {
        $property = DB::table('settings')->where('name', 'advanced')->value('property');
        $fieldSet = json_decode((string) $property, true)['field_set'] ?? [];

        if (! is_array($fieldSet) || ! $fieldSet) {
            return;
        }

        $now = now();
        $sort = 0;

        foreach ($fieldSet as $name => $f) {
            if (! is_array($f)) {
                continue;
            }

            $during = max(1, (int) ($f['m_during'] ?? 30));
            $client = max(1, (int) ($f['m_during_client'] ?? $during));
            $slots = max(0, (int) ($f['n_slot'] ?? 0));

            $open = $this->time($f['h_start'] ?? null) ?: '08:00';
            $close = Carbon::createFromFormat('H:i', $open)->addMinutes($client * $slots);

            // Una giornata che non arriva a coprire nemmeno una fascia non è
            // configurabile: si tiene aperta fino a fine giornata.
            $closeLabel = $slots > 0 ? $close->format('H:i') : '23:00';

            if ($closeLabel === '00:00' || $closeLabel <= $open) {
                $closeLabel = '23:59';
            }

            $fieldId = DB::table('fields')->insertGetId([
                'name' => (string) $name,
                'type' => (string) ($f['type'] ?? 'padel'),
                'm_during' => $during,
                'm_during_client' => $client,
                'sort' => $sort++,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $closedDays = array_map('intval', (array) ($f['closed_days'] ?? []));

            $rows = [];

            foreach (self::WEEKDAYS as $weekday) {
                $closed = in_array($weekday, $closedDays, true);

                $rows[] = [
                    'field_id' => $fieldId,
                    'weekday' => $weekday,
                    'closed' => $closed,
                    'h_start' => $closed ? null : $open,
                    'h_end' => $closed ? null : $closeLabel,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('field_hours')->insert($rows);
        }
    }

    /** Normalizza un orario a 'H:i', o null se non è leggibile. */
    private function time($value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('H:i', substr($value, 0, 5))->format('H:i');
        } catch (\Throwable $e) {
            return null;
        }
    }
};
