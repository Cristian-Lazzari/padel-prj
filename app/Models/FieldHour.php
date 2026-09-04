<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * L'orario di un campo in un giorno della settimana.
 * `weekday` segue la numerazione ISO: 1 = lunedì … 7 = domenica.
 */
class FieldHour extends Model
{
    use HasFactory;

    protected $fillable = ['field_id', 'weekday', 'closed', 'h_start', 'h_end'];

    protected $casts = [
        'weekday' => 'integer',
        'closed' => 'boolean',
    ];

    public const WEEKDAYS = [
        1 => 'Lunedì',
        2 => 'Martedì',
        3 => 'Mercoledì',
        4 => 'Giovedì',
        5 => 'Venerdì',
        6 => 'Sabato',
        7 => 'Domenica',
    ];

    public function field()
    {
        return $this->belongsTo(Field::class);
    }

    /** Quanto dura l'apertura, in minuti. Zero se il giorno è chiuso. */
    public function minutes(): int
    {
        if ($this->closed || ! $this->h_start || ! $this->h_end) {
            return 0;
        }

        $start = Carbon::createFromFormat('H:i', $this->h_start);
        $end = Carbon::createFromFormat('H:i', $this->h_end);

        // Una chiusura "prima" dell'apertura vuol dire dopo la mezzanotte.
        if ($end->lte($start)) {
            $end->addDay();
        }

        return $start->diffInMinutes($end);
    }
}
