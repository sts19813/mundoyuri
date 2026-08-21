<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body style="margin:0;padding:0;background:#150d16;font-family:Arial,Helvetica,sans-serif;color:#fff;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#150d16;padding:36px 16px;">
        <tr><td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:620px;background:#241825;border:1px solid #513044;border-radius:20px;overflow:hidden;">
                <tr><td style="padding:36px 42px 28px;background:linear-gradient(135deg,#45243f 0%,#211523 70%);">
                    <p style="margin:0 0 16px;color:#ff8cc6;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;">Mundo Yuri · Nuevo episodio</p>
                    <h1 style="margin:0;color:#fff;font-family:Georgia,'Times New Roman',serif;font-size:34px;line-height:1.18;">Ya está disponible<br><span style="color:#ff80bf;">{{ $episode->title }}</span></h1>
                </td></tr>
                <tr><td style="padding:34px 42px 40px;">
                    <p style="margin:0 0 20px;color:#eee1ea;font-size:17px;line-height:1.6;">Hay un nuevo capítulo de <strong style="color:#fff;">{{ $episode->series->title }}</strong> esperando por ti.</p>
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 30px;background:#191119;border-radius:12px;">
                        <tr><td style="padding:18px 20px;color:#cbbac6;font-size:14px;line-height:1.7;">
                            <strong style="color:#ff92c8;">T{{ $episode->season_number }} · E{{ $episode->episode_number }}</strong><br>
                            {{ $episode->release_date?->format('d/m/Y') ?: 'Disponible ahora' }}
                        </td></tr>
                    </table>
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0"><tr><td style="border-radius:999px;background:#f43f8f;">
                        <a href="{{ route('public.episodes.show', $episode->slug) }}" style="display:inline-block;padding:15px 25px;color:#fff;font-size:15px;font-weight:700;text-decoration:none;">Ver {{ $episode->title }} →</a>
                    </td></tr></table>
                    <p style="margin:30px 0 0;color:#a58b9d;font-size:12px;line-height:1.5;">Recibes este correo porque tienes una cuenta en Mundo Yuri. Pronto podrás elegir qué avisos deseas recibir.</p>
                </td></tr>
            </table>
            <p style="margin:18px 0 0;color:#806d7a;font-size:12px;">© {{ now()->year }} Mundo Yuri</p>
        </td></tr>
    </table>
</body>
</html>
