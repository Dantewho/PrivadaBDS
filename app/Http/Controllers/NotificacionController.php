<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificacionController extends Controller
{
    public function index(Request $request): View
    {
        $today = now()->toDateString();
        $notifications = $request->user()->notificaciones()->with('anuncio')
            ->where(fn ($query) => $query->whereNull('anuncio_id')->orWhereHas('anuncio', fn ($announcement) => $announcement
                ->where('estado', 'publicado')->whereDate('visible_desde', '<=', $today)
                ->where(fn ($range) => $range->whereNull('visible_hasta')->orWhereDate('visible_hasta', '>=', $today))))
            ->latest()->paginate(15);
        return view('residentes.notificaciones', compact('notifications'));
    }

    public function show(Request $request, Notificacion $notificacion): View
    {
        $this->authorizeOwner($request, $notificacion);
        $this->authorizeVisible($notificacion);
        if (!$notificacion->leida_at) $notificacion->update(['leida_at' => now()]);

        return view('residentes.notificacion', ['notification' => $notificacion->load('anuncio')]);
    }

    public function markRead(Request $request, Notificacion $notificacion): RedirectResponse
    {
        $this->authorizeOwner($request, $notificacion);
        $this->authorizeVisible($notificacion);
        if (!$notificacion->leida_at) $notificacion->update(['leida_at' => now()]);

        return back()->with('status', 'Notificación marcada como leída.');
    }

    private function authorizeOwner(Request $request, Notificacion $notificacion): void
    {
        abort_unless((int) $notificacion->usuario_id === (int) $request->user()->id, 403);
    }

    private function authorizeVisible(Notificacion $notificacion): void
    {
        if (!$notificacion->anuncio_id) return;

        $today = now()->toDateString();
        $visibleFrom = $notificacion->anuncio?->visible_desde?->toDateString();
        abort_unless($notificacion->anuncio && $notificacion->anuncio->estado === 'publicado'
            && $visibleFrom && $visibleFrom <= $today
            && (!$notificacion->anuncio->visible_hasta || $notificacion->anuncio->visible_hasta->toDateString() >= $today), 404);
    }
}
