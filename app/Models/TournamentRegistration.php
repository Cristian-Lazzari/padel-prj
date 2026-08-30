<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TournamentRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id', 'player_id', 'partner_player_id',
        'team_name', 'status', 'paid', 'note',
    ];

    protected $casts = [
        'paid' => 'boolean',
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function player()
    {
        return $this->belongsTo(Player::class, 'player_id');
    }

    public function partner()
    {
        return $this->belongsTo(Player::class, 'partner_player_id');
    }

    /**
     * Nome con cui la squadra compare in calendario e classifica.
     * Se il gestore non ha scelto un nome si usano i nickname.
     */
    public function displayName(): string
    {
        if ($this->team_name) {
            return $this->team_name;
        }

        $names = [];

        if ($this->relationLoaded('player') ? $this->player : $this->player()->first()) {
            $names[] = $this->player->nickname;
        }

        if ($this->partner_player_id) {
            $partner = $this->relationLoaded('partner') ? $this->partner : $this->partner()->first();
            if ($partner) {
                $names[] = $partner->nickname;
            }
        }

        return $names ? implode(' / ', $names) : 'Squadra #'.$this->id;
    }

    public function statusLabel(): string
    {
        return [
            'pending' => 'In attesa di conferma',
            'confirmed' => 'Confermata',
            'waitlist' => 'Lista d\'attesa',
            'rejected' => 'Rifiutata',
            'cancelled' => 'Ritirata',
        ][$this->status] ?? $this->status;
    }
}
