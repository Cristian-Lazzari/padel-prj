<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Notifiche legate ai tornei (iscrizione ricevuta, conferma dalla lista
 * d'attesa, promemoria di inizio).
 *
 * Stesso schema di OpenMatchNotification: un array associativo alla view.
 */
class TournamentNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $content_mail;

    public function __construct($content_mail)
    {
        $this->content_mail = $content_mail;
    }

    public function build()
    {
        $subject = $this->content_mail['subject']
            ?? 'Torneo - Notifica da '.config('configurazione.name');

        return $this->subject($subject)->view('emails.tournament');
    }

    public function attachments()
    {
        return [];
    }
}
