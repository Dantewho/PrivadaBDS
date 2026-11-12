<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
    </head>
    <body style="margin:0;padding:32px 12px;background:#f3f6fb;font-family:Arial,Helvetica,sans-serif;color:#17243a">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;background:#fff;border:1px solid #e4eaf3;border-radius:18px;overflow:hidden"><tr>
                    <td style="padding:25px 34px;background:#155eef;color:#fff">
                        <div style="font-size:20px;font-weight:bold">⌂ &nbsp;PrivadaBDS</div>
                        <div style="margin-top:6px;color:#d9e5ff;font-size:12px">Bahía de Santiago · Portal de residentes</div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:34px">
                        <p style="margin:0 0 8px;color:#5278bd;font-size:11px;font-weight:bold;letter-spacing:1.2px">TIENES UNA INVITACIÓN</p>
                        <h1 style="margin:0 0 14px;font-size:25px;line-height:1.25">Activa tu cuenta</h1>
                        <p style="margin:0 0 22px;color:#65748a;font-size:15px;line-height:1.7">Se te invitó a formar parte del portal de residentes de la vivienda <strong style="color:#17243a">#{{ $invitation->vivienda->numero }}</strong>.</p>
                        <p style="margin:0 0 8px;color:#8793a5;font-size:12px">Correo de acceso</p><p style="margin:0 0 26px;padding:13px 15px;border:1px solid #e4eaf3;border-radius:9px;background:#f8faff;font-size:14px;font-weight:bold">{{ $invitation->email }}</p>
                        <table role="presentation" cellspacing="0" cellpadding="0">
                            <tr>
                                <td style="border-radius:9px;background:#155eef">
                                    <a href="{{ route('invitacion.show', $invitation->token) }}" style="display:inline-block;padding:14px 24px;color:#fff;text-decoration:none;font-size:14px;font-weight:bold">Aceptar invitación&nbsp; →</a>
                                </td>
                            </tr>
                        </table>
                        <p style="margin:25px 0 0;color:#78869a;font-size:12px;line-height:1.6">Este enlace vence el <strong>{{ $invitation->expira_at->format('d/m/Y H:i') }}</strong>. Si no esperabas esta invitación, puedes ignorar este mensaje.</p>
                        <hr style="margin:28px 0;border:0;border-top:1px solid #edf0f5"><p style="margin:0;color:#98a3b3;font-size:11px;line-height:1.6">PrivadaBDS · Sistema de gestión residencial<br>Este correo se envió a {{ $invitation->email }}.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
