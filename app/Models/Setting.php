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

    /** Elenco dei campi configurati (chiave => impostazioni del campo). */
    public static function fieldSet(): array
    {
        return self::props('advanced')['field_set'] ?? [];
    }
}
