<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#155eef"><title>Recuperar contraseña · PrivadaBDS</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="login-body"><main class="login-page">
    <section class="login-showcase"><a class="login-brand" href="{{ route('login') }}"><span class="brand-mark">⌂</span><strong>PrivadaBDS</strong></a><div class="showcase-copy"><span class="eyebrow">RECUPERACIÓN DE CUENTA</span><h1>Vuelve a tener acceso</h1><p>Te enviaremos un enlace seguro para crear una contraseña nueva.</p></div><p class="showcase-foot">Bahía de Santiago · Puerto Vallarta, Jalisco</p></section>
    <section class="login-panel"><div class="login-card"><div class="login-heading"><p class="eyebrow">¿OLVIDASTE TU CONTRASEÑA?</p><h2>Recupera tu cuenta</h2><p>Escribe el correo asociado a tu cuenta.</p></div>
        @if(session('status'))<p class="role-note" role="status">{{ session('status') }}</p>@endif
        @if($errors->any())<p class="form-error" role="alert">{{ $errors->first('email') }}</p>@endif
        <form class="login-form" action="{{ route('password.email.custom') }}" method="post">@csrf
            <label for="email">Correo electrónico</label><div class="input-wrap"><input id="email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" placeholder="nombre@correo.com" required autofocus></div>
            <button class="button button-primary login-submit" type="submit">Enviar enlace de recuperación <span>→</span></button>
        </form>
        <p class="login-help"><a href="{{ route('login') }}">← Volver a iniciar sesión</a></p>
    </div><footer class="login-footer">© 2026 PrivadaBDS · Acceso seguro para residentes</footer></section>
</main></body></html>

