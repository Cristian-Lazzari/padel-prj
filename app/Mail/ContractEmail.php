<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $filePath;
    public $consumer;

    public function __construct($filePath, $consumer)
    {
        $this->filePath = $filePath;
        $this->consumer = $consumer;
    }

    public function build()
    {
        return $this->subject('Contratto di Servizio Future Plus')
            ->view('emails.contract')
            ->attach($this->filePath, [
                'as' => 'Contratto_FuturePlus.pdf',
                'mime' => 'application/pdf',
            ]);
    }
}

