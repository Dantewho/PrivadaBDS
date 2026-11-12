<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Invitacion;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        abort_unless(config('services.google.client_id') && config('services.google.client_secret'), 503, 'Configura GOOGLE_CLIENT_ID y GOOGLE_CLIENT_SECRET para habilitar Google.');
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $google = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('login')->withErrors(['email' => 'No se pudo validar la cuenta de Google.']);
        }

        $user = User::where('google_id', $google->getId())->orWhere('email', strtolower($google->getEmail()))->first();
        if (!$user) {
            $user = DB::transaction(function () use ($google) {
                $invite = Invitacion::where('email', strtolower($google->getEmail()))->where('estado', 'pendiente')->where('expira_at', '>', now())->lockForUpdate()->first();
                if (!$invite) return null;
                $home = $invite->vivienda()->lockForUpdate()->first();
                if (!$home || User::where('vivienda_id', $home->id)->where('estado', 'activo')->count() >= 2 || ($invite->es_propietario && $home->propietario_id)) return null;
                $created = User::create(['name' => $google->getName() ?: $google->getNickname() ?: 'Residente', 'email' => strtolower($google->getEmail()), 'google_id' => $google->getId(), 'password' => \Illuminate\Support\Str::random(48), 'vivienda_id' => $home->id, 'rol' => 'residente', 'estado' => 'activo', 'email_verified_at' => now()]);
                if ($invite->es_propietario) $home->update(['propietario_id' => $created->id]);
                $invite->update(['estado' => 'aceptada']);
                return $created;
            });
        } elseif ($user->google_id && $user->google_id !== $google->getId()) {
            return redirect()->route('login')->withErrors(['email' => 'Esta cuenta está vinculada a otro perfil de Google.']);
        } elseif (!$user->google_id) {
            $user->update(['google_id' => $google->getId()]);
        }
        if (!$user || !$user->estaActivo()) return redirect()->route('login')->withErrors(['email' => 'No existe una invitación o cuenta activa para ese correo.']);
        Auth::login($user, true);
        request()->session()->regenerate();
        return redirect()->intended($user->esAdministrador() ? route('admin.dashboard') : route('residentes.inicio'));
    }
}
