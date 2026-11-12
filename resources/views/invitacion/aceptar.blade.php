<!doctype html><html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <meta name="theme-color" content="#155eef">
        <title>Aceptar invitación · PrivadaBDS</title>
        @vite(['resources/css/app.css','resources/js/app.js'])
    </head>
    <body class="invitation-page">
        <main class="invitation-layout">
            <aside class="invitation-brand">
                <a href="{{ route('login') }}" class="invitation-logo">
                    <span>⌂</span>PrivadaBDS
                </a>
                <div>
                    <p class="eyebrow">BAHÍA DE SANTIAGO</p>
                    <h1>Tu comunidad,<br>más cerca.</h1>
                    <p>Activa tu acceso al portal residencial para consultar servicios, pagos y avisos de tu vivienda.</p>
                </div>
                <small>Acceso seguro para residentes</small>
            </aside>
            <section class="invitation-main">
                <div class="invitation-card">
                    <div class="invitation-icon">✓</div>
                    <p class="eyebrow">INVITACIÓN A PRIVADABDS</p>
                    <h2>Completa tu registro</h2>
                    <p class="invitation-subtitle">Te invitamos a unirte a la vivienda <strong>#{{ $invitation->vivienda->numero }}</strong>.</p>
                    <div class="invitation-email">
                        <span>Correo asociado</span>
                        <strong>{{ $invitation->email }}</strong>
                    </div>
                    @if($errors->any())
                        <div class="invitation-error" role="alert">
                            {{ $errors->first() }}
                        </div>
                    @endif
                    <form method="post" action="{{ route('invitacion.accept', $invitation->token) }}" class="invitation-form" data-validate>
                        @csrf
                        <label>
                            Nombre completo
                            <input name="name" type="text" maxlength="150" autocomplete="name" required value="{{ old('name') }}" placeholder="Escribe tu nombre">
                        </label>
                        <label>
                            Contraseña
                            <input name="password" type="password" minlength="8" maxlength="128" autocomplete="new-password" required placeholder="Al menos 8 caracteres">
                        </label>
                        <label>
                            Confirma tu contraseña
                            <input name="password_confirmation" type="password" minlength="8" maxlength="128" autocomplete="new-password" required placeholder="Vuelve a escribirla">
                        </label>
                        <button class="button button-primary" type="submit">Activar mi cuenta <span>→</span></button>
                    </form>
                    <p class="invitation-footnote">Al continuar, se creará tu cuenta y quedará vinculada a la vivienda indicada.</p>
                </div>
            </section>
        </main>
    </body>
</html>
