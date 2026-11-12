<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordRecoveryController extends Controller
{
    public function sendResetLink(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255']]);

        try {
            $status = Password::broker(config('fortify.passwords'))
                ->sendResetLink(['email' => Str::lower(trim($data['email']))]);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'No se pudo enviar el correo. Revisa la configuración SMTP e inténtalo de nuevo.']);
        }

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 'Enviamos el enlace de confirmación al correo indicado. Revisa también la carpeta de spam.');
        }

        if ($status === Password::RESET_THROTTLED) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Ya solicitaste un enlace hace poco. Espera un minuto antes de intentarlo otra vez.']);
        }

        // Keep the response generic so the form does not reveal whether an email is registered.
        return back()->withInput($request->only('email'))
            ->with('status', 'Si existe una cuenta con ese correo, recibirás un enlace para restablecer la contraseña.');
    }
}
