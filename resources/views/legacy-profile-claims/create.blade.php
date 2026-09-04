<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar reclamación · Mundo Yuri</title>
    <x-portal-favicon />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
</head>
<body>
    <x-navbar />
    <main class="portal-profile-page">
        <div class="profile-ambient profile-ambient-one"></div><div class="profile-ambient profile-ambient-two"></div>
        <div class="container-xl px-4 position-relative">
            <nav class="profile-breadcrumb" aria-label="Migas de pan"><a href="{{ route('community.index') }}">Comunidad</a><span>›</span><a href="{{ route('legacy-profiles.index') }}">Archivo histórico</a><span>›</span><span>Solicitar reclamación</span></nav>
            <div class="legacy-claim-page profile-panel">
                <span class="profile-eyebrow">Archivo de Mundo Yuri</span>
                <h1>Solicitar reclamación</h1>
                <p>Esta solicitud no vincula automáticamente ninguna cuenta. El equipo revisará manualmente la evidencia antes de asociar un perfil histórico.</p>
                <form method="POST" action="{{ route('legacy-profile-claims.store') }}" class="legacy-claim-form">
                    @csrf
                    <label>Perfil histórico
                        <select name="legacy_profile_id" required>
                            <option value="">Elige el perfil que deseas reclamar</option>
                            @foreach($legacyProfiles as $legacyProfile)
                                <option value="{{ $legacyProfile->id }}" @selected(old('legacy_profile_id', $selectedProfile?->id) === $legacyProfile->id)>{{ $legacyProfile->nickname }}@if($legacyProfile->legacy_joined_at) · {{ $legacyProfile->legacy_joined_at->format('Y') }}@endif</option>
                            @endforeach
                        </select>
                    </label>
                    @error('legacy_profile_id')<p class="legacy-claim-error">{{ $message }}</p>@enderror
                    <label>¿Por qué crees que este perfil era tuyo?
                        <textarea name="message" rows="6" maxlength="3000" required>{{ old('message') }}</textarea>
                    </label>
                    <p class="legacy-claim-hint">No compartas contraseñas antiguas ni datos sensibles. Describe solo señales que el equipo pueda contrastar con el archivo.</p>
                    @error('message')<p class="legacy-claim-error">{{ $message }}</p>@enderror
                    <label>Información adicional o enlaces de evidencia <small>(opcional)</small>
                        <textarea name="evidence" rows="4" maxlength="3000">{{ old('evidence') }}</textarea>
                    </label>
                    @error('evidence')<p class="legacy-claim-error">{{ $message }}</p>@enderror
                    <div class="legacy-claim-actions"><a href="{{ $selectedProfile ? route('legacy-profiles.show', $selectedProfile) : route('legacy-profiles.index') }}" class="profile-btn profile-btn-soft">Cancelar</a><button type="submit" class="profile-btn">Enviar solicitud</button></div>
                </form>
            </div>
        </div>
    </main>
    <x-footer />
</body>
</html>
