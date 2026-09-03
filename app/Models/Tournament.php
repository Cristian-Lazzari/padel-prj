<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Tournament extends Model
{
    use HasFactory;

    /** Stati in cui il torneo è visibile sul sito clienti. */
    public const PUBLIC_STATUSES = ['open', 'closed', 'running', 'finished'];

    public const FORMATS = ['gironi', 'eliminazione', 'americano'];

    public const STATUSES = ['draft', 'open', 'closed', 'running', 'finished', 'cancelled'];

    protected $fillable = [
        'name', 'slug', 'description', 'regulation', 'cover', 'type', 'format',
        'level_min', 'level_max', 'teams_max', 'is_pair', 'price',
        'starts_at', 'ends_at', 'registration_opens_at', 'registration_closes_at',
        'status', 'location', 'note', 'fields',
    ];

    protected $casts = [
        'is_pair' => 'boolean',
        'level_min' => 'integer',
        'level_max' => 'integer',
        'teams_max' => 'integer',
        'price' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'registration_opens_at' => 'datetime',
        'registration_closes_at' => 'datetime',
        'fields' => 'array',
    ];

    /**
     * Attributi calcolati esposti solo dove servono: appenderli sempre
     * costerebbe una query per riga nelle liste.
     */
    public const PUBLIC_APPENDS = ['cover_url', 'spots_left', 'is_full', 'registration_open'];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    // ==========================================================
    // Relazioni
    // ==========================================================

    public function registrations()
    {
        return $this->hasMany(TournamentRegistration::class);
    }

    /** Le iscrizioni che occupano un posto. */
    public function confirmedRegistrations()
    {
        return $this->hasMany(TournamentRegistration::class)->where('status', 'confirmed');
    }

    public function waitlistRegistrations()
    {
        return $this->hasMany(TournamentRegistration::class)->where('status', 'waitlist');
    }

    public function matches()
    {
        return $this->hasMany(TournamentMatch::class)->orderBy('position');
    }

    // ==========================================================
    // Scope
    // ==========================================================

    /** Tornei visibili sul sito clienti. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereIn('status', self::PUBLIC_STATUSES);
    }

    /** Tornei che accettano iscrizioni adesso. */
    public function scopeOpenForRegistration(Builder $query): Builder
    {
        return $query->where('status', 'open')
            ->where(function ($q) {
                $q->whereNull('registration_opens_at')
                  ->orWhere('registration_opens_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('registration_closes_at')
                  ->orWhere('registration_closes_at', '>=', now());
            });
    }

    /**
     * Tornei che occupano il calendario in un intervallo di date.
     * Gli annullati non occupano niente.
     */
    public function scopeInPeriod(Builder $query, $from, $to): Builder
    {
        return $query->where('status', '!=', 'cancelled')
            ->whereDate('starts_at', '<=', $to)
            ->where(function ($q) use ($from) {
                // Senza data di fine il torneo dura il solo giorno di inizio
                $q->whereDate('ends_at', '>=', $from)
                  ->orWhere(function ($q2) use ($from) {
                      $q2->whereNull('ends_at')->whereDate('starts_at', '>=', $from);
                  });
            });
    }

    // ==========================================================
    // Campi impegnati
    // ==========================================================

    /** Ultimo giorno occupato: senza ends_at è lo stesso giorno di inizio. */
    public function lastDay(): ?Carbon
    {
        if ($this->ends_at) {
            return $this->ends_at->copy()->endOfDay();
        }

        return $this->starts_at ? $this->starts_at->copy()->endOfDay() : null;
    }

    /** Elenco dei giorni occupati, in formato Y-m-d. */
    public function occupiedDays(): array
    {
        if (! $this->starts_at) {
            return [];
        }

        $giorni = [];
        $cursore = $this->starts_at->copy()->startOfDay();
        $fine = $this->lastDay();

        // Un tetto di sicurezza: un torneo più lungo di due mesi è un errore di dati
        for ($i = 0; $i < 62 && $cursore->lte($fine); $i++) {
            $giorni[] = $cursore->format('Y-m-d');
            $cursore->addDay();
        }

        return $giorni;
    }

    public function occupiedFields(): array
    {
        return array_values(array_filter((array) ($this->fields ?? [])));
    }

    public function fieldsLabel(): string
    {
        $campi = $this->occupiedFields();

        return $campi ? implode(', ', $campi) : 'Campi da assegnare';
    }

    /** True se i due tornei si pestano i piedi: stesse date e almeno un campo in comune. */
    public function clashesWith(self $altro): bool
    {
        $comuni = array_intersect($this->occupiedFields(), $altro->occupiedFields());

        if (! $comuni || ! $this->starts_at || ! $altro->starts_at) {
            return false;
        }

        return $this->starts_at->copy()->startOfDay()->lte($altro->lastDay())
            && $altro->starts_at->copy()->startOfDay()->lte($this->lastDay());
    }

    /**
     * Tornei già in calendario che occuperebbero gli stessi campi negli stessi
     * giorni. $ignoreId serve in modifica, per non litigare con se stesso.
     */
    public static function clashes(array $fields, $startsAt, $endsAt, ?int $ignoreId = null)
    {
        $fields = array_values(array_filter($fields));

        if (! $fields || ! $startsAt) {
            return collect();
        }

        $sonda = new self([
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'fields' => $fields,
        ]);

        return self::query()
            ->where('status', '!=', 'cancelled')
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->whereNotNull('fields')
            // Prefiltro sulle date in SQL, l'intersezione dei campi in PHP:
            // i campi sono un JSON e il confronto varia troppo tra i motori.
            ->whereDate('starts_at', '<=', $sonda->lastDay())
            ->get()
            ->filter(fn (self $t) => $t->clashesWith($sonda))
            ->values();
    }

    // ==========================================================
    // Accessor
    // ==========================================================

    public function getCoverUrlAttribute(): ?string
    {
        $cover = $this->attributes['cover'] ?? null;

        return $cover ? asset('storage/'.ltrim($cover, '/')) : null;
    }

    /**
     * Squadre confermate. Usa confirmed_registrations_count quando la query
     * ha fatto withCount(), poi la relazione già caricata.
     */
    public function getTeamsCountAttribute(): int
    {
        if (isset($this->attributes['confirmed_registrations_count'])) {
            return (int) $this->attributes['confirmed_registrations_count'];
        }

        if ($this->relationLoaded('confirmedRegistrations')) {
            return $this->getRelation('confirmedRegistrations')->count();
        }

        return $this->confirmedRegistrations()->count();
    }

    public function getSpotsLeftAttribute(): int
    {
        return max(0, (int) $this->teams_max - $this->teams_count);
    }

    public function getIsFullAttribute(): bool
    {
        return $this->teams_count >= (int) $this->teams_max;
    }

    /** True se in questo momento si può inviare un'iscrizione. */
    public function getRegistrationOpenAttribute(): bool
    {
        if ($this->status !== 'open') {
            return false;
        }

        if ($this->registration_opens_at && $this->registration_opens_at->isFuture()) {
            return false;
        }

        if ($this->registration_closes_at && $this->registration_closes_at->isPast()) {
            return false;
        }

        return true;
    }

    // ==========================================================
    // Helper
    // ==========================================================

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

    public function formatLabel(): string
    {
        return [
            'gironi' => 'Gironi',
            'eliminazione' => 'Eliminazione diretta',
            'americano' => 'Americano',
        ][$this->format] ?? ucfirst((string) $this->format);
    }

    public function statusLabel(): string
    {
        return [
            'draft' => 'Bozza',
            'open' => 'Iscrizioni aperte',
            'closed' => 'Iscrizioni chiuse',
            'running' => 'In corso',
            'finished' => 'Concluso',
            'cancelled' => 'Annullato',
        ][$this->status] ?? $this->status;
    }

    /** Elimina la copertina dal disco pubblico. */
    public function deleteCoverFile(): void
    {
        $cover = $this->attributes['cover'] ?? null;

        if ($cover && Storage::disk('public')->exists($cover)) {
            Storage::disk('public')->delete($cover);
        }
    }

    /** Slug univoco derivato dal nome. */
    public static function makeSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'torneo';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
