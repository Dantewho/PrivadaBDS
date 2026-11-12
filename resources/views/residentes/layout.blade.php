<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#f4f7fb">
    <title>@yield('title', 'Mi cuenta') · PrivadaBDS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-body resident-body">
@php
    $resident = auth()->user();
    $today = now()->toDateString();
    $unreadCount = $resident->notificaciones()->whereNull('leida_at')->where(fn ($query) => $query
        ->whereNull('anuncio_id')->orWhereHas('anuncio', fn ($announcement) => $announcement->where('estado', 'publicado')
            ->whereDate('visible_desde', '<=', $today)->where(fn ($range) => $range->whereNull('visible_hasta')->orWhereDate('visible_hasta', '>=', $today))))->count();
    $pendingFineCount = $resident->vivienda_id
        ? \App\Models\Multa::where('vivienda_id', $resident->vivienda_id)->where('estado', 'pendiente')->where(fn ($q) => $q->where('usuario_id', $resident->id)->orWhereNull('usuario_id'))->count()
        : 0;
    $residentNav = [
        ['residentes.inicio', 'Inicio', '⌂'],
        ['residentes.vivienda', 'Mi vivienda', '▦'],
        ['residentes.adeudos', 'Pagos y adeudos', '$'],
        ['residentes.facturas', 'Facturas', '▤'],
        ['residentes.tarjetas', 'Tarjetas de acceso', '▣'],
        ['residentes.multas', 'Multas', '⚑'],
        ['residentes.apelaciones', 'Apelaciones', '◉'],
        ['residentes.quejas', 'Quejas', '☏'],
        ['residentes.notificaciones', 'Notificaciones', '♢'],
        ['residentes.reglamento', 'Reglamento', '▧'],
        ['residentes.perfil', 'Perfil', '○'],
    ];
@endphp
<div class="resident-app-shell">
    <aside class="resident-sidebar">
        <a class="resident-brand" href="{{ route('residentes.inicio') }}">
            <span class="resident-brand-mark">⌂</span>
            <span><strong>PrivadaBDS</strong><small>Bahía de Santiago</small></span>
        </a>
        <nav class="resident-nav" aria-label="Menú del residente">
            @foreach($residentNav as [$routeName, $label, $icon])
                <a class="{{ request()->routeIs($routeName) ? 'active' : '' }}" href="{{ route($routeName) }}">
                    <span class="resident-nav-icon">{{ $icon }}</span>{{ $label }}
                    @if($routeName === 'residentes.notificaciones' && $unreadCount > 0)<i class="resident-nav-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</i>@endif
                    @if($routeName === 'residentes.multas' && $pendingFineCount > 0)<i class="resident-nav-badge resident-nav-badge-red">{{ $pendingFineCount }}</i>@endif
                </a>
            @endforeach
        </nav>
        <form class="resident-sidebar-logout" method="post" action="{{ route('logout') }}">
            @csrf
            <button type="submit"><span>↪</span>Cerrar sesión</button>
        </form>
    </aside>

    <div class="resident-main-column">
        <header class="resident-topbar">
            <div class="resident-greeting"><strong>Hola, {{ $resident->name }}</strong><small>Casa #{{ $resident->vivienda?->numero ?? '—' }} · Bahía de Santiago</small></div>
            <div class="resident-topbar-actions">
                <a class="resident-notification-button" href="{{ route('residentes.notificaciones') }}" aria-label="Notificaciones">
                    ♧
                    @if($unreadCount > 0)<span>{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>@endif
                </a>
                <a class="resident-avatar-link" href="{{ route('residentes.perfil') }}" title="Mi perfil">{{ mb_strtoupper(mb_substr($resident->name, 0, 1)) }}</a>
            </div>
        </header>
        <main class="resident-content">
            @if(session('status'))<div class="info-banner">{{ session('status') }}</div>@endif
            @if($errors->any())<div class="info-banner info-banner-error" role="alert">{{ $errors->first() }}</div>@endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
