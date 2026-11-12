@extends('residentes.layout')
@section('title', 'Mi perfil')
@section('content')
<div class="resident-home-heading profile-page-heading"><div><p class="eyebrow">GESTIÓN DE CUENTA</p><h1>Mi perfil</h1><p>Consulta tus datos y administra la seguridad de tu cuenta.</p></div></div>
@if(session('status'))<div class="info-banner" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="info-banner" role="alert">{{ $errors->first() }}</div>@endif
<div class="resident-profile-grid">
    <div class="resident-profile-main">
        <section class="panel resident-profile-card">
            <div class="resident-profile-identity"><span class="resident-profile-avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span><div><h2>{{ auth()->user()->name }}</h2><p>{{ auth()->user()->email }}</p><div class="resident-profile-tags"><span class="status status-active">{{ ucfirst(auth()->user()->estado) }}</span><span>{{ auth()->user()->rol === 'residente' ? 'Residente' : ucfirst(auth()->user()->rol) }}</span></div></div></div>
            <div class="resident-profile-details"><div><small>MÉTODO DE ACCESO</small><strong>{{ auth()->user()->google_id ? 'Cuenta de Google' : 'Correo y contraseña' }}</strong></div><div><small>VIVIENDA</small><strong>Casa #{{ $home?->numero ?? 'Sin asignar' }}</strong></div><div><small>PRIVADA</small><strong>Bahía de Santiago</strong></div><div><small>FECHA DE REGISTRO</small><strong>{{ auth()->user()->created_at?->format('d/m/Y') ?? '—' }}</strong></div></div>
        </section>
        <section class="panel resident-account-security"><div class="panel-heading"><div><h2>Acceso y seguridad</h2><p>Tu correo es {{ auth()->user()->email }}.</p></div><span class="resident-security-mark">✓</span></div><div class="resident-security-row"><span><strong>Correo verificado</strong><small>{{ auth()->user()->email_verified_at ? 'La dirección puede usarse para iniciar sesión.' : 'La dirección todavía no está verificada.' }}</small></span><span class="status {{ auth()->user()->email_verified_at ? 'status-active' : 'status-pending' }}">{{ auth()->user()->email_verified_at ? 'Verificado' : 'Pendiente' }}</span></div><div class="resident-security-row"><span><strong>Estado de cuenta</strong><small>El acceso está controlado por la administración de la privada.</small></span><span class="status {{ auth()->user()->estado === 'activo' ? 'status-active' : 'status-inactive' }}">{{ ucfirst(auth()->user()->estado) }}</span></div></section>
        <section class="panel resident-account-security">
            <div class="panel-heading"><div><h2>Contraseña</h2><p>Después podrás iniciar sesión escribiendo tu correo y esta contraseña.</p></div><span class="resident-security-mark">⌑</span></div>
            @if(auth()->user()->google_id)<p>Tu cuenta usa Google. Puedes crear una contraseña para habilitar también el acceso con correo.</p>@else<p>Elige una contraseña nueva de al menos 8 caracteres.</p>@endif
            <form method="post" action="{{ route('residentes.perfil.password') }}" class="resident-password-form" data-validate>@csrf @method('PATCH')
                @unless(auth()->user()->google_id)<label>Contraseña actual<input type="password" name="current_password" autocomplete="current-password" required></label>@endunless
                <label>Nueva contraseña<input type="password" name="password" minlength="8" autocomplete="new-password" required></label>
                <label>Confirmar nueva contraseña<input type="password" name="password_confirmation" minlength="8" autocomplete="new-password" required></label>
                <button class="button button-primary" type="submit">Guardar contraseña</button>
            </form>
        </section>
    </div>
    <aside class="resident-profile-side">
        <section class="panel resident-profile-notice"><span>INFORMACIÓN IMPORTANTE</span><p>Los cambios de vivienda y correo deben gestionarse con la administración para proteger el registro de residentes.</p><a href="{{ route('residentes.quejas') }}">Contactar administración →</a></section>
        <section class="panel resident-profile-summary"><h2>Resumen de cuenta</h2><div><span>Adeudo actual</span><strong>${{ number_format((float) $profileStats['adeudos'], 2) }} MXN</strong></div><div><span>Multas pendientes</span><strong>{{ $profileStats['multas'] }}</strong></div><div><span>Tarjetas activas</span><strong>{{ $profileStats['tarjetas'] }}</strong></div><div><span>Residentes en casa</span><strong>{{ $home?->usuarios()->where('rol', 'residente')->where('estado', 'activo')->count() ?? 0 }} / 2</strong></div></section>
        <section class="panel resident-profile-logout-card"><a href="{{ route('residentes.notificaciones') }}">Notificaciones <span>→</span></a><form method="post" action="{{ route('logout') }}">@csrf<button type="submit">↪ &nbsp; Cerrar sesión</button></form></section>
    </aside>
</div>
@endsection
