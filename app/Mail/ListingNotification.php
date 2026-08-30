<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Notifiche della bacheca annunci (approvazione, rifiuto).
 * Stesso schema delle altre mail del progetto: array associativo alla view.
 */
class ListingNotification extends Mailable
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
            ?? 'Bacheca - Notifica da '.config('configurazione.name');

        return $this->subject($subject)->view('emails.listing');
    }

    public function attachments()
    {
        return [];
    }
}
