<?php

namespace App\Http\Controllers;

use App\Models\MotivoMulta;
use App\Support\ValidacionesEntrada;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MotivoMultaController extends Controller
{
    public function reglamento(): View
    {
        return view('administrador.reglamento', [
            'reasons' => MotivoMulta::withCount('multas')->orderBy('nombre')->paginate(12),
            'pdfUrl' => route('admin.reglamento.pdf'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => [...ValidacionesEntrada::texto(true, 150), 'unique:motivo_multas,nombre'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'monto_base' => ['required', 'numeric', 'min:0.01', 'max:1000000'],
            'incremento' => ['required', 'numeric', 'min:0', 'max:500'],
        ]);
        MotivoMulta::create($data + ['activo' => true]);

        return back()->with('status', 'Motivo de multa agregado.');
    }

    public function update(Request $request, MotivoMulta $motivoMulta): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => [...ValidacionesEntrada::texto(true, 150), Rule::unique('motivo_multas', 'nombre')->ignore($motivoMulta->id)],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'monto_base' => ['required', 'numeric', 'min:0.01', 'max:1000000'],
            'incremento' => ['required', 'numeric', 'min:0', 'max:500'],
        ]);
        $motivoMulta->update($data);

        return back()->with('status', 'Motivo de multa actualizado. Los cargos ya generados conservan su monto histórico.');
    }

    public function toggle(Request $request, MotivoMulta $motivoMulta): RedirectResponse
    {
        $data = $request->validate(['activo' => ['required', 'boolean']]);
        $motivoMulta->update(['activo' => (bool) $data['activo']]);

        return back()->with('status', $motivoMulta->activo ? 'Motivo habilitado para nuevas multas.' : 'Motivo deshabilitado; se conserva en el historial.');
    }

    public function destroy(MotivoMulta $motivoMulta): RedirectResponse
    {
        if ($motivoMulta->multas()->exists()) {
            $motivoMulta->update(['activo' => false]);

            return back()->with('status', 'Este motivo ya tiene multas asociadas; se desactivó para conservar el historial.');
        }

        $motivoMulta->delete();

        return back()->with('status', 'Motivo de multa eliminado.');
    }
}
