<?php

namespace App\Http\Controllers;

use App\Models\Adeudo;
use App\Models\Anuncio;
use App\Models\Apelacion;
use App\Models\Acceso;
use App\Models\Factura;
use App\Models\Multa;
use App\Models\Notificacion;
use App\Models\Pago;
use App\Models\Queja;
use App\Models\Tarjeta;
use App\Models\User;
use App\Models\Vivienda;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function admin(): View
    {
        $activity = collect();
        foreach (Multa::with('vivienda')->latest('fecha')->take(4)->get() as $fine) {
            $activity->push(['title' => 'Multa registrada', 'detail' => 'Casa #'.$fine->vivienda?->numero.' · $'.number_format((float) $fine->monto_final, 2).' MXN', 'date' => $fine->fecha ?? $fine->created_at, 'icon' => '⚠']);
        }
        foreach (Tarjeta::with('vivienda')->latest()->take(3)->get() as $card) {
            $activity->push(['title' => 'Solicitud de tarjeta', 'detail' => ucfirst($card->tipo).' · Casa #'.$card->vivienda?->numero, 'date' => $card->created_at, 'icon' => '▣']);
        }
        foreach (Apelacion::with('multa.vivienda')->latest()->take(3)->get() as $appeal) {
            $activity->push(['title' => 'Apelación '.$appeal->estado, 'detail' => 'Multa #'.$appeal->multa_id.' · Casa #'.$appeal->multa?->vivienda?->numero, 'date' => $appeal->created_at, 'icon' => '◉']);
        }

        return view('administrador.dashboard', [
            'stats' => [
                'viviendas' => Vivienda::count(),
                'usuarios' => User::where('rol', 'residente')->where('estado', 'activo')->count(),
                'tarjetas' => Tarjeta::where('estado', 'activa')->count(),
                'adeudos' => Adeudo::whereIn('estado', ['pendiente', 'vencido'])->sum('monto'),
                'multas' => Multa::where('estado', 'pendiente')->count(),
                'apelaciones' => Apelacion::whereIn('estado', ['pendiente', 'en_revision'])->count(),
                'quejas' => Queja::whereIn('estado', ['abierta', 'en_revision'])->count(),
                'anuncios' => Anuncio::where('estado', 'publicado')->count(),
            ],
            'activity' => $activity->sortByDesc('date')->take(8)->values(),
            'pendingComplaints' => Queja::whereIn('estado', ['abierta', 'en_revision'])->count(),
            'pendingAppeals' => Apelacion::whereIn('estado', ['pendiente', 'en_revision'])->count(),
            'latestAnnouncement' => Anuncio::where('estado', 'publicado')->latest('fecha_publicacion')->first(),
        ]);
    }

    public function resident(): View
    {
        $user = auth()->user();
        $home = $user->vivienda()->with(['usuarios', 'tarjetas', 'adeudos'])->first();
        $fines = Multa::with('motivo')->where('vivienda_id', $user->vivienda_id)
            ->where(fn ($q) => $q->where('usuario_id', $user->id)->orWhereNull('usuario_id'))->latest('fecha')->take(4)->get();
        $pendingFinesCount = Multa::where('vivienda_id', $user->vivienda_id)->where('estado', 'pendiente')
            ->where(fn ($q) => $q->where('usuario_id', $user->id)->orWhereNull('usuario_id'))->count();
        $latestDebt = $home?->adeudos()->whereIn('estado', ['pendiente', 'vencido'])->orderBy('fecha_vencimiento')->first();
        $payments = Pago::with('adeudo')->where(fn ($q) => $q->where('usuario_id', $user->id)->orWhereHas('adeudo', fn ($debt) => $debt->where('vivienda_id', $user->vivienda_id)))
            ->latest('created_at')->take(5)->get();
        $complaints = Queja::with('mensajes')->where(fn ($q) => $q->where('usuario_id', $user->id)->orWhere('vivienda_id', $user->vivienda_id))
            ->latest()->take(4)->get();
        $accesses = $user->vivienda_id
            ? Acceso::with('tarjeta')->where('vivienda_id', $user->vivienda_id)->whereHas('tarjeta', fn ($q) => $q->where('usuario_id', $user->id))->latest('fecha_hora')->take(4)->get()
            : collect();
        $today = now()->toDateString();
        $visibleNotifications = $user->notificaciones()->where(fn ($query) => $query->whereNull('anuncio_id')->orWhereHas('anuncio', fn ($announcement) => $announcement
            ->where('estado', 'publicado')->whereDate('visible_desde', '<=', $today)
            ->where(fn ($range) => $range->whereNull('visible_hasta')->orWhereDate('visible_hasta', '>=', $today))));
        $recentNotifications = (clone $visibleNotifications)->latest()->take(5)->get();
        $activity = collect();
        foreach ($complaints as $complaint) {
            $activity->push(['title' => 'Queja: '.$complaint->asunto, 'detail' => $complaint->estado === 'cerrada' ? 'Caso cerrado por administración' : 'Estado: '.str_replace('_', ' ', $complaint->estado), 'date' => $complaint->updated_at, 'icon' => '☏', 'tone' => 'blue', 'url' => route('residentes.quejas')]);
        }
        foreach ($payments as $payment) {
            $activity->push(['title' => $payment->estado === 'pagado' ? 'Pago recibido' : 'Pago reportado', 'detail' => ($payment->adeudo?->concepto ?? 'Pago de vivienda').' · $'.number_format((float) $payment->monto, 2).' MXN', 'date' => $payment->fecha_pago ?? $payment->created_at, 'icon' => '$', 'tone' => 'green', 'url' => route('residentes.adeudos')]);
        }
        foreach ($fines as $fine) {
            $activity->push(['title' => 'Multa: '.$fine->motivo?->nombre, 'detail' => ucfirst($fine->estado).' · $'.number_format((float) $fine->monto_final, 2).' MXN', 'date' => $fine->fecha ?? $fine->created_at, 'icon' => '⚑', 'tone' => 'red', 'url' => route('residentes.multas')]);
        }
        foreach ($recentNotifications as $notification) {
            $activity->push(['title' => 'Nuevo anuncio', 'detail' => $notification->titulo, 'date' => $notification->created_at, 'icon' => '◖', 'tone' => 'orange', 'url' => route('residentes.notificaciones')]);
        }
        foreach ($accesses as $access) {
            $activity->push(['title' => 'Acceso registrado', 'detail' => ucfirst($access->movimiento).' · '.ucfirst($access->puerta), 'date' => $access->fecha_hora, 'icon' => '↔', 'tone' => 'blue', 'url' => route('residentes.accesos')]);
        }

        return view('residentes.inicio', [
            'home' => $home,
            'latestDebt' => $latestDebt,
            'activity' => $activity->sortByDesc('date')->take(8)->values(),
            'stats' => [
                'tarjetas' => $home?->tarjetas()->where('usuario_id', $user->id)->count() ?? 0,
                'adeudos' => $home?->adeudos()->whereIn('estado', ['pendiente', 'vencido'])->sum('monto') ?? 0,
                'multas' => $pendingFinesCount,
                'notificaciones' => (clone $visibleNotifications)->whereNull('leida_at')->count(),
                'usuarios' => $home?->usuarios()->where('rol', 'residente')->where('estado', 'activo')->count() ?? 0,
                'tarjetasActivas' => $home?->tarjetas()->where('estado', 'activa')->count() ?? 0,
            ],
            'fines' => $fines,
            'announcements' => Anuncio::with(['notificaciones' => fn ($q) => $q->where('usuario_id', $user->id)])
                ->where('estado', 'publicado')->whereDate('visible_desde', '<=', $today)
                ->where(fn ($range) => $range->whereNull('visible_hasta')->orWhereDate('visible_hasta', '>=', $today))
                ->latest('fecha_publicacion')->take(3)->get(),
        ]);
    }

    public function home(): View
    {
        $user = auth()->user();
        $home = $user->vivienda()->with(['usuarios', 'tarjetas', 'adeudos', 'multas.motivo'])->first();

        return view('residentes.mi_vivienda', compact('home'));
    }

    public function profile(): View
    {
        $user = auth()->user();
        $home = $user->vivienda()->with(['adeudos', 'tarjetas', 'multas'])->first();
        return view('residentes.perfil', [
            'home' => $home,
            'profileStats' => [
                'adeudos' => $home?->adeudos()->whereIn('estado', ['pendiente', 'vencido'])->sum('monto') ?? 0,
                'multas' => $home?->multas()->where('estado', 'pendiente')->count() ?? 0,
                'tarjetas' => $home?->tarjetas()->where('estado', 'activa')->count() ?? 0,
            ],
        ]);
    }

    public function invoices(): View
    {
        $invoices = Factura::with('pago.adeudo')->where('vivienda_id', auth()->user()->vivienda_id)->latest('fecha_emision')->paginate(12);
        return view('residentes.facturas', compact('invoices'));
    }

    public function notifications(): View
    {
        $notifications = auth()->user()->notificaciones()->with('anuncio')->latest()->paginate(15);
        return view('residentes.notificaciones', compact('notifications'));
    }

    public function markNotificationRead(int $notificationId)
    {
        $notification = auth()->user()->notificaciones()->findOrFail($notificationId);
        $notification->update(['leida_at' => now()]);
        return back()->with('status', 'Notificación marcada como leída.');
    }

    public function notificationShow(Notificacion $notificacion): View
    {
        abort_unless($notificacion->usuario_id === auth()->id(), 403);
        if (!$notificacion->leida_at) $notificacion->update(['leida_at' => now()]);
        return view('residentes.notificacion', ['notification' => $notificacion->load('anuncio')]);
    }
}
