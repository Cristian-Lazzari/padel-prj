<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Campo fisso: uno slot ricorrente riservato a un giocatore.
 *
 * Le singole occorrenze vengono materializzate in reservations da
 * FixedSlotService, così tutta la logica di disponibilità già esistente
 * le vede occupate senza modifiche.
 */
class FixedSlot extends Model
{
    use HasFactory;

    /** Etichette dei giorni secondo Carbon::dayOfWeek (0 = domenica). */
    public const WEEKDAYS = [
        0 => 'Domenica',
        1 => 'Lunedì',
        2 => 'Martedì',
        3 => 'Mercoledì',
        4 => 'Giovedì',
        5 => 'Venerdì',
        6 => 'Sabato',
    ];

    protected $fillable = [
        'player_id', 'field', 'weekday', 'start_time', 'duration',
        'valid_from', 'valid_to', 'status', 'price', 'note', 'created_by',
    ];

    protected $casts = [
        'weekday' => 'integer',
        'duration' => 'integer',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'price' => 'decimal:2',
    ];

    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    public function exceptions()
    {
        return $this->hasMany(FixedSlotException::class)->orderBy('date');
    }

    /** Prenotazioni generate da questo campo fisso. */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ==========================================================
    // Scope
    // ==========================================================

    /** Campi fissi che occupano il campo in questo momento. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /** Campi fissi validi in una certa data. */
    public function scopeValidOn(Builder $query, $date): Builder
    {
        $date = Carbon::parse($date)->format('Y-m-d');

        return $query->where('valid_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('valid_to')->orWhere('valid_to', '>=', $date);
            });
    }

    // ==========================================================
    // Helper
    // ==========================================================

    public function weekdayLabel(): string
    {
        return self::WEEKDAYS[$this->weekday] ?? '—';
    }

    public function statusLabel(): string
    {
        return [
            'active' => 'Attivo',
            'suspended' => 'Sospeso',
            'ended' => 'Concluso',
        ][$this->status] ?? $this->status;
    }

    /** Orario di fine calcolato sulla durata dello slot del campo. */
    public function endTime(int $minutesPerUnit): string
    {
        return Carbon::createFromFormat('H:i', $this->start_time)
            ->addMinutes($minutesPerUnit * $this->duration)
            ->format('H:i');
    }

    /** True se la ricorrenza è valida (e non eccettuata) in quella data. */
    public function occursOn(Carbon $date, ?array $exceptionDates = null): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($date->dayOfWeek !== $this->weekday) {
            return false;
        }

        if ($this->valid_from && $date->lt($this->valid_from->copy()->startOfDay())) {
            return false;
        }

        if ($this->valid_to && $date->gt($this->valid_to->copy()->endOfDay())) {
            return false;
        }

        $exceptions = $exceptionDates ?? $this->exceptions->pluck('date')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
            ->all();

        return ! in_array($date->format('Y-m-d'), $exceptions, true);
    }
}
