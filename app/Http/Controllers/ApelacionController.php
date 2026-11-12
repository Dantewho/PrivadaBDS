<?php

namespace App\Http\Controllers;

use App\Models\Adeudo;
use App\Models\Apelacion;
use App\Models\MensajeApelacion;
use App\Models\Multa;
use App\Models\Pago;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApelacionController extends Controller
{
    public function index(Request $request): View
    {
        $appeals = Apelacion::with([
            'usuario.vivienda', 'multa.vivienda', 'multa.motivo',
            'mensajes.usuario.vivienda',
        ])->when($request->filled('estado'), fn ($query) => $query->where('estado', $request->estado))
            ->latest()->paginate(12)->withQueryString();

        return view('administrador.apelaciones', [
            'appeals' => $appeals,
            'stats' => [
                'total' => Apelacion::count(),
                'pendiente' => Apelacion::where('estado', 'pendiente')->count(),
                'revision' => Apelacion::where('estado', 'en_revision')->count(),
                'resuelta' => Apelacion::whereIn('estado', ['aceptada', 'rechazada'])->count(),
            ],
        ]);
    }

    public function store(Request $request, Multa $multa): RedirectResponse
    {
        $user = $request->user();
        abort_unless($multa->vivienda_id === $user->vivienda_id && (!$multa->usuario_id || $multa->usuario_id === $user->id), 403);
        abort_unless($multa->estado === 'pendiente', 422, 'Solo puedes apelar una multa pendiente.');

        $data = $request->validate([
            'motivo' => ['required', 'string', 'min:10', 'max:3000'],
            'adjunto' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx', 'max:8192'],
        ]);
        abort_if(Apelacion::where('multa_id', $multa->id)->exists(), 422, 'Esta multa ya tiene una apelación registrada.');

        DB::transaction(function () use ($request, $user, $multa, $data) {
            $appeal = Apelacion::create([
                'multa_id' => $multa->id,
                'usuario_id' => $user->id,
                'motivo' => $data['motivo'],
                'estado' => 'pendiente',
            ]);
            $this->saveMessage($request, $appeal, $user->id, $data['motivo']);
        });

        return back()->with('status', 'Apelación enviada. Puedes continuar la conversación desde Apelaciones.');
    }

    public function residentIndex(): View
    {
        $appeals = Apelacion::with(['multa.motivo', 'mensajes.usuario.vivienda'])
            ->where('usuario_id', request()->user()->id)->latest()->paginate(10);

        return view('residentes.apelaciones', compact('appeals'));
    }

    public function update(Request $request, Apelacion $apelacion): RedirectResponse
    {
        $fine = $apelacion->multa;
        $data = $request->validate([
            'estado' => ['required', 'in:en_revision,aceptada,rechazada'],
            'mensaje' => ['nullable', 'string', 'max:3000'],
            'adjunto' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx', 'max:8192'],
            'monto_resuelto' => ['nullable', 'required_if:estado,aceptada', 'numeric', 'min:0', 'max:'.$fine->monto_final],
            'resolucion' => ['nullable', 'required_if:estado,aceptada,rechazada', 'string', 'min:5', 'max:2000'],
        ]);
        if ($data['estado'] === 'aceptada' && (float) $data['monto_resuelto'] >= (float) $fine->monto_final) {
            return back()->withErrors(['monto_resuelto' => 'Para aceptar la apelación, define un monto menor al importe actual.'])->withInput();
        }

        DB::transaction(function () use ($request, $apelacion, $data) {
            $appeal = Apelacion::whereKey($apelacion->id)->lockForUpdate()->firstOrFail();
            abort_unless(!in_array($appeal->estado, ['aceptada', 'rechazada'], true), 422, 'Esta apelación ya fue resuelta.');

            if (!empty($data['mensaje']) || $request->hasFile('adjunto')) {
                $this->saveMessage($request, $appeal, $request->user()->id, $data['mensaje'] ?? '');
            }

            $finalizada = in_array($data['estado'], ['aceptada', 'rechazada'], true);
            $appeal->update([
                'estado' => $data['estado'],
                'monto_resuelto' => $data['estado'] === 'aceptada' ? $data['monto_resuelto'] : null,
                'resolucion' => $finalizada ? $data['resolucion'] : null,
                'resuelta_at' => $finalizada ? now() : null,
            ]);

            if ($data['estado'] === 'aceptada') {
                $fine = Multa::whereKey($appeal->multa_id)->lockForUpdate()->firstOrFail();
                $debt = Adeudo::where('multa_id', $fine->id)->lockForUpdate()->first();
                $newAmount = (float) $data['monto_resuelto'];
                $fine->update(['monto_final' => $newAmount, 'estado' => $newAmount === 0.0 ? 'condonada' : 'pendiente']);

                if ($debt) {
                    $debt->update([
                        'monto' => $newAmount,
                        'estado' => $newAmount === 0.0 ? 'condonado' : 'pendiente',
                    ]);
                    Pago::where('adeudo_id', $debt->id)->where('estado', 'pendiente')->update(['estado' => 'rechazado']);
                }
            }
        });

        return back()->with('status', $data['estado'] === 'aceptada'
            ? 'Apelación aceptada; la multa se canceló o se actualizó al importe acordado.'
            : 'Resolución de apelación guardada.');
    }

    public function message(Request $request, Apelacion $apelacion): RedirectResponse
    {
        abort_unless(in_array($apelacion->estado, ['pendiente', 'en_revision'], true), 422, 'La apelación ya fue resuelta.');
        $data = $request->validate([
            'mensaje' => ['nullable', 'required_without:adjunto', 'string', 'max:3000'],
            'adjunto' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx', 'max:8192'],
        ]);
        $this->saveMessage($request, $apelacion, $request->user()->id, $data['mensaje'] ?? '');
        if ($apelacion->estado === 'pendiente') $apelacion->update(['estado' => 'en_revision']);

        return back()->with('status', 'Mensaje enviado.');
    }

    public function residentMessage(Request $request, Apelacion $apelacion): RedirectResponse
    {
        abort_unless($apelacion->usuario_id === $request->user()->id, 403);
        abort_unless(in_array($apelacion->estado, ['pendiente', 'en_revision'], true), 422, 'La apelación ya fue resuelta.');
        $data = $request->validate([
            'mensaje' => ['nullable', 'required_without:adjunto', 'string', 'max:3000'],
            'adjunto' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx', 'max:8192'],
        ]);
        $this->saveMessage($request, $apelacion, $request->user()->id, $data['mensaje'] ?? '');

        return back()->with('status', 'Mensaje enviado.');
    }

    public function download(Request $request, MensajeApelacion $mensajeApelacion): StreamedResponse
    {
        abort_unless($request->user()->rol === 'administrador' || $mensajeApelacion->apelacion()->where('usuario_id', $request->user()->id)->exists(), 403);
        abort_unless($mensajeApelacion->adjunto_path && Storage::disk('local')->exists($mensajeApelacion->adjunto_path), 404);

        return Storage::disk('local')->download($mensajeApelacion->adjunto_path, $mensajeApelacion->adjunto_nombre);
    }

    private function saveMessage(Request $request, Apelacion $appeal, int $userId, string $message): MensajeApelacion
    {
        $file = $request->file('adjunto');
        $path = $file?->store('apelaciones', 'local');

        return MensajeApelacion::create([
            'apelacion_id' => $appeal->id,
            'usuario_id' => $userId,
            'mensaje' => $message,
            'adjunto_path' => $path,
            'adjunto_nombre' => $file ? basename($file->getClientOriginalName()) : null,
            'adjunto_mime' => $file?->getMimeType(),
        ]);
    }
}
