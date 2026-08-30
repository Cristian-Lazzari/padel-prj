<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Annuncio della bacheca compravendita.
 *
 * Nessun pagamento online e nessuna messaggistica interna: il contatto
 * avviene direttamente fra i due giocatori.
 */
class Listing extends Model
{
    use HasFactory;

    public const CATEGORIES = ['racchette', 'abbigliamento', 'scarpe', 'accessori', 'altro'];
    public const CONDITIONS = ['nuovo', 'come nuovo', 'usato'];

    /** Numero massimo di annunci attivi per giocatore. */
    public const MAX_ACTIVE_PER_PLAYER = 5;

    /** Numero massimo di immagini per annuncio. */
    public const MAX_IMAGES = 4;

    /** Stati che contano verso il limite di annunci attivi. */
    public const ACTIVE_STATUSES = ['pending', 'published'];

    protected $fillable = [
        'player_id', 'title', 'description', 'category', 'condition', 'price',
        'contact_phone', 'contact_mail', 'show_phone', 'show_mail',
        'status', 'reject_reason', 'expires_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'show_phone' => 'boolean',
        'show_mail' => 'boolean',
        'expires_at' => 'datetime',
        'views' => 'integer',
    ];

    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    public function images()
    {
        return $this->hasMany(ListingImage::class)->orderBy('position');
    }

    // ==========================================================
    // Scope
    // ==========================================================

    /**
     * Annunci visibili in bacheca: pubblicati e non scaduti.
     * È l'unico filtro usato dagli endpoint pubblici.
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    // ==========================================================
    // Helper
    // ==========================================================

    public function statusLabel(): string
    {
        return [
            'pending' => 'In attesa di approvazione',
            'published' => 'Pubblicato',
            'sold' => 'Venduto',
            'rejected' => 'Rifiutato',
            'expired' => 'Scaduto',
        ][$this->status] ?? $this->status;
    }

    public function priceLabel(): string
    {
        return $this->price !== null
            ? number_format((float) $this->price, 2, ',', '.').' €'
            : 'Trattabile';
    }

    /** True se l'annuncio è ancora visibile in bacheca. */
    public function isVisible(): bool
    {
        return $this->status === 'published'
            && (! $this->expires_at || $this->expires_at->isFuture());
    }

    /** Durata di default configurata dal back office, in giorni. */
    public static function defaultDurationDays(): int
    {
        $setting = Setting::where('name', 'Bacheca annunci')->first();

        if (! $setting) {
            return 30;
        }

        $property = json_decode($setting->property, true);

        return (int) ($property['default_days'] ?? 30) ?: 30;
    }
}
