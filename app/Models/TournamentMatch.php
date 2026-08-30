<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TournamentMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id', 'round', 'group_name', 'position', 'reservation_id',
        'team_a_id', 'team_b_id', 'score', 'winner', 'played_at', 'status',
    ];

    protected $casts = [
        'score' => 'array',
        'played_at' => 'datetime',
        'position' => 'integer',
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function teamA()
    {
        return $this->belongsTo(TournamentRegistration::class, 'team_a_id');
    }

    public function teamB()
    {
        return $this->belongsTo(TournamentRegistration::class, 'team_b_id');
    }

    /** Slot campo agganciato, se il gestore ne ha collegato uno. */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    /** Punteggio leggibile: "6-4 6-3". */
    public function scoreLabel(): string
    {
        if (! is_array($this->score) || ! count($this->score)) {
            return '';
        }

        return collect($this->score)
            ->map(fn ($set) => ($set['a'] ?? 0).'-'.($set['b'] ?? 0))
            ->implode(' ');
    }

    /**
     * Converte "6-4 6-3" (o "6-4, 6-3") nell'array di set salvato in JSON.
     * Restituisce null se la stringa non contiene set validi.
     */
    public static function parseScore(?string $raw): ?array
    {
        if (! $raw || ! trim($raw)) {
            return null;
        }

        $sets = [];

        foreach (preg_split('/[\s,;]+/', trim($raw)) as $chunk) {
            if (! preg_match('/^(\d{1,2})\s*[-\/:]\s*(\d{1,2})$/', $chunk, $m)) {
                continue;
            }

            $sets[] = ['a' => (int) $m[1], 'b' => (int) $m[2]];
        }

        return $sets ?: null;
    }

    /** Vincitore dedotto dai set vinti: 'a', 'b' o 'draw'. */
    public static function winnerFromScore(?array $sets): ?string
    {
        if (! $sets) {
            return null;
        }

        $a = 0;
        $b = 0;

        foreach ($sets as $set) {
            if (($set['a'] ?? 0) > ($set['b'] ?? 0)) {
                $a++;
            } elseif (($set['b'] ?? 0) > ($set['a'] ?? 0)) {
                $b++;
            }
        }

        if ($a === $b) {
            return 'draw';
        }

        return $a > $b ? 'a' : 'b';
    }
}
