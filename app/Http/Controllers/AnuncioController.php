<?php

namespace App\Http\Controllers;

use App\Models\Anuncio;
use App\Models\AnuncioEnvio;
use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AnuncioController extends Controller
{
    public function index(Request $request): View
    {
        $announcements = Anuncio::with([
            'administrador',
            'envios',
            'notificaciones' => fn ($query) => $query->whereNotNull('leida_at')->with('usuario.vivienda'),
        ])
            ->withCount('notificaciones')
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($search) => $search
                ->where('titulo', 'like', '%'.$request->q.'%')
                ->orWhere('contenido', 'like', '%'.$request->q.'%')))
            ->when($request->filled('estado'), fn ($query) => $query->where('estado', $request->estado))
            ->latest('created_at')->paginate(12)->withQueryString();

        return view('administrador.anuncios', [
            'announcements' => $announcements,
            'stats' => [
                'publicados' => Anuncio::where('estado', 'publicado')->count(),
                'notificaciones' => Notificacion::count(),
                'borradores' => Anuncio::where('estado', 'borrador')->count(),
                'whatsappPendiente' => AnuncioEnvio::where('canal', 'whatsapp')->whereIn('estado', ['pendiente', 'sin_configurar', 'error'])->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $publish = $data['accion'] === 'publicar';

        $announcement = DB::transaction(function () use ($request, $data, $publish) {
            $announcement = Anuncio::create([
                'administrador_id' => $request->user()->id,
                'titulo' => $data['titulo'],
                'contenido' => $data['contenido'],
                'estado' => $publish ? 'publicado' : 'borrador',
                'fecha_publicacion' => $publish ? now() : null,
                'visible_desde' => $data['visible_desde'] ?? null,
                'visible_hasta' => $data['visible_hasta'] ?? null,
            ]);

            if ($publish) {
                $this->notifyResidents($announcement);
            }

            return $announcement;
        });
        if ($publish && !empty($data['enviar_whatsapp'])) $this->recordWhatsappAttempt($announcement);

        return back()->with('status', $publish
            ? 'Anuncio publicado y enviado a las notificaciones de los residentes activos.'
            : 'Borrador de anuncio guardado.');
    }

    public function update(Request $request, Anuncio $anuncio): RedirectResponse
    {
        $data = $this->validated($request);
        $wasPublished = $anuncio->estado === 'publicado';
        $publish = $data['accion'] === 'publicar';

        DB::transaction(function () use ($request, $anuncio, $data, $publish) {
            $anuncio->update([
                'titulo' => $data['titulo'],
                'contenido' => $data['contenido'],
                'estado' => $publish ? 'publicado' : 'borrador',
                'fecha_publicacion' => $publish ? ($anuncio->fecha_publicacion ?? now()) : null,
                'visible_desde' => $data['visible_desde'] ?? null,
                'visible_hasta' => $data['visible_hasta'] ?? null,
            ]);

            if ($publish) {
                $this->notifyResidents($anuncio);
            }
        });
        if ($publish && !$wasPublished && !empty($data['enviar_whatsapp'])) $this->recordWhatsappAttempt($anuncio);

        return back()->with('status', $publish
            ? 'Anuncio actualizado y notificaciones sincronizadas.'
            : 'Anuncio guardado como borrador.');
    }

    public function destroy(Anuncio $anuncio): RedirectResponse
    {
        $anuncio->delete();
        return back()->with('status', 'Anuncio eliminado.');
    }

    public function retryWhatsapp(Anuncio $anuncio): RedirectResponse
    {
        abort_unless($anuncio->estado === 'publicado', 422, 'Solo se pueden enviar anuncios publicados.');
        $this->recordWhatsappAttempt($anuncio);
        $delivery = $anuncio->envios()->where('canal', 'whatsapp')->first();

        return back()->with('status', match ($delivery?->estado) {
            'enviado' => 'Anuncio enviado al grupo de WhatsApp.',
            'sin_configurar' => 'Configura el webhook y el grupo de WhatsApp para habilitar este envío.',
            default => 'El envío a WhatsApp falló. Revisa el detalle del registro e inténtalo de nuevo.',
        });
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'titulo' => ['required', 'string', 'max:150'],
            'contenido' => ['required', 'string', 'min:5', 'max:10000'],
            'accion' => ['required', Rule::in(['borrador', 'publicar'])],
            'visible_desde' => ['required_if:accion,publicar', 'nullable', 'date'],
            'visible_hasta' => ['required_if:accion,publicar', 'nullable', 'date', 'after_or_equal:visible_desde'],
            'enviar_whatsapp' => ['nullable', 'boolean'],
        ]);
    }

    private function notifyResidents(Anuncio $announcement): void
    {
        User::where('rol', 'residente')->where('estado', 'activo')->orderBy('id')->chunkById(200, function ($users) use ($announcement) {
            foreach ($users as $user) {
                Notificacion::updateOrCreate(
                    ['anuncio_id' => $announcement->id, 'usuario_id' => $user->id],
                    ['titulo' => $announcement->titulo, 'mensaje' => $announcement->contenido],
                );
            }
        });
    }

    private function recordWhatsappAttempt(Anuncio $announcement): void
    {
        $webhook = config('services.whatsapp.webhook_url');
        $destination = config('services.whatsapp.group_id');
        if (!$webhook) {
            $announcement->envios()->updateOrCreate(['canal' => 'whatsapp'], [
                'estado' => 'sin_configurar', 'grupo_destino' => $destination,
                'error' => 'Falta configurar la integración de WhatsApp.',
            ]);
            return;
        }

        try {
            $response = Http::timeout(12)->post($webhook, [
                'group_id' => $destination,
                'message' => $announcement->titulo."\n\n".$announcement->contenido,
            ]);
            $announcement->envios()->updateOrCreate(['canal' => 'whatsapp'], [
                'estado' => $response->successful() ? 'enviado' : 'error',
                'grupo_destino' => $destination,
                'external_id' => data_get($response->json(), 'id'),
                'error' => $response->successful() ? null : mb_substr($response->body(), 0, 2000),
                'enviado_at' => $response->successful() ? now() : null,
            ]);
        } catch (\Throwable $exception) {
            $announcement->envios()->updateOrCreate(['canal' => 'whatsapp'], [
                'estado' => 'error', 'grupo_destino' => $destination,
                'error' => mb_substr($exception->getMessage(), 0, 2000),
            ]);
        }
    }
}
