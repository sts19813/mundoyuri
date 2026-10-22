<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Restablece tu contraseña</title>
</head>
<body style="margin:0;padding:0;background:#100912;font-family:Arial,Helvetica,sans-serif;color:#f8f1f6;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;">Usa este enlace seguro para crear una nueva contraseña de Mundo Yuri.</div>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;background:#100912;">
        <tr>
            <td align="center" style="padding:32px 14px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:600px;border:1px solid #4d2541;border-radius:20px;background:#211523;overflow:hidden;">
                    <tr>
                        <td align="center" style="padding:24px 26px;background:#160d18;border-bottom:1px solid #3b2235;">
                            <img src="{{ asset('assets/img/logos/mundo-yuri-logo-blanco.png') }}" width="148" alt="Mundo Yuri" style="display:block;width:148px;max-width:100%;height:auto;border:0;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:34px 34px 38px;">
                            <p style="margin:0 0 12px;color:#ff8fc7;font-size:12px;font-weight:700;letter-spacing:1.8px;text-transform:uppercase;">Seguridad de tu cuenta</p>
                            <h1 style="margin:0;color:#fff;font-family:Georgia,'Times New Roman',serif;font-size:31px;line-height:1.18;">Restablece tu contraseña</h1>
                            <p style="margin:20px 0 24px;color:#eadde6;font-size:16px;line-height:1.65;">Hola, {{ $user->name }}. Recibimos una solicitud para crear una nueva contraseña para tu cuenta de Mundo Yuri.</p>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="margin:0 auto 26px;">
                                <tr>
                                    <td align="center" style="border-radius:999px;background:#f43f8f;">
                                        <a href="{{ $resetUrl }}" style="display:inline-block;padding:15px 28px;color:#fff;font-size:15px;font-weight:700;text-decoration:none;">Crear nueva contraseña&nbsp; →</a>
                                    </td>
                                </tr>
                            </table>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-radius:12px;background:#170f19;">
                                <tr>
                                    <td style="padding:16px 18px;color:#cbb8c6;font-size:14px;line-height:1.55;">
                                        Este enlace vence en 60 minutos. Si no solicitaste este cambio, puedes ignorar este correo con tranquilidad.
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:26px 0 0;color:#9f8999;font-size:12px;line-height:1.6;text-align:center;">Por seguridad, nunca compartas este enlace con nadie.</p>
                        </td>
                    </tr>
                </table>
                <p style="margin:18px 0 0;color:#776371;font-size:12px;">© {{ now()->year }} Mundo Yuri · Historias que merecen ser encontradas</p>
            </td>
        </tr>
    </table>
</body>
</html>
