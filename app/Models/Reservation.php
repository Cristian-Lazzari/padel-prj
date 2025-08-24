<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Player;

class Reservation extends Model
{
    use HasFactory;

    public function players() {
        return $this->belongsToMany(Player::class);
    }
}
