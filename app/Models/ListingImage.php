<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ListingImage extends Model
{
    use HasFactory;

    protected $fillable = ['listing_id', 'path', 'position'];

    protected $casts = ['position' => 'integer'];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function getUrlAttribute(): ?string
    {
        $path = $this->attributes['path'] ?? null;

        return $path ? asset('storage/'.ltrim($path, '/')) : null;
    }

    /** Rimuove il file dal disco pubblico. */
    public function deleteFile(): void
    {
        $path = $this->attributes['path'] ?? null;

        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
