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
            <td align="center" style="padding:32px 14px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:600px;border:1px solid #4d2541;border-radius:20px;background:#211523;overflow:hidden;">
                    <tr>
                        <td align="center" style="padding:24px 26px;background:#160d18;border-bottom:1px solid #3b2235;">
                            <img src="{{ asset('assets/img/logos/mundo-yuri-logo-blanco.png') }}" width="148" alt="Mundo Yuri" style="display:block;width:148px;max-width:100%;height:auto;border:0;">
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:30px 30px 26px;background:#321b30;">
                            <div style="width:50px;height:50px;line-height:50px;border-radius:50%;background:#f43f8f;color:#fff;font-size:24px;text-align:center;">✦</div>
                            <p style="margin:17px 0 9px;color:#ff8fc7;font-size:11px;font-weight:700;letter-spacing:1.8px;text-transform:uppercase;">Tu historia comienza aquí</p>
                            <h1 style="margin:0;color:#fff;font-family:Georgia,'Times New Roman',serif;font-size:31px;line-height:1.18;">¡Qué gusto verte, {{ $user->name }}!</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 32px 34px;">
                            <p style="margin:0 0 20px;color:#eadde6;font-size:16px;line-height:1.65;text-align:center;">Tu cuenta ya forma parte de <strong style="color:#fff;">Mundo Yuri</strong>, un espacio para descubrir historias GL, guardar favoritas y compartir cada emoción.</p>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 28px;border-radius:16px;background:#170f19;">
                                <tr>
                                    <td style="padding:18px 20px;color:#cbb8c6;font-size:14px;line-height:1.75;">
                                        <strong style="color:#ff91c7;">A partir de hoy puedes:</strong><br>
                                        Descubrir nuevos episodios<br>
                                        Guardar tus series favoritas<br>
                                        Compartir lo que cada historia te hizo sentir
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
