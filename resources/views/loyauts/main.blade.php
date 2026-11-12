<!doctype html><html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#f4f7fb">
        <title>@yield('title', 'Administración') · PrivadaBDS</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="admin-body">
        <div class="admin-shell">
            <aside class="admin-sidebar" id="adminSidebar">
                <a class="brand" href="{{ route('admin.dashboard') }}">
                    <span class="brand-mark">⌂</span>
                    <span>
                        <strong>PrivadaBDS</strong>
                        <small>Bahía de Santiago</small>
                    </span>
                </a>
                <div class="sidebar-section-label">MENÚ PRINCIPAL</div>
                <nav class="admin-nav" aria-label="Navegación administrativa">
                    <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">▦ Dashboard</a>
                    <a class="{{ request()->routeIs('admin.viviendas') ? 'active' : '' }}" href="{{ route('admin.viviendas') }}">⌂ Viviendas</a>
                    <a class="{{ request()->routeIs('admin.usuarios') ? 'active' : '' }}" href="{{ route('admin.usuarios') }}">♧ Usuarios</a>
                    <a class="{{ request()->routeIs('admin.tarjetas') ? 'active' : '' }}" href="{{ route('admin.tarjetas') }}">▣ Tarjetas</a>
                    <a class="{{ request()->routeIs('admin.accesos') ? 'active' : '' }}" href="{{ route('admin.accesos') }}">♙ Accesos</a>
                    <a class="{{ request()->routeIs('admin.adeudos') ? 'active' : '' }}" href="{{ route('admin.adeudos') }}">$ Adeudos</a>
                    <a class="{{ request()->routeIs('admin.multas') ? 'active' : '' }}" href="{{ route('admin.multas') }}">△ Multas</a>
                    <a class="{{ request()->routeIs('admin.apelaciones') ? 'active' : '' }}" href="{{ route('admin.apelaciones') }}">◉ Apelaciones</a>
                    <a class="{{ request()->routeIs('admin.quejas') ? 'active' : '' }}" href="{{ route('admin.quejas') }}">☏ Quejas</a>
                    <a class="{{ request()->routeIs('admin.historial*') ? 'active' : '' }}" href="{{ route('admin.historial') }}">▤ Historial</a>
                    <a class="{{ request()->routeIs('admin.anuncios') ? 'active' : '' }}" href="{{ route('admin.anuncios') }}">◖ Anuncios</a>
                    <a class="{{ request()->routeIs('admin.documentos') ? 'active' : '' }}" href="{{ route('admin.documentos') }}">▧ Documentos</a>
                    <a class="{{ request()->routeIs('admin.reglamento') ? 'active' : '' }}" href="{{ route('admin.reglamento') }}">▤ Reglamento</a>
                    <a class="{{ request()->routeIs('admin.configuracion') ? 'active' : '' }}" href="{{ route('admin.configuracion') }}">⚙ Configuración</a>
                </nav>
                <form class="sidebar-logout" method="post" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">↪ Cerrar sesión</button>
                </form>
            </aside>
            <main class="admin-main">
                <header class="admin-topbar">
                    <button class="sidebar-toggle" type="button" aria-label="Abrir menú" data-sidebar-toggle>☰</button>
                    <div class="topbar-title">
                        <span class="topbar-icon">▣</span>
                        <span>
                            <strong>Panel Administrativo</strong>
                            <small>Bahía de Santiago</small>
                        </span>
                    </div>
                    <details class="topbar-user">
                        <summary>
                            <span>{{ auth()->user()->name }}</span>
                            <span class="avatar avatar-blue">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->name,0,1)) }}</span>
                        </summary>
                        <div class="account-menu">
                            <strong>{{ auth()->user()->name }}</strong>
                            <small>{{ auth()->user()->email }}</small>
                            <small>{{ ucfirst(auth()->user()->rol) }} · {{ auth()->user()->vivienda?->numero ? 'Casa #'.auth()->user()->vivienda->numero : 'Sin vivienda' }}</small>
                            <form method="post" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit">Cerrar sesión</button>
                            </form>
                        </div>
                    </details>
                </header>

                <section class="admin-content" id="pageContent" aria-live="polite">
                    @yield('content')
                </section>

            </main>

        </div>

    </body>

</html>