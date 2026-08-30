<?php

namespace App\Services;

use App\Models\Player;
use App\Models\Tournament;
use App\Models\TournamentRegistration;
use Illuminate\Support\Facades\DB;

/**
 * Iscrizioni ai tornei: creazione, ritiro e scorrimento della lista d'attesa.
 *
 * Sta in un servizio perché serve sia alle API pubbliche sia al back office,
 * e perché la decisione "confermato o lista d'attesa" deve avvenire sotto
 * lock per non superare teams_max con richieste concorrenti.
 */
class TournamentRegistrar
{
    public function __construct(private TournamentNotifier $notifier)
    {
    }

    /**
     * Registra una squadra. Restituisce ['registration' => ...] oppure
     * ['error' => 'messaggio'] se una validazione di dominio non passa.
     */
    public function register(Tournament $tournament, Player $player, ?Player $partner, ?string $teamName, ?string $note = null): array
    {
        $result = DB::transaction(function () use ($tournament, $player, $partner, $teamName, $note) {
            /** @var Tournament $locked */
            $locked = Tournament::whereKey($tournament->id)->lockForUpdate()->first();

            if (! $locked || ! $locked->registration_open) {
                return ['error' => 'Le iscrizioni per questo torneo non sono aperte'];
            }

            if ($locked->is_pair && ! $partner) {
                return ['error' => 'Questo torneo è a coppie: devi indicare un compagno'];
            }

            if (! $locked->is_pair && $partner) {
                return ['error' => 'Questo torneo è singolo: non serve un compagno'];
            }

            if ($partner && $partner->id === $player->id) {
                return ['error' => 'Non puoi iscriverti in coppia con te stesso'];
            }

            if (! $locked->levelAllows($player->level)) {
                return ['error' => 'Il tuo livello non rientra in quello richiesto ('.$locked->levelLabel().')'];
            }

            if ($partner && ! $locked->levelAllows($partner->level)) {
                return ['error' => 'Il livello del tuo compagno non rientra in quello richiesto ('.$locked->levelLabel().')'];
            }

            // Un giocatore non può comparire due volte nello stesso torneo,
            // né come iscritto né come compagno di un'altra coppia.
            $ids = array_filter([$player->id, $partner?->id]);

            $busy = TournamentRegistration::where('tournament_id', $locked->id)
                ->whereNotIn('status', ['cancelled', 'rejected'])
                ->where(function ($q) use ($ids) {
                    $q->whereIn('player_id', $ids)->orWhereIn('partner_player_id', $ids);
                })
                ->first();

            if ($busy) {
                $isSelf = in_array($busy->player_id, [$player->id], true)
                    || in_array($busy->partner_player_id, [$player->id], true);

                return ['error' => $isSelf
                    ? 'Risulti già iscritto a questo torneo'
                    : 'Il giocatore che hai scelto come compagno è già iscritto a questo torneo'];
            }

            // Il conteggio sotto lock decide se c'è posto o si va in attesa.
            $confirmed = TournamentRegistration::where('tournament_id', $locked->id)
                ->where('status', 'confirmed')
                ->count();

            $status = $confirmed < $locked->teams_max ? 'confirmed' : 'waitlist';

            $attributes = [
                'partner_player_id' => $partner?->id,
                'team_name' => $teamName ?: null,
                'status' => $status,
                'note' => $note,
            ];

            // Chi si era ritirato ha ancora la sua riga (l'indice unico è su
            // tournament_id + player_id): va riattivata, non inserita di nuovo.
            $registration = TournamentRegistration::firstOrNew([
                'tournament_id' => $locked->id,
                'player_id' => $player->id,
            ]);

            $registration->fill($attributes);
            // Una nuova iscrizione riparte in fondo alla lista d'attesa.
            $registration->created_at = now();
            $registration->save();

            return ['registration' => $registration];
        });

        if (isset($result['registration'])) {
            $result['registration']->setRelation('tournament', $tournament);
            $result['registration']->load(['player', 'partner']);
            $this->notifier->registrationReceived($result['registration']);
        }

        return $result;
    }

    /**
     * Ritiro dell'iscrizione. Libera il posto e fa scorrere la lista d'attesa.
     */
    public function withdraw(TournamentRegistration $registration): void
    {
        $wasConfirmed = $registration->status === 'confirmed';

        $registration->status = 'cancelled';
        $registration->save();

        if ($wasConfirmed) {
            $this->promoteFromWaitlist($registration->tournament);
        }
    }

    /**
     * Cambio di stato dal back office. Gestisce notifiche e scorrimento
     * della lista d'attesa in base alla transizione.
     */
    public function changeStatus(TournamentRegistration $registration, string $status): array
    {
        $allowed = ['pending', 'confirmed', 'waitlist', 'rejected', 'cancelled'];

        if (! in_array($status, $allowed, true)) {
            return ['error' => 'Stato non valido'];
        }

        $previous = $registration->status;

        if ($previous === $status) {
            return ['registration' => $registration];
        }

        $tournament = $registration->tournament;

        if ($status === 'confirmed') {
            $confirmed = TournamentRegistration::where('tournament_id', $tournament->id)
                ->where('status', 'confirmed')
                ->where('id', '!=', $registration->id)
                ->count();

            if ($confirmed >= $tournament->teams_max) {
                return ['error' => 'Il torneo ha già '.$tournament->teams_max.' squadre confermate: aumenta i posti o libera una squadra.'];
            }
        }

        $registration->status = $status;
        $registration->save();

        if ($status === 'confirmed') {
            $registration->load(['player', 'partner']);
            $registration->setRelation('tournament', $tournament);
            $this->notifier->registrationConfirmed($registration);
        }

        // Se una squadra confermata esce, il primo in attesa entra.
        if ($previous === 'confirmed' && $status !== 'confirmed') {
            $this->promoteFromWaitlist($tournament);
        }

        return ['registration' => $registration];
    }

    /**
     * Promuove la prima iscrizione in lista d'attesa, se c'è posto.
     * Restituisce l'iscrizione promossa o null.
     */
    public function promoteFromWaitlist(Tournament $tournament): ?TournamentRegistration
    {
        $promoted = DB::transaction(function () use ($tournament) {
            $locked = Tournament::whereKey($tournament->id)->lockForUpdate()->first();

            $confirmed = TournamentRegistration::where('tournament_id', $locked->id)
                ->where('status', 'confirmed')
                ->count();

            if ($confirmed >= $locked->teams_max) {
                return null;
            }

            $next = TournamentRegistration::where('tournament_id', $locked->id)
                ->where('status', 'waitlist')
                ->orderBy('created_at')
                ->orderBy('id')
                ->first();

            if (! $next) {
                return null;
            }

            $next->status = 'confirmed';
            $next->save();

            return $next;
        });

        if ($promoted) {
            $promoted->setRelation('tournament', $tournament);
            $promoted->load(['player', 'partner']);
            $this->notifier->registrationConfirmed($promoted);
        }

        return $promoted;
    }
}
