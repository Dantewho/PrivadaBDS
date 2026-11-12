@extends('residentes.layout')
@section('title', 'Inicio')
@section('content')
<div class="resident-summary-grid">
    <article class="resident-summary-card"><span>Adeudo actual</span><strong class="summary-orange">${{ number_format((float) $stats['adeudos'], 2) }} MXN</strong><small>{{ $latestDebt ? 'Próximo vencimiento: '.$latestDebt->fecha_vencimiento?->format('d/m/Y') : 'No tienes adeudos pendientes' }}</small></article>
    <article class="resident-summary-card"><span>Próximo pago</span><strong>{{ $latestDebt?->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</strong><small>{{ $latestDebt?->concepto ?? 'Sin pagos programados' }}</small></article>
    <article class="resident-summary-card"><span>Multas pendientes</span><strong class="summary-red">{{ $stats['multas'] }}</strong><small>{{ $stats['multas'] === 1 ? 'Requiere seguimiento' : 'Consulta tus multas' }}</small></article>
    <article class="resident-summary-card"><span>Tarjetas activas</span><strong class="summary-green">{{ $stats['tarjetasActivas'] }}</strong><small>{{ $stats['tarjetas'] }} {{ $stats['tarjetas'] === 1 ? 'tarjeta registrada a tu nombre' : 'tarjetas registradas a tu nombre' }}</small></article>
</div>

<div class="resident-dashboard-grid">
    <div class="resident-dashboard-primary">
        @if($latestDebt)
            <section class="panel resident-payment-highlight">
                <div class="resident-panel-kicker">PRÓXIMO PAGO <span class="status status-pending">Pendiente</span></div>
                <h2>{{ $latestDebt->concepto }}</h2>
                <div class="resident-payment-meta"><div><small>Periodo</small><strong>{{ $latestDebt->periodo }}</strong></div><div><small>Vivienda</small><strong>Casa #{{ $home?->numero ?? '—' }}</strong></div><div><small>Fecha límite</small><strong>{{ $latestDebt->fecha_vencimiento?->format('d/m/Y') }}</strong></div></div>
                <div class="resident-payment-total"><div><small>Importe</small><strong>${{ number_format((float) $latestDebt->monto, 2) }} <small>MXN</small></strong></div><a class="button button-primary" href="{{ route('residentes.adeudos') }}">Ver y reportar pago</a></div>
            </section>
        @else
            <section class="panel resident-payment-highlight resident-no-debt"><div class="resident-panel-kicker">PAGOS</div><h2>Estás al corriente</h2><p>No hay pagos pendientes para tu vivienda.</p><a class="button button-outline" href="{{ route('residentes.adeudos') }}">Ver historial de pagos</a></section>
        @endif

        <section class="panel resident-activity-panel">
            <div class="panel-heading"><div><h2>Actividad reciente</h2><p>Movimientos y avisos relacionados con tu cuenta</p></div><a href="{{ route('residentes.notificaciones') }}">Ver notificaciones →</a></div>
            <div class="resident-activity-list">
                @forelse($activity as $item)
                    <a class="resident-activity-item" href="{{ $item['url'] }}"><span class="resident-activity-icon tone-{{ $item['tone'] }}">{{ $item['icon'] }}</span><span class="resident-activity-copy"><strong>{{ $item['title'] }}</strong><small>{{ $item['detail'] }}</small></span><time>{{ $item['date']?->format('d/m/Y') }}</time></a>
                @empty
                    <p class="resident-empty">Cuando tengas avisos o movimientos, aparecerán aquí.</p>
                @endforelse
            </div>
        </section>
    </div>

    <aside class="resident-dashboard-side">
        <section class="panel resident-home-status"><div class="resident-panel-kicker">ESTADO DE MI VIVIENDA</div><div class="resident-house-title"><span>⌂</span><div><strong>Casa #{{ $home?->numero ?? '—' }}</strong><small>PrivadaBDS · Bahía de Santiago</small></div></div><div class="resident-house-data"><div><span>Estado</span><strong class="status status-active">{{ ucfirst($home?->estado ?? 'Sin asignar') }}</strong></div><div><span>Usuarios</span><strong>{{ $stats['usuarios'] }} / 2</strong></div><div><span>Tarjetas activas</span><strong>{{ $stats['tarjetasActivas'] }}</strong></div><div><span>Multas pendientes</span><strong>{{ $stats['multas'] }}</strong></div></div><a class="resident-side-link" href="{{ route('residentes.vivienda') }}">Ver mi vivienda →</a></section>

        @php($activeFine = $fines->firstWhere('estado', 'pendiente'))
        @if($activeFine)
            <section class="panel resident-active-fine"><div class="resident-fine-label"><i></i>MULTA ACTIVA</div><strong>{{ $activeFine->motivo?->nombre ?? 'Multa' }}</strong><p>{{ $activeFine->descripcion ?: $activeFine->motivo?->descripcion }}</p><small>{{ $activeFine->fecha?->format('d/m/Y') }} · Reincidencia {{ $activeFine->numero_reincidencia }}</small><div><b>${{ number_format((float) $activeFine->monto_final, 2) }} <small>MXN</small></b><a class="button button-danger" href="{{ route('residentes.multas') }}">Ver / apelar</a></div></section>
        @endif

        <section class="panel resident-quick-links"><div class="resident-panel-kicker">ACCESOS RÁPIDOS</div><div><a href="{{ route('residentes.facturas') }}">Mis facturas</a><a href="{{ route('residentes.tarjetas') }}">Tarjetas</a><a href="{{ route('residentes.reglamento') }}">Reglamento</a><a href="{{ route('residentes.apelaciones') }}">Apelaciones</a><a href="{{ route('residentes.accesos') }}">Historial de accesos</a><a href="{{ route('residentes.quejas') }}">Quejas</a></div></section>
    </aside>
</div>

<section class="panel resident-announcement-feed"><div class="panel-heading"><div><h2>Anuncios de la administración</h2><p>Comunicados publicados para los residentes</p></div><a href="{{ route('residentes.notificaciones') }}">Abrir bandeja →</a></div>
    @forelse($announcements as $announcement)@php($announcementNotification = $announcement->notificaciones->first())<article><span class="announcement-tag">ANUNCIO · {{ $announcement->fecha_publicacion?->format('d/m/Y') }}</span><strong>{{ $announcement->titulo }}</strong><p>{{ $announcement->contenido }}</p>@if($announcementNotification)<a class="text-action" href="{{ route('residentes.notificaciones.show', $announcementNotification) }}">Abrir aviso →</a>@endif</article>@empty<p class="resident-empty">No hay anuncios publicados por el momento.</p>@endforelse
</section>
@endsection
