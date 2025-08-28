<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class otpUser extends Mailable
{
    use Queueable, SerializesModels;

    public $consumer;

    public function __construct($consumer)
    {
        $this->consumer = $consumer;
    }


    public function build()
    {
        return $this->subject('VERIFICA CODICE - Notifica da ' . config('configurazione.name'))
            ->view('emails.otpUser');
    }
}

