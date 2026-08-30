<?php

namespace App\Services;

use App\Mail\TournamentNotification;
use App\Models\Setting;
use App\Models\Tournament;
use App\Models\TournamentRegistration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Notifiche dei tornei.
 *
 * Come OpenMatchNotifier, ogni invio è protetto da try/catch: un problema
 * SMTP non deve far fallire l'iscrizione, già salvata a database.
 */
class TournamentNotifier
{
    /** Iscrizione ricevuta (confermata o in lista d'attesa). */
    public function registrationReceived(TournamentRegistration $registration): void
    {
        $tournament = $registration->tournament;
        $waitlist = $registration->status === 'waitlist';

        $this->sendToTeam($registration, [
            'subject' => $waitlist
                ? 'Sei in lista d\'attesa per '.$tournament->name
                : 'Iscrizione ricevuta per '.$tournament->name,
            'title' => $waitlist ? 'Sei in lista d\'attesa' : 'Iscrizione ricevuta',
            'subtitle' => $waitlist
                ? 'Il torneo è al completo: ti avvisiamo appena si libera un posto.'
                : 'Abbiamo registrato la tua iscrizione. Ti aspettiamo in campo!',
        ]);
    }

    /** L'iscrizione è passata da lista d'attesa (o pending) a confermata. */
    public function registrationConfirmed(TournamentRegistration $registration): void
    {
        $this->sendToTeam($registration, [
            'subject' => 'Iscrizione confermata: '.$registration->tournament->name,
            'title' => 'Iscrizione confermata',
            'subtitle' => 'Si è liberato un posto e la tua iscrizione è ora confermata.',
        ]);
    }

    /** Promemoria inviato alle squadre confermate prima dell'inizio. */
    public function reminder(Tournament $tournament): int
    {
        $registrations = $tournament->confirmedRegistrations()
            ->with(['player', 'partner'])
            ->get();

        $sent = 0;

        foreach ($registrations as $registration) {
            $registration->setRelation('tournament', $tournament);

            $this->sendToTeam($registration, [
                'subject' => 'Il torneo '.$tournament->name.' sta per iniziare',
                'title' => 'Il torneo sta per iniziare',
                'subtitle' => 'Controlla il calendario dei tuoi incontri nella sezione Tornei.',
            ]);

            $sent++;
        }

        return $sent;
    }

    /** Invia allo iscritto e all'eventuale compagno di coppia. */
    private function sendToTeam(TournamentRegistration $registration, array $body): void
    {
        $recipients = collect([
            $registration->player?->mail,
            $registration->partner?->mail,
        ])->filter()->unique();

        if ($recipients->isEmpty()) {
            return;
        }

        $payload = $this->payload($registration, $body);

        foreach ($recipients as $mail) {
            $this->send($mail, $payload, $registration->id);
        }
    }

    private function payload(TournamentRegistration $registration, array $body): array
    {
        $tournament = $registration->tournament;

        $contactSetting = Setting::where('name', 'Contatti')->first();
        $contact = $contactSetting ? json_decode($contactSetting->property, true) : [];

        return [
            'subject' => $body['subject'],
            'title' => $body['title'],
            'subtitle' => $body['subtitle'],
            'tournament_name' => $tournament->name,
            'starts_label' => $tournament->starts_at
                ? ucfirst($tournament->starts_at->locale('it')->translatedFormat('l j F \a\l\l\e H:i'))
                : 'da definire',
            'location' => $tournament->location,
            'format' => $tournament->formatLabel(),
            'level_label' => $tournament->levelLabel(),
            'team_name' => $registration->displayName(),
            'status_label' => $registration->statusLabel(),
            'price' => $tournament->price ? number_format((float) $tournament->price, 2, ',', '.').' €' : null,
            'admin_phone' => $contact['phone'] ?? null,
        ];
    }

    /**
     * L'invio avviene dopo che la risposta è stata restituita al client:
     * con un SMTP lento o irraggiungibile l'utente non resta in attesa.
     * afterResponse() funziona anche con QUEUE_CONNECTION=sync, quindi non
     * serve un worker sull'hosting condiviso.
     */
    private function send(string $to, array $payload, int $registrationId): void
    {
        dispatch(function () use ($to, $payload, $registrationId) {
            try {
                Mail::to($to)->send(new TournamentNotification($payload));
            } catch (\Throwable $e) {
                Log::warning('Notifica torneo non inviata', [
                    'registration_id' => $registrationId,
                    'to' => $to,
                    'exception' => $e,
                ]);
            }
        })->afterResponse();
    }
}
