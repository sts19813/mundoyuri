<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nuevo episodio de {{ $episode->series->title }}</title>
</head>
<body style="margin:0;padding:0;background:#100912;font-family:Arial,Helvetica,sans-serif;color:#f8f1f6;">
    @php
        $seriesCover = $episode->series->coverMediaType() !== 'video'
            ? $episode->series->coverMediaUrl()
            : null;
        $seriesCover ??= $episode->series->bannerMediaType() !== 'video'
            ? $episode->series->bannerMediaUrl()
            : null;
        if ($seriesCover && \Illuminate\Support\Str::startsWith($seriesCover, '//')) {
            $seriesCover = 'https:'.$seriesCover;
        } elseif ($seriesCover && ! \Illuminate\Support\Str::startsWith($seriesCover, ['http://', 'https://'])) {
            $seriesCover = url($seriesCover);
        }
    @endphp
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;">{{ $episode->series->title }} tiene un nuevo episodio listo para ti.</div>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;background:#100912;">
        <tr>
            <td align="center" style="padding:42px 16px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:640px;border:1px solid #4d2541;border-radius:24px;background:#211523;overflow:hidden;">
                    <tr>
                        <td align="center" style="padding:24px 30px;background:#160d18;border-bottom:1px solid #3b2235;">
                            <img src="{{ asset('assets/img/logos/Logo_default.png') }}" width="150" alt="Mundo Yuri" style="display:block;width:150px;max-width:100%;height:auto;border:0;">
                        </td>
                    </tr>
                    @if($seriesCover)
                        <tr>
                            <td style="padding:0;background:#160d18;">
                                <img src="{{ $seriesCover }}" width="640" alt="Portada de {{ $episode->series->title }}" style="display:block;width:100%;max-width:640px;height:auto;max-height:390px;object-fit:cover;border:0;">
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td style="padding:34px 42px 22px;background:#321b30;">
                            <p style="margin:0 0 13px;color:#ff8fc7;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;">Nuevo episodio · Disponible ahora</p>
                            <h1 style="margin:0;color:#fff;font-family:Georgia,'Times New Roman',serif;font-size:37px;line-height:1.16;">{{ $episode->series->title }}</h1>
                            <p style="margin:12px 0 0;color:#ffc0dc;font-size:19px;line-height:1.4;">Temporada {{ $episode->season_number }} · Episodio {{ $episode->episode_number }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:30px 42px 42px;">
                            <p style="margin:0 0 22px;color:#eadde6;font-size:17px;line-height:1.65;">La espera terminó. <strong style="color:#fff;">{{ $episode->title }}</strong> ya está listo para que continúes la historia.</p>
                            @if($episode->description)
                                <p style="margin:0 0 24px;padding:18px 20px;border-left:3px solid #f43f8f;border-radius:4px 12px 12px 4px;background:#170f19;color:#cbb8c6;font-size:14px;line-height:1.65;">{{ \Illuminate\Support\Str::limit($episode->description, 220) }}</p>
                            @endif
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 28px;border-radius:14px;background:#170f19;">
                                <tr>
                                    <td style="padding:17px 20px;color:#a991a2;font-size:13px;line-height:1.7;">
                                        <strong style="color:#ff91c7;font-size:14px;">T{{ $episode->season_number }} · E{{ $episode->episode_number }}</strong><br>
                                        {{ $episode->release_date?->translatedFormat('d \d\e F \d\e Y') ?: 'Disponible desde hoy' }}
                                    </td>
                                </tr>
                            </table>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center">
                                <tr>
                                    <td align="center" style="border-radius:999px;background:#f43f8f;">
                                        <a href="{{ route('public.episodes.show', $episode->slug) }}" style="display:inline-block;padding:16px 29px;color:#fff;font-size:15px;font-weight:700;text-decoration:none;">Ver episodio {{ $episode->episode_number }}&nbsp; →</a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:30px 0 0;color:#9f8999;font-size:12px;line-height:1.6;text-align:center;">Recibes este correo porque activaste los avisos de nuevos episodios. <a href="{{ route('profile.edit') }}#email-notifications" style="color:#ff91c7;text-decoration:underline;">Administrar correos</a></p>
                        </td>
                    </tr>
                </table>
                <p style="margin:20px 0 0;color:#776371;font-size:12px;">© {{ now()->year }} Mundo Yuri · Nos vemos en el próximo episodio</p>
            </td>
        </tr>
    </table>
</body>
</html>
