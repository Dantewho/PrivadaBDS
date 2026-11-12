<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContrasenaRestablecida extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $temporaryPassword,
    ) {}

    public function build(): static
    {
        return $this->subject('Tu contraseña de PrivadaBDS fue restablecida')
            ->view('emails.contrasena-restablecida');
    }
}
