<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Invitacion;
use App\Models\Vivienda;
use App\Mail\ContrasenaRestablecida;
use App\Support\ValidacionesEntrada;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function updateOwnPassword(Request $request): RedirectResponse
    {
        $user = $request->user();
        $currentPasswordRules = $user->google_id ? ['nullable'] : ['required', 'current_password'];
        $data = $request->validate([
            'current_password' => $currentPasswordRules,
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->forceFill(['password' => $data['password']])->save();

        return back()->with('status', 'Tu contraseña se actualizó. Ya puedes iniciar sesión con tu correo y la nueva contraseña.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $temporaryPassword = (string) random_int(10000000, 99999999);

        try {
            DB::transaction(function () use ($user, $temporaryPassword) {
                Mail::to($user->email)->send(new ContrasenaRestablecida($user, $temporaryPassword));
                $user->forceFill(['password' => $temporaryPassword])->save();
            });
        } catch (\Throwable $exception) {
            report($exception);
            return back()->withErrors(['password' => 'No se pudo enviar el correo; la contraseña no fue modificada. Revisa la configuración de correo.']);
        }

        return back()->with('status', 'Se envió una contraseña provisional al correo del usuario.');
    }

    public function index(Request $request): View
    {
        $users = User::with('vivienda')->when($request->filled('q'), fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', '%'.$request->q.'%')->orWhere('email', 'like', '%'.$request->q.'%')))
            ->when($request->filled('rol'), fn ($q) => $q->where('rol', $request->rol))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->when($request->filled('vivienda_id'), fn ($q) => $q->where('vivienda_id', $request->vivienda_id))
            ->orderBy('name')->paginate(10)->withQueryString();

        return view('administrador.usuarios', [
            'users' => $users,
            'invitaciones' => Invitacion::with('vivienda')->where('estado', 'pendiente')->orderByDesc('created_at')->get(),
            'viviendas' => Vivienda::withCount(['usuarios' => fn ($q) => $q->where('estado', 'activo')])->orderBy('numero')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user) && ($request->rol !== 'administrador' || $request->estado !== 'activo')) {
            return back()->withErrors(['user' => 'No puedes quitarte tu propio acceso de administrador.']);
        }

        $data = $request->validate([
            'name' => ValidacionesEntrada::texto(true,255),
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'rol' => ['required', Rule::in(['administrador', 'residente'])],
            'estado' => ['required', Rule::in(['activo', 'inactivo'])],
            'vivienda_id' => ['nullable', 'exists:viviendas,id'],
            'es_propietario' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($data, $user) {
            $newHome = $data['vivienda_id'] ?? null;
            if ($data['rol'] === 'residente' && $data['estado'] === 'activo' && $newHome) {
                $home = Vivienda::whereKey($newHome)->lockForUpdate()->firstOrFail();
                $count = User::where('vivienda_id', $home->id)->where('estado', 'activo')->where('id', '!=', $user->id)->count();
                $pending = \App\Models\Invitacion::where('vivienda_id', $home->id)->where('estado', 'pendiente')->where('expira_at', '>', now())->count();
                abort_if($count + $pending >= 2, 422, 'La vivienda ya tiene el máximo de dos usuarios o invitaciones pendientes.');
            }
            if ($user->viviendaComoPropietario()->exists()) {
                Vivienda::where('propietario_id', $user->id)->update(['propietario_id' => null]);
            }
            $user->fill(collect($data)->only(['name', 'email', 'rol', 'estado', 'vivienda_id'])->all())->save();
            if (!empty($data['es_propietario']) && $newHome && $data['rol'] === 'residente') {
                $home = Vivienda::whereKey($newHome)->firstOrFail();
                abort_if($home->propietario_id && (int) $home->propietario_id !== (int) $user->id, 422, 'La vivienda ya tiene propietario.');
                Vivienda::whereKey($newHome)->update(['propietario_id' => $user->id]);
            }
        });

        return back()->with('status', 'Usuario actualizado.');
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user), 422, 'No puedes desactivar tu propia cuenta.');
        $user->update(['estado' => $user->estado === 'activo' ? 'inactivo' : 'activo']);
        return back()->with('status', 'Estado actualizado.');
    }
}
