<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Un campo del circolo, con i suoi orari giorno per giorno.
 *
 * Il nome resta la chiave con cui il campo compare altrove: `reservations.field`
 * e `fixed_slots.field` contengono quella stringa, non l'id.
 */
class Field extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'm_during', 'm_during_client', 'sort'];

    protected $casts = [
        'm_during' => 'integer',
        'm_during_client' => 'integer',
        'sort' => 'integer',
    ];

    public function hours()
    {
        return $this->hasMany(FieldHour::class)->orderBy('weekday');
    }

    /**
     * Il campo nella forma che tutto il gestionale già conosce, più la
     * chiave `hours` con l'orario di ciascun giorno.
     *
     * `h_start` e `n_slot` restano per i punti che non sono ancora passati
     * agli orari per giorno: sono l'apertura più mattiniera e la giornata
     * più lunga fra quelle configurate.
     */
    public function toFieldSet(): array
    {
        $hours = [];
        $closedDays = [];
        $open = null;
        $span = 0;

        foreach ($this->hours as $h) {
            $hours[$h->weekday] = [
                'closed' => (bool) $h->closed,
                'h_start' => $h->h_start,
                'h_end' => $h->h_end,
            ];

            if ($h->closed || ! $h->h_start || ! $h->h_end) {
                $closedDays[] = $h->weekday;
                continue;
            }

            $open = $open === null ? $h->h_start : min($open, $h->h_start);
            $span = max($span, $h->minutes());
        }

        $client = max(1, $this->m_during_client);

        return [
            'h_start' => $open ?? '08:00',
            'n_slot' => (int) floor($span / $client),
            'm_during' => $this->m_during,
            'm_during_client' => $this->m_during_client,
            'type' => $this->type,
            'closed_days' => $closedDays,
            'hours' => $hours,
        ];
    }
}
