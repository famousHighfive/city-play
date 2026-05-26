<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GameInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $lien,
        public string $nomEnvironnement,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu es invité à rejoindre une partie City Play !',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.game-invitation',
        );
    }
}