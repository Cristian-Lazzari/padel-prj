<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Notifiche legate alle partite aperte (nuovo iscritto, partita al completo).
 * Segue lo stesso schema di confermaOrdineAdmin: un array associativo
 * passato alla view.
 */
class OpenMatchNotification extends Mailable
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
            ?? 'Aggiornamento partita - Notifica da '.config('configurazione.name');

        return $this->subject($subject)->view('emails.openMatch');
    }

    public function attachments()
    {
        return [];
    }
}
