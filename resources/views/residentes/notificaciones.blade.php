@extends('residentes.layout')
@section('title', 'Notificaciones')
@section('content')
<div class="page-heading"><div><p class="eyebrow">AVISOS DE TU COMUNIDAD</p><h1>Notificaciones</h1><p>Los anuncios y avisos disponibles aparecen aquí durante el periodo definido por la administración.</p></div></div>
<div class="resident-case-list">
    @forelse($notifications as $notification)
        <article class="panel resident-case notification-card {{ $notification->leida_at ? '' : 'notification-unread' }}">
            <header><div><strong>{{ $notification->titulo }}</strong><small>{{ $notification->created_at?->format('d/m/Y H:i') }} · {{ $notification->leida_at ? 'Leída '.$notification->leida_at->format('d/m/Y H:i') : 'Nueva' }}</small></div>
                @if(!$notification->leida_at)<form method="post" action="{{ route('residentes.notificaciones.leida', $notification->id) }}">@csrf @method('PATCH')<button class="button button-outline" type="submit">Marcar leída</button></form>@endif
            </header>
            @if($notification->anuncio)<small class="announcement-tag">Visible hasta {{ $notification->anuncio->visible_hasta?->format('d/m/Y') ?? 'sin fecha de término' }}</small>@endif
            <p>{{ \Illuminate\Support\Str::limit($notification->mensaje, 220) }}</p>
            @if($notification->anuncio)<a class="text-action" href="{{ route('residentes.notificaciones.show', $notification) }}">Leer aviso completo →</a>@endif
        </article>
    @empty
        <div class="panel admin-empty-state">No tienes notificaciones disponibles por el momento.</div>
    @endforelse
</div>
<div class="table-footer">{{ $notifications->links() }}</div>
@endsection
