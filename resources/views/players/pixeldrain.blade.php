<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <title>{{ $source->label ?: 'Reproductor Pixeldrain' }}</title>
    <style>
        html, body {
            margin: 0;
            background: #000;
            height: 100%;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        video {
            width: 100%;
            height: 100%;
            background: #000;
        }
    </style>
</head>
<body>
    <video controls playsinline preload="metadata">
        <source src="{{ $source->direct_video_url }}" type="video/mp4">
        Tu navegador no soporta la reproducción de video.
    </video>
</body>
</html>
