<?php

namespace App\Http\Controllers;

use App\Mail\ResidentInvitation;
use App\Models\Invitacion;
use App\Models\User;
use App\Models\Vivienda;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InvitacionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        if (config('mail.default') === 'log' || config('mail.default') === 'array') {
            return back()->withErrors(['email' => 'Configura un servidor SMTP en .env para enviar invitaciones reales.']);
        }
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'vivienda_id' => ['required', 'exists:viviendas,id'],
            'es_propietario' => ['nullable', 'boolean'],
        ]);
        abort_if(Invitacion::where('email', $data['email'])->where('estado', 'pendiente')->where('expira_at', '>', now())->exists(), 422, 'Ya existe una invitación pendiente para ese correo.');
        $invite = DB::transaction(function () use ($data) {
            $home = Vivienda::whereKey($data['vivienda_id'])->lockForUpdate()->firstOrFail();
            abort_if($home->estado !== 'activa', 422, 'La vivienda está inactiva.');
            $active = User::where('vivienda_id', $home->id)->where('estado', 'activo')->count();
            $pending = Invitacion::where('vivienda_id', $home->id)->where('estado', 'pendiente')->where('expira_at', '>', now())->count();
            abort_if($active + $pending >= 2, 422, 'La vivienda ya tiene dos residentes o invitaciones pendientes.');
            $owner = (bool) ($data['es_propietario'] ?? false);
            abort_if($owner && $home->propietario_id, 422, 'Esta vivienda ya tiene propietario.');
            return Invitacion::create([
                'vivienda_id' => $home->id, 'email' => $data['email'], 'es_propietario' => $owner,
                'token' => Str::random(64), 'estado' => 'pendiente', 'expira_at' => now()->addDays(7),
            ]);
        });
        try {
            Mail::to($invite->email)->send(new ResidentInvitation($invite->load('vivienda')));
        } catch (\Throwable $e) {
            $invite->delete();
            report($e);
            return back()->withErrors(['email' => 'No se pudo entregar el correo. Revisa la configuración SMTP.']);
        }
        return back()->with('status', 'Invitación enviada por correo.');
    }

    public function resend(Invitacion $invitacion): RedirectResponse
    {
        if (config('mail.default') === 'log' || config('mail.default') === 'array') {
            return back()->withErrors(['email' => 'Configura SMTP para reenviar invitaciones.']);
        }
        $previous = $invitacion->only(['token', 'estado', 'expira_at']);
        $invitacion->update(['token' => Str::random(64), 'estado' => 'pendiente', 'expira_at' => now()->addDays(7)]);
        try {
            Mail::to($invitacion->email)->send(new ResidentInvitation($invitacion->load('vivienda')));
        } catch (\Throwable $e) {
            $invitacion->update($previous);
            report($e);
            return back()->withErrors(['email' => 'No se pudo reenviar el correo. Revisa la conexión SMTP.']);
        }
        return back()->with('status', 'Invitación reenviada.');
    }

    public function show(string $token): View
    {
        $invitation = Invitacion::where('token', $token)->where('estado', 'pendiente')->where('expira_at', '>', now())->with('vivienda')->firstOrFail();
        return view('invitacion.aceptar', compact('invitation'));
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $request->validate(['name' => ['required', 'string', 'max:255'], 'password' => ['required', 'string', 'min:8', 'confirmed']]);
        $user = DB::transaction(function () use ($token, $request) {
            $invite = Invitacion::where('token', $token)->where('estado', 'pendiente')->where('expira_at', '>', now())->lockForUpdate()->firstOrFail();
            abort_if(User::where('email', $invite->email)->exists(), 422, 'Este correo ya tiene una cuenta.');
            $home = Vivienda::whereKey($invite->vivienda_id)->lockForUpdate()->firstOrFail();
            abort_if(User::where('vivienda_id', $home->id)->where('estado', 'activo')->count() >= 2, 422, 'La vivienda llegó a su máximo de usuarios.');
            abort_if($invite->es_propietario && $home->propietario_id, 422, 'La vivienda ya tiene propietario.');
            $user = User::create(['name' => $request->name, 'email' => $invite->email, 'password' => Hash::make($request->password), 'vivienda_id' => $home->id, 'rol' => 'residente', 'estado' => 'activo', 'email_verified_at' => now()]);
            if ($invite->es_propietario) $home->update(['propietario_id' => $user->id]);
            $invite->update(['estado' => 'aceptada']);
            return $user;
        });
        auth()->login($user);
        return redirect()->route('residentes.inicio');
    }
}
