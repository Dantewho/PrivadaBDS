<?php

namespace App\Http\Controllers;

use App\Models\Adeudo;
use App\Models\MotivoMulta;
use App\Models\Multa;
use App\Models\Pago;
use App\Models\User;
use App\Models\Vivienda;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MultaController extends Controller
{
    public function index(Request $request): View
    {
        $fines = Multa::with(['vivienda', 'usuario', 'motivo', 'adeudo.pagos'])
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($search) => $search
                ->where('descripcion', 'like', '%'.$request->q.'%')
                ->orWhereHas('vivienda', fn ($home) => $home->where('numero', 'like', '%'.$request->q.'%'))
                ->orWhereHas('motivo', fn ($reason) => $reason->where('nombre', 'like', '%'.$request->q.'%'))))
            ->when($request->filled('vivienda_id'), fn ($query) => $query->where('vivienda_id', $request->vivienda_id))
            ->when($request->filled('motivo_id'), fn ($query) => $query->where('motivo_id', $request->motivo_id))
            ->when($request->filled('estado'), fn ($query) => $query->where('estado', $request->estado))
            ->orderByDesc('fecha')->paginate(15)->withQueryString();

        return view('administrador.multas', [
            'fines' => $fines,
            'homes' => Vivienda::with('usuarios')->orderBy('numero')->get(),
            'reasons' => MotivoMulta::where('activo', true)->orderBy('nombre')->get(),
            'stats' => [
                'total' => Multa::count(),
                'pendiente' => Multa::where('estado', 'pendiente')->sum('monto_final'),
                'revision' => Multa::whereHas('apelaciones', fn ($query) => $query->whereIn('estado', ['pendiente', 'en_revision']))->count(),
                'pagada' => Multa::where('estado', 'pagada')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'vivienda_id' => ['required', 'exists:viviendas,id'],
            'usuario_id' => ['nullable', 'exists:users,id'],
            'motivo_id' => ['required', 'exists:motivo_multas,id'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'evidencia' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx', 'max:8192'],
        ]);

        $result = DB::transaction(function () use ($request, $data) {
            $home = Vivienda::findOrFail($data['vivienda_id']);
            $person = isset($data['usuario_id'])
                ? User::whereKey($data['usuario_id'])->where('rol', 'residente')->where('estado', 'activo')->where('vivienda_id', $home->id)->firstOrFail()
                : null;
            $reason = MotivoMulta::where('activo', true)->findOrFail($data['motivo_id']);
            $base = (float) $reason->monto_base;
            $increment = (float) $reason->incremento;
            $occurrence = Multa::where('vivienda_id', $home->id)->where('motivo_id', $reason->id)
                ->when($person, fn ($query) => $query->where(fn ($scope) => $scope->whereNull('usuario_id')->orWhere('usuario_id', $person->id)))
                ->count() + 1;
            $amount = round($base * (1 + ($increment / 100) * ($occurrence - 1)), 2);
            $evidence = $request->file('evidencia');
            $fine = Multa::create([
                'vivienda_id' => $home->id,
                'usuario_id' => $person?->id,
                'motivo_id' => $reason->id,
                'monto_base' => $base,
                'incremento' => $increment,
                'monto_final' => $amount,
                'numero_reincidencia' => $occurrence,
                'descripcion' => $data['descripcion'] ?? null,
                'evidencia_path' => $evidence?->store('multas', 'local'),
                'evidencia_nombre' => $evidence ? basename($evidence->getClientOriginalName()) : null,
                'estado' => 'pendiente',
                'fecha' => now(),
            ]);
            Adeudo::create([
                'vivienda_id' => $home->id,
                'multa_id' => $fine->id,
                'concepto' => 'Multa: '.$reason->nombre.' #'.$fine->id,
                'periodo' => 'multa-'.$fine->id,
                'monto' => $amount,
                'fecha_vencimiento' => now()->addDays(15)->toDateString(),
                'estado' => 'pendiente',
            ]);

            return ['occurrence' => $occurrence, 'amount' => $amount, 'reason' => $reason->nombre, 'home' => $home->numero, 'person' => $person?->name, 'increment' => $increment];
        });

        $scope = $result['person'] ? 'para '.$result['person'] : 'en la vivienda #'.$result['home'];
        $incrementTotal = ($result['occurrence'] - 1) * $result['increment'];

        return back()->with('status', 'Multa registrada: '.$result['occurrence'].'.ª por '.$result['reason'].' '.$scope.'; incremento aplicado '.$incrementTotal.'% y total $'.number_format($result['amount'], 2).' MXN.');
    }

    public function update(Request $request, Multa $multa): RedirectResponse
    {
        $data = $request->validate([
            'estado' => ['required', 'in:pendiente,pagada,condonada'],
            'metodo' => ['required_if:estado,pagada', 'nullable', 'in:transferencia,efectivo'],
        ]);

        DB::transaction(function () use ($multa, $data) {
            $fine = Multa::whereKey($multa->id)->lockForUpdate()->firstOrFail();
            abort_unless($fine->estado === 'pendiente', 422, 'Solo se pueden actualizar multas pendientes.');

            $debt = Adeudo::where('multa_id', $fine->id)->lockForUpdate()->firstOrFail();

            if ($data['estado'] === 'pagada') {
                $payment = Pago::where('adeudo_id', $debt->id)
                    ->where('estado', 'pagado')
                    ->lockForUpdate()
                    ->first();

                if (!$payment) {
                    $payment = Pago::where('adeudo_id', $debt->id)
                        ->where('estado', 'pendiente')
                        ->latest('fecha_pago')
                        ->lockForUpdate()
                        ->first();

                    if ($payment) {
                        $payment->update([
                            'estado' => 'pagado',
                            'fecha_pago' => now(),
                        ]);
                    } else {
                        Pago::create([
                            'adeudo_id' => $debt->id,
                            'usuario_id' => $fine->usuario_id,
                            'monto' => $debt->monto,
                            'metodo' => $data['metodo'],
                            'referencia' => null,
                            'estado' => 'pagado',
                            'fecha_pago' => now(),
                        ]);
                    }
                }

                $debt->update(['estado' => 'pagado']);
                $fine->update(['estado' => 'pagada']);

                return;
            }

            $debt->update(['estado' => $data['estado'] === 'condonada' ? 'condonado' : 'pendiente']);
            $fine->update(['estado' => $data['estado']]);

            if ($data['estado'] === 'condonada') {
                Pago::where('adeudo_id', $debt->id)
                    ->where('estado', 'pendiente')
                    ->update(['estado' => 'rechazado']);
            }
        }, 3);

        return back()->with('status', $data['estado'] === 'pagada'
            ? 'Pago de multa registrado y adeudo conciliado.'
            : 'Estado de multa actualizado.');
    }

    public function residentIndex(): View
    {
        $user = request()->user();
        $fines = Multa::with(['motivo', 'apelaciones', 'adeudo.pagos'])
            ->where('vivienda_id', $user->vivienda_id)
            ->where(fn ($query) => $query->where('usuario_id', $user->id)->orWhereNull('usuario_id'))
            ->latest('fecha')->paginate(15);

        return view('residentes.multas', compact('fines'));
    }

    public function downloadEvidence(Request $request, Multa $multa): StreamedResponse
    {
        $user = $request->user();
        abort_unless($user->rol === 'administrador' || ($user->vivienda_id === $multa->vivienda_id && (!$multa->usuario_id || $multa->usuario_id === $user->id)), 403);
        abort_unless($multa->evidencia_path && Storage::disk('local')->exists($multa->evidencia_path), 404);

        return Storage::disk('local')->download($multa->evidencia_path, $multa->evidencia_nombre ?: 'evidencia-multa');
    }
}
