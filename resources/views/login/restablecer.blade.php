<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#155eef">
    <title>Crear contraseña nueva · PrivadaBDS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-body">
<main class="login-page">
    <section class="login-showcase">
        <a class="login-brand" href="{{ route('login') }}"><span class="brand-mark" aria-hidden="true">⌂</span><strong>PrivadaBDS</strong></a>
        <div class="showcase-copy"><span class="eyebrow">ACCESO SEGURO</span><h1>Actualiza tu contraseña</h1><p>Elige una contraseña nueva para volver a entrar a tu cuenta.</p></div>
        <p class="showcase-foot">Bahía de Santiago · Puerto Vallarta, Jalisco</p>
    </section>
    <section class="login-panel">
        <div class="login-card">
            <div class="login-heading"><p class="eyebrow">CONFIRMACIÓN DE IDENTIDAD</p><h2>Nueva contraseña</h2><p>El enlace es personal y de un solo uso.</p></div>
            @if($errors->any())<p class="form-error" role="alert">{{ $errors->first() }}</p>@endif
            <form class="login-form" action="{{ route('password.update') }}" method="post">@csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">
                <label for="email">Correo electrónico</label><div class="input-wrap"><input id="email" name="email" type="email" autocomplete="email" value="{{ old('email', $request->email) }}" required></div>
                <label for="password">Nueva contraseña</label><div class="input-wrap"><input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required></div>
                <label for="password_confirmation">Confirmar contraseña</label><div class="input-wrap"><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required></div>
                <button class="button button-primary login-submit" type="submit">Guardar contraseña <span aria-hidden="true">→</span></button>
            </form>
            <p class="login-help"><a href="{{ route('login') }}">← Volver a iniciar sesión</a></p>
        </div>
        <footer class="login-footer">© 2026 PrivadaBDS · Acceso seguro para residentes</footer>
    </section>
</main>
</body>
</html>
