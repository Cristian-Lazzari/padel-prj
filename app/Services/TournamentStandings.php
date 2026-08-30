<?php

namespace App\Services;

use App\Models\Tournament;
use Illuminate\Support\Collection;

/**
 * Calcolo delle classifiche di un torneo.
 *
 * Il calcolo sta qui e non nel componente Vue: il frontend riceve
 * le righe già ordinate e non deve conoscere le regole del punteggio.
 *
 * Criteri, nell'ordine: punti, differenza set, differenza game,
 * game vinti, nome squadra.
 */
class TournamentStandings
{
    /** Punti assegnati per una vittoria e per un pareggio. */
    public const POINTS_WIN = 3;
    public const POINTS_DRAW = 1;

    /**
     * Classifica per girone.
     * Restituisce una collezione di ['group' => 'Girone A', 'rows' => [...]].
     */
    public function forTournament(Tournament $tournament): Collection
    {
        $matches = $tournament->relationLoaded('matches')
            ? $tournament->getRelation('matches')
            : $tournament->matches()->with(['teamA.player', 'teamA.partner', 'teamB.player', 'teamB.partner'])->get();

        $played = $matches->where('status', 'played');

        // Le squadre partecipano alla classifica del girone in cui giocano.
        // Se non ci sono gironi finisce tutto in un'unica tabella.
        $groups = [];

        foreach ($played as $match) {
            $group = $match->group_name ?: ($match->round ?: 'Classifica');

            foreach (['a', 'b'] as $side) {
                $team = $side === 'a' ? $match->teamA : $match->teamB;

                if (! $team) {
                    continue;
                }

                $groups[$group][$team->id] ??= $this->emptyRow($team);
            }

            $this->applyMatch($groups[$group], $match);
        }

        return collect($groups)
            ->map(fn ($rows, $group) => [
                'group' => $group,
                'rows' => $this->sort(collect($rows))->values()->all(),
            ])
            ->sortBy('group')
            ->values();
    }

    private function emptyRow($team): array
    {
        return [
            'team_id' => $team->id,
            'team' => $team->displayName(),
            'played' => 0,
            'won' => 0,
            'drawn' => 0,
            'lost' => 0,
            'sets_won' => 0,
            'sets_lost' => 0,
            'games_won' => 0,
            'games_lost' => 0,
            'points' => 0,
        ];
    }

    /** Somma un incontro giocato alle righe del girone. */
    private function applyMatch(array &$rows, $match): void
    {
        $sets = is_array($match->score) ? $match->score : [];

        $tally = ['a' => ['sets' => 0, 'games' => 0], 'b' => ['sets' => 0, 'games' => 0]];

        foreach ($sets as $set) {
            $ga = (int) ($set['a'] ?? 0);
            $gb = (int) ($set['b'] ?? 0);

            $tally['a']['games'] += $ga;
            $tally['b']['games'] += $gb;

            if ($ga > $gb) {
                $tally['a']['sets']++;
            } elseif ($gb > $ga) {
                $tally['b']['sets']++;
            }
        }

        $winner = $match->winner ?: \App\Models\TournamentMatch::winnerFromScore($sets);

        foreach (['a' => 'b', 'b' => 'a'] as $side => $other) {
            $team = $side === 'a' ? $match->teamA : $match->teamB;

            if (! $team || ! isset($rows[$team->id])) {
                continue;
            }

            $row = &$rows[$team->id];
            $row['played']++;
            $row['sets_won'] += $tally[$side]['sets'];
            $row['sets_lost'] += $tally[$other]['sets'];
            $row['games_won'] += $tally[$side]['games'];
            $row['games_lost'] += $tally[$other]['games'];

            if ($winner === $side) {
                $row['won']++;
                $row['points'] += self::POINTS_WIN;
            } elseif ($winner === 'draw') {
                $row['drawn']++;
                $row['points'] += self::POINTS_DRAW;
            } elseif ($winner !== null) {
                $row['lost']++;
            }

            unset($row);
        }
    }

    private function sort(Collection $rows): Collection
    {
        return $rows->sort(function ($x, $y) {
            return [
                $y['points'],
                $y['sets_won'] - $y['sets_lost'],
                $y['games_won'] - $y['games_lost'],
                $y['games_won'],
                $x['team'],
            ] <=> [
                $x['points'],
                $x['sets_won'] - $x['sets_lost'],
                $x['games_won'] - $x['games_lost'],
                $x['games_won'],
                $y['team'],
            ];
        });
    }
}
