<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'property'
    ];

    /**
     * Stato di un'impostazione (0 = spenta, 2 = accesa), senza esplodere se la
     * riga non c'è: una configurazione incompleta deve degradare, non buttare
     * giù la pagina.
     */
    public static function flag(string $name, int $default = 0): int
    {
        $status = self::where('name', $name)->value('status');

        return $status === null ? $default : (int) $status;
    }

    /** Contenuto JSON di un'impostazione, come array. Vuoto se manca. */
    public static function props(string $name, array $default = []): array
    {
        $property = self::where('name', $name)->value('property');

        if (! $property) {
            return $default;
        }

        $decoded = json_decode($property, true);

        return is_array($decoded) ? $decoded : $default;
    }

    /** Il field_set letto una volta sola per richiesta. */
    private static ?array $fieldSetCache = null;

    /**
     * Elenco dei campi configurati (nome => impostazioni del campo).
     *
     * I campi vivono nelle tabelle `fields` e `field_hours`, ma la forma
     * restituita è quella di sempre — `h_start`, `n_slot`, `m_during`,
     * `m_during_client`, `type`, `closed_days` — con in più `hours`, che
     * porta l'orario di ciascun giorno della settimana. Finché le tabelle
     * non esistono (o sono vuote) si continua a leggere il vecchio JSON di
     * `advanced`, così il gestionale regge anche prima della migrazione.
     */
    public static function fieldSet(): array
    {
        if (self::$fieldSetCache !== null) {
            return self::$fieldSetCache;
        }

        return self::$fieldSetCache = self::fieldsFromTable()
            ?: (self::props('advanced')['field_set'] ?? []);
    }

    /** Da rilanciare dopo aver salvato i campi, se si rilegge nella stessa richiesta. */
    public static function forgetFieldSet(): void
    {
        self::$fieldSetCache = null;
    }

    private static function fieldsFromTable(): array
    {
        try {
            $fields = Field::with('hours')->orderBy('sort')->orderBy('id')->get();
        } catch (\Throwable $e) {
            // Tabelle non ancora migrate: si continua con il JSON.
            return [];
        }

        $set = [];

        foreach ($fields as $field) {
            $set[$field->name] = $field->toFieldSet();
        }

        return $set;
    }
}
