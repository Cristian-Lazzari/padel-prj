<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\Reservation;

class Player extends Model
{
    use HasFactory;

    /**
     * Campi mai esposti nelle risposte API.
     */
    protected $hidden = ['otp'];

    protected $casts = [
        'mail_verified_at' => 'datetime',
        'otp_sent_at' => 'datetime',
        'certificate_expires_at' => 'date',
        'birth_date' => 'date',
    ];

    /**
     * Attributi calcolati sempre presenti nel JSON.
     */
    protected $appends = ['img_url', 'mail_verified', 'certificate_status'];

    public function reservations()
    {
        return $this->belongsToMany(Reservation::class);
    }

    /**
     * URL assoluto della foto profilo (null se non caricata).
     */
    public function getImgUrlAttribute()
    {
        $img = $this->attributes['img'] ?? null;

        if (! $img) {
            return null;
        }

        return asset('storage/'.ltrim($img, '/'));
    }

    /**
     * Comodo booleano per il frontend.
     */
    public function getMailVerifiedAttribute()
    {
        return ! empty($this->attributes['mail_verified_at']);
    }

    public function isMailVerified()
    {
        return ! empty($this->attributes['mail_verified_at']);
    }

    /**
     * Stato del certificato medico: missing | expired | expiring | valid.
     * "expiring" scatta a 30 giorni dalla scadenza.
     */
    public function getCertificateStatusAttribute()
    {
        $certificate = $this->attributes['certificate'] ?? null;
        $expires = $this->attributes['certificate_expires_at'] ?? null;

        if (! $certificate && ! $expires) {
            return 'missing';
        }

        if (! $expires) {
            return 'valid';
        }

        $date = Carbon::parse($expires)->endOfDay();

        if ($date->isPast()) {
            return 'expired';
        }

        if ($date->lessThanOrEqualTo(now()->addDays(30))) {
            return 'expiring';
        }

        return 'valid';
    }

    /**
     * Elimina la foto profilo dal disco pubblico (se presente).
     */
    public function deleteImgFile()
    {
        $img = $this->attributes['img'] ?? null;

        if ($img && Storage::disk('public')->exists($img)) {
            Storage::disk('public')->delete($img);
        }
    }
}
