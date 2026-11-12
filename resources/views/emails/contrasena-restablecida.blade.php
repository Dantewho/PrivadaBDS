<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Contraseña restablecida</title></head>
<body style="margin:0;background:#f3f6fb;font-family:Arial,sans-serif;color:#17243b;padding:32px 16px">
    <main style="max-width:560px;margin:auto;background:#fff;border:1px solid #e1e8f2;border-radius:18px;overflow:hidden">
        <header style="background:#185eef;color:#fff;padding:26px 32px"><strong style="font-size:20px">PrivadaBDS</strong><div style="margin-top:6px;font-size:13px;opacity:.85">Seguridad de tu cuenta</div></header>
        <section style="padding:32px">
            <h1 style="font-size:22px;margin:0 0 14px">Hola, {{ $user->name }}</h1>
            <p style="line-height:1.6;color:#52627b">Un administrador restableció la contraseña de tu cuenta. Usa esta contraseña provisional para iniciar sesión con tu correo y actualizarla desde tu perfil.</p>
            <div style="margin:24px 0;padding:18px;text-align:center;background:#f3f6fb;border:1px solid #dce6f5;border-radius:12px">
                <small style="display:block;color:#697994;margin-bottom:8px">CONTRASEÑA PROVISIONAL</small>
                <strong style="font-size:24px;letter-spacing:4px">{{ $temporaryPassword }}</strong>
            </div>
            <p style="font-size:13px;line-height:1.6;color:#697994">Si no esperabas este cambio, contacta a la administración de tu privada.</p>
        </section>
    </main>
</body>
</html>
