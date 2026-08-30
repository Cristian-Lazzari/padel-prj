<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixedSlotException extends Model
{
    use HasFactory;

    public const REASONS = ['festivo', 'sospensione', 'recupero'];

    protected $fillable = ['fixed_slot_id', 'date', 'reason', 'note'];

    protected $casts = ['date' => 'date'];

    public function fixedSlot()
    {
        return $this->belongsTo(FixedSlot::class);
    }

    public function reasonLabel(): string
    {
        return [
            'festivo' => 'Festivo',
            'sospensione' => 'Sospensione',
            'recupero' => 'Recupero',
        ][$this->reason] ?? $this->reason;
    }
}
