<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class RestablecerContrasena extends ResetPassword
{
    protected function buildMailMessage($url): MailMessage
    {
        $expiration = config('auth.passwords.'.config('fortify.passwords').'.expire', 60);

        return (new MailMessage)
            ->subject('Restablece tu contraseña de PrivadaBDS')
            ->greeting('Hola')
            ->line('Recibimos una solicitud para cambiar la contraseña de tu cuenta.')
            ->action('Confirmar cambio de contraseña', $url)
            ->line('Este enlace vence en '.$expiration.' minutos y solo puede utilizarse una vez.')
            ->line('Si no solicitaste el cambio, ignora este correo. Tu contraseña actual seguirá funcionando.');
    }
}
