<?php

namespace App\Services;

use App\Mail\OpenMatchNotification;
use App\Models\Player;
use App\Models\Reservation;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Invio delle notifiche relative alle partite aperte.
 *
 * Ogni invio è racchiuso in un try/catch: un problema SMTP non deve mai
 * far fallire l'iscrizione, che a quel punto è già salvata a database.
 */
class OpenMatchNotifier
{
    /** Nuovo iscritto: avvisa chi ha pubblicato la partita. */
    public function playerJoined(Reservation $reservation, Player $player): void
    {
        $owner = Player::find($reservation->booking_subject);

        if (! $owner || ! $owner->mail) {
            return;
        }

        $left = max(0, (int) $reservation->slots_total - $this->takenCount($reservation));

        $this->send($owner->mail, [
            'subject' => $player->nickname.' si è iscritto alla tua partita',
            'title' => '#'.$player->nickname.' si è iscritto alla tua partita',
            'subtitle' => $left > 0
                ? ($left === 1 ? 'Manca ancora 1 giocatore.' : 'Mancano ancora '.$left.' giocatori.')
                : 'La partita è al completo.',
            'reservation' => $reservation,
            'player' => $player,
        ]);
    }

    /** Partita al completo: avvisa owner e iscritti. */
    public function matchIsFull(Reservation $reservation): void
    {
        $recipients = $reservation->acceptedPlayers()
            ->get(['players.id', 'players.nickname', 'players.mail'])
            ->pluck('mail')
            ->filter()
            ->values();

        $owner = Player::find($reservation->booking_subject);

        if ($owner && $owner->mail) {
            $recipients = $recipients->push($owner->mail);
        }

        $recipients = $recipients->unique();

        if ($recipients->isEmpty()) {
            return;
        }

        $body = [
            'subject' => 'La partita è al completo',
            'title' => 'La partita è al completo',
            'subtitle' => 'Siete al numero giusto di giocatori: ci vediamo in campo.',
            'reservation' => $reservation,
            'player' => null,
        ];

        foreach ($recipients as $mail) {
            $this->send($mail, $body);
        }
    }

    private function takenCount(Reservation $reservation): int
    {
        return $reservation->acceptedPlayers()->count();
    }

    private function send(string $to, array $body): void
    {
        $reservation = $body['reservation'];
        $contactSetting = Setting::where('name', 'Contatti')->first();
        $contact = $contactSetting ? json_decode($contactSetting->property, true) : [];

        $start = $reservation->slotStartsAt();

        $payload = [
            'subject' => $body['subject'],
            'title' => $body['title'],
            'subtitle' => $body['subtitle'],
            'field' => $reservation->field,
            'date_label' => $start
                ? ucfirst(Carbon::instance($start)->locale('it')->translatedFormat('l j F \a\l\l\e H:i'))
                : $reservation->date_slot,
            'category' => $this->categoryLabel($reservation),
            'slots_total' => (int) $reservation->slots_total,
            'slots_taken' => $this->takenCount($reservation),
            'level_label' => $reservation->levelLabel(),
            'player_nickname' => $body['player']->nickname ?? null,
            'admin_phone' => $contact['phone'] ?? null,
        ];

        try {
            Mail::to($to)->send(new OpenMatchNotification($payload));
        } catch (\Throwable $e) {
            Log::warning('Notifica partita aperta non inviata', [
                'reservation_id' => $reservation->id,
                'to' => $to,
                'exception' => $e,
            ]);
        }
    }

    private function categoryLabel(Reservation $reservation): string
    {
        return [
            'match' => 'Partita',
            'lesson' => 'Lezione',
            'tournament' => 'Torneo',
        ][$reservation->open_category] ?? 'Partita';
    }
}
