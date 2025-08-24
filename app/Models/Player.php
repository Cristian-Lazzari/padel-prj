<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Reservation;

class Player extends Model
{
    use HasFactory;

    public function reservations() {
        return $this->belongsToMany(Reservation::class);
    }
}
