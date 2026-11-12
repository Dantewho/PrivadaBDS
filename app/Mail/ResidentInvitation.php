<?php

namespace App\Mail;

use App\Models\Invitacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResidentInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invitacion $invitation) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Invitación a PrivadaBDS');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.invitacion');
    }
}
