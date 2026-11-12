<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vivienda;
use App\Support\ValidacionesEntrada;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ViviendaController extends Controller
{
    public function index(Request $request): View
    {
        $viviendas = Vivienda::with('propietario')->withCount(['usuarios' => fn ($q) => $q->where('estado', 'activo'), 'tarjetas', 'multas'])
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($q) => $q->where('numero', 'like', '%'.$request->q.'%')->orWhere('direccion', 'like', '%'.$request->q.'%')->orWhereHas('propietario', fn ($p) => $p->where('name', 'like', '%'.$request->q.'%'))))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->orderBy('numero')->paginate(10)->withQueryString();
        return view('administrador.viviendas', compact('viviendas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'numero' => [...ValidacionesEntrada::texto(true,50), 'unique:viviendas,numero'],
            'direccion' => ValidacionesEntrada::texto(true,255),
            'estado' => ['required', Rule::in(['activa', 'inactiva'])],
        ]);
        Vivienda::create($data);
        return back()->with('status', 'Vivienda registrada.');
    }

    public function update(Request $request, Vivienda $vivienda): RedirectResponse
    {
        $data = $request->validate([
            'numero' => [...ValidacionesEntrada::texto(true,50), Rule::unique('viviendas')->ignore($vivienda->id)],
            'direccion' => ValidacionesEntrada::texto(true,255),
            'estado' => ['required', Rule::in(['activa', 'inactiva'])],
            'propietario_id' => ['nullable', 'exists:users,id'],
        ]);
        DB::transaction(function () use ($data, $vivienda) {
            if ($data['propietario_id'] ?? null) {
                $owner = User::findOrFail($data['propietario_id']);
                abort_if($owner->rol !== 'residente' || $owner->estado !== 'activo' || (int) $owner->vivienda_id !== (int) $vivienda->id, 422, 'El propietario debe ser un residente activo asignado a esta vivienda.');
                abort_if(Vivienda::where('propietario_id', $owner->id)->whereKeyNot($vivienda->id)->exists(), 422, 'El usuario ya es propietario de otra vivienda.');
            }
            $vivienda->update($data);
        });
        return back()->with('status', 'Vivienda actualizada.');
    }
}
