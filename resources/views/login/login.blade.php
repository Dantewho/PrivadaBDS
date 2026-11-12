<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#155eef">
    <title>Iniciar sesión · PrivadaBDS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-body">
<main class="login-page">
    <section class="login-showcase">
        <a class="login-brand" href="{{ route('login') }}"><span class="brand-mark" aria-hidden="true">⌂</span><strong>PrivadaBDS</strong></a>
        <div class="showcase-copy">
            <span class="eyebrow">BAHÍA DE SANTIAGO</span>
            <h1>Sistema de gestión residencial</h1>
            <p>Administra pagos, accesos, avisos y servicios de tu comunidad en un solo lugar.</p>
        </div>
        <div class="showcase-stats">
            <div><span aria-hidden="true">⌂</span><strong>50</strong><small>Viviendas</small></div>
            <div><span aria-hidden="true">♙</span><strong>30</strong><small>Usuarios</small></div>
        </div>
        <p class="showcase-foot">Bahía de Santiago · Puerto Vallarta, Jalisco</p>
    </section>
    <section class="login-panel">
        <div class="login-card">
            <div class="login-heading"><p class="eyebrow">BIENVENIDO</p><h2>Inicia sesión</h2><p>Accede a tu cuenta de PrivadaBDS</p></div>
            @if(session('status'))<p class="role-note" role="status">Tu contraseña se actualizó correctamente. Ya puedes iniciar sesión con ella.</p>@endif
            <form class="login-form" action="{{ route('login') }}" method="post">@csrf
                @if($errors->any())@php($loginError = $errors->first())<p class="form-error" role="alert">{{ $loginError === 'auth.failed' ? 'El correo o la contraseña no son correctos.' : __($loginError) }}</p>@endif
                <label for="email">Correo electrónico</label>
                <div class="input-wrap"><input id="email" name="email" type="email" autocomplete="username" value="{{ old('email') }}" placeholder="nombre@correo.com" required></div>
                <div class="password-label"><label for="password">Contraseña</label><a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a></div>
                <div class="input-wrap"><input id="password" name="password" type="password" autocomplete="current-password" placeholder="Ingresa tu contraseña" required><button type="button" class="password-toggle" aria-label="Mostrar contraseña" data-password-toggle>◉</button></div>
                <button class="button button-primary login-submit" type="submit">Iniciar sesión <span aria-hidden="true">→</span></button>
                <div class="login-divider"><span>o continúa con</span></div>
                <a class="button button-google" href="{{ route('auth.google') }}"><span class="google-mark" aria-hidden="true">G</span>Iniciar con Google</a>
            </form>
            <div class="role-note"><p>Tu panel depende de los permisos de tu cuenta.</p></div>
            <p class="login-help">¿Necesitas ayuda? Contacta a administración</p>
        </div>
        <footer class="login-footer">© 2026 PrivadaBDS · Acceso seguro para residentes</footer>
    </section>
</main>
</body>
</html>
