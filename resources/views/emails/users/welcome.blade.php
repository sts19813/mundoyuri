<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bienvenida a Mundo Yuri</title>
</head>
<body style="margin:0;padding:0;background:#100912;font-family:Arial,Helvetica,sans-serif;color:#f8f1f6;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;">Tu nuevo espacio para descubrir y disfrutar historias Girls' Love.</div>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;background:#100912;">
        <tr>
            <td align="center" style="padding:42px 16px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:640px;border:1px solid #4d2541;border-radius:24px;background:#211523;overflow:hidden;">
                    <tr>
                        <td align="center" style="padding:28px 30px 22px;background:#160d18;border-bottom:1px solid #3b2235;">
                            <img src="{{ asset('assets/img/logos/Logo_default.png') }}" width="150" alt="Mundo Yuri" style="display:block;width:150px;max-width:100%;height:auto;border:0;">
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:42px 42px 20px;background:#321b30;">
                            <div style="width:62px;height:62px;line-height:62px;border-radius:50%;background:#f43f8f;color:#fff;font-size:30px;text-align:center;">✦</div>
                            <p style="margin:22px 0 10px;color:#ff8fc7;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;">Tu historia comienza aquí</p>
                            <h1 style="margin:0;color:#fff;font-family:Georgia,'Times New Roman',serif;font-size:38px;line-height:1.16;">¡Qué gusto tenerte aquí, {{ $user->name }}!</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:30px 42px 42px;">
                            <p style="margin:0 0 22px;color:#eadde6;font-size:17px;line-height:1.65;text-align:center;">Tu cuenta ya forma parte de <strong style="color:#fff;">Mundo Yuri</strong>, una comunidad para descubrir series, guardar tus favoritas y compartir cada historia.</p>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 28px;border-radius:16px;background:#170f19;">
                                <tr>
                                    <td style="padding:22px 24px;color:#cbb8c6;font-size:14px;line-height:1.8;">
                                        <strong style="color:#ff91c7;">Desde ahora puedes:</strong><br>
                                        Explorar el catálogo y ver nuevos episodios<br>
                                        Guardar las series que más te gustan<br>
                                        Participar con comentarios y aportes
                                    </td>
                                </tr>
                            </table>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center">
                                <tr>
                                    <td align="center" style="border-radius:999px;background:#f43f8f;">
                                        <a href="{{ route('home') }}" style="display:inline-block;padding:15px 28px;color:#fff;font-size:15px;font-weight:700;text-decoration:none;">Explorar Mundo Yuri&nbsp; →</a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:30px 0 0;color:#9f8999;font-size:12px;line-height:1.6;text-align:center;">Te avisaremos cuando haya episodios nuevos. Puedes pausar estos correos en cualquier momento desde las preferencias de tu perfil.</p>
                        </td>
                    </tr>
                </table>
                <p style="margin:20px 0 0;color:#776371;font-size:12px;">© {{ now()->year }} Mundo Yuri · Historias que merecen ser encontradas</p>
            </td>
        </tr>
    </table>
</body>
</html>
