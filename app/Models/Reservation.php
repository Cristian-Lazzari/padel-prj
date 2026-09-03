<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Player;

class Reservation extends Model
{
    use HasFactory;

    /** Prenotazione confermata (la colonna status è una stringa). */
    public const STATUS_CONFIRMED = '1';

    /** Categorie ammesse per una partita aperta. */
    public const CATEGORIES = ['match', 'lesson', 'tournament'];

    /**
     * Quanto dura una prenotazione fatta dal cliente: un blocco unico di
     * un'ora e mezza. Non esistono più "slot cliente": l'orario di partenza
     * è libero sulla griglia del campo, la durata è sempre questa.
     */
    public const CLIENT_MINUTES = 90;

    /**
     * Espressione SQL che converte date_slot (varchar 'Y-m-d H:i') in datetime.
     * Serve in ogni confronto temporale perché la colonna non è un DATETIME.
     */
    public const SLOT_AS_DATETIME = "STR_TO_DATE(date_slot, '%Y-%m-%d %H:%i')";

    protected $casts = [
        'is_open' => 'boolean',
        'slots_total' => 'integer',
        'level_min' => 'integer',
        'level_max' => 'integer',
        'open_closes_at' => 'datetime',
    ];

    /**
     * Attributi calcolati esposti solo dove servono davvero.
     * Non sono in $appends di proposito: serializzare una lista di
     * prenotazioni senza withCount() farebbe una query per riga.
     */
    public const OPEN_APPENDS = ['slots_taken', 'slots_left', 'is_full'];

    // ==========================================================
    // Relazioni
    // ==========================================================

    public function players()
    {
        return $this->belongsToMany(Player::class)
            ->withPivot(['join_status', 'joined_at', 'is_owner']);
    }

    /** Solo le iscrizioni confermate: sono quelle che occupano un posto. */
    public function acceptedPlayers()
    {
        return $this->players()->wherePivot('join_status', 'accepted');
    }

    /** Il giocatore che ha effettuato la prenotazione. */
    public function owner()
    {
        return $this->belongsTo(Player::class, 'booking_subject');
    }

    // ==========================================================
    // Scope
    // ==========================================================

    /**
     * Partite aperte pubblicabili: aperte, confermate, con lo slot ancora
     * da giocare, le iscrizioni ancora aperte e almeno un posto libero.
     */
    public function scopeOpenPublished(Builder $query): Builder
    {
        return $query
            ->where('is_open', true)
            ->where('status', self::STATUS_CONFIRMED)
            ->whereRaw(self::SLOT_AS_DATETIME.' > ?', [now()->format('Y-m-d H:i:s')])
            ->where(function ($q) {
                $q->whereNull('open_closes_at')
                  ->orWhere('open_closes_at', '>', now());
            })
            ->whereNotNull('slots_total')
            ->whereRaw('('.self::acceptedCountSql().') < reservations.slots_total');
    }

    /** Sotto-query con il numero di iscritti confermati. */
    /** Quante fasce del campo servono per coprire la prenotazione del cliente. */
    public static function clientSlots(int $mDuring): int
    {
        return (int) max(1, ceil(self::CLIENT_MINUTES / max(1, $mDuring)));
    }

    public static function acceptedCountSql(): string
    {
        return "select count(*) from player_reservation pr
                where pr.reservation_id = reservations.id
                and pr.join_status = 'accepted'";
    }

    /** Ordina per orario di gioco, non per id. */
    public function scopeOrderBySlot(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderByRaw(self::SLOT_AS_DATETIME.' '.($direction === 'desc' ? 'desc' : 'asc'));
    }

    // ==========================================================
    // Accessor
    // ==========================================================

    /**
     * Posti occupati. Usa accepted_players_count quando la query ha fatto
     * withCount(), poi la relazione già caricata, e solo come ultima
     * risorsa interroga il database (evita N+1 nelle liste).
     */
    public function getSlotsTakenAttribute(): int
    {
        if (isset($this->attributes['accepted_players_count'])) {
            return (int) $this->attributes['accepted_players_count'];
        }

        if ($this->relationLoaded('acceptedPlayers')) {
            return $this->getRelation('acceptedPlayers')->count();
        }

        if ($this->relationLoaded('players')) {
            return $this->getRelation('players')
                ->where('pivot.join_status', 'accepted')
                ->count();
        }

        // Modello singolo senza relazioni caricate: una query mirata.
        return $this->acceptedPlayers()->count();
    }

    public function getSlotsLeftAttribute(): int
    {
        if (! $this->slots_total) {
            return 0;
        }

        return max(0, $this->slots_total - $this->slots_taken);
    }

    public function getIsFullAttribute(): bool
    {
        return $this->slots_total ? $this->slots_taken >= $this->slots_total : false;
    }

    // ==========================================================
    // Helper di dominio
    // ==========================================================

    /** Inizio dello slot come oggetto Carbon. */
    public function slotStartsAt(): ?Carbon
    {
        if (! $this->date_slot) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d H:i', trim($this->date_slot));
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** True se le iscrizioni sono ancora aperte. */
    public function joinWindowIsOpen(): bool
    {
        $deadline = $this->open_closes_at ?: $this->slotStartsAt();

        return $deadline ? $deadline->isFuture() : false;
    }

    /** True se il livello del giocatore rientra nel range richiesto. */
    public function levelAllows(?int $level): bool
    {
        $level = $level ?: 1;

        if ($this->level_min && $level < $this->level_min) {
            return false;
        }

        if ($this->level_max && $level > $this->level_max) {
            return false;
        }

        return true;
    }

    /** Etichetta leggibile del range di livello. */
    public function levelLabel(): string
    {
        if (! $this->level_min && ! $this->level_max) {
            return 'Tutti i livelli';
        }

        if ($this->level_min && $this->level_max) {
            return $this->level_min === $this->level_max
                ? 'Livello '.$this->level_min
                : 'Livello '.$this->level_min.'-'.$this->level_max;
        }

        return $this->level_min ? 'Livello '.$this->level_min.'+' : 'Fino al livello '.$this->level_max;
    }
}
