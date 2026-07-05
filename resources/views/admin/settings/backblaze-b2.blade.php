@extends('layouts.admin')

@section('title', 'Backblaze B2 - Administración')

@section('toolbar')
    <div class="d-flex align-items-center gap-2 gap-lg-3">
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
            Backblaze B2
        </h1>
    </div>
@endsection

@section('content')
    <div class="row g-5 g-xl-8">
        <div class="col-xl-8">
            <form method="POST" action="{{ route('admin.settings.backblaze-b2.update') }}">
                @csrf
                @method('PUT')

                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <h3 class="fw-bold m-0">Credenciales y bucket</h3>
                        </div>
                    </div>
                    <div class="card-body row g-5">
                        <div class="col-12">
                            <label class="form-check form-switch form-check-custom form-check-solid">
                                <input type="hidden" name="enabled" value="0">
                                <input class="form-check-input" type="checkbox" name="enabled" value="1" @checked(old('enabled', $settings->enabled))>
                                <span class="form-check-label fw-semibold text-gray-700">Habilitar reproducción desde Backblaze B2</span>
                            </label>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Key ID</label>
                            <input class="form-control @error('key_id') is-invalid @enderror" name="key_id" value="{{ old('key_id', $settings->key_id) }}" autocomplete="off">
                            @error('key_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label {{ $settings->exists && filled($settings->application_key) ? '' : 'required' }}">Application Key</label>
                            <input class="form-control @error('application_key') is-invalid @enderror" type="password" name="application_key" value="" autocomplete="new-password" placeholder="{{ filled($settings->application_key) ? 'Guardada; deja vacío para conservarla' : 'Pega una Application Key nueva' }}">
                            @error('application_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Se cifra con <code>APP_KEY</code> antes de guardarse y nunca vuelve a mostrarse.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Nombre exacto del bucket</label>
                            <input class="form-control @error('bucket_name') is-invalid @enderror" name="bucket_name" value="{{ old('bucket_name', $settings->bucket_name) }}" placeholder="mundoyuri">
                            @error('bucket_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Usa el nombre que aparece en “Bucket Name”; no uses el nombre de la Application Key.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Bucket ID</label>
                            <input class="form-control @error('bucket_id') is-invalid @enderror" name="bucket_id" value="{{ old('bucket_id', $settings->bucket_id) }}" placeholder="Se obtiene al probar la conexión">
                            @error('bucket_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Opcional. La prueba de conexión lo completa automáticamente.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Duración de cada enlace privado</label>
                            <select class="form-select @error('token_ttl_seconds') is-invalid @enderror" name="token_ttl_seconds">
                                @foreach([900 => '15 minutos', 3600 => '1 hora', 7200 => '2 horas', 21600 => '6 horas', 86400 => '24 horas'] as $seconds => $label)
                                    <option value="{{ $seconds }}" @selected((int) old('token_ttl_seconds', $settings->token_ttl_seconds ?: 3600) === $seconds)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('token_ttl_seconds')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Última verificación</label>
                            <div class="form-control form-control-solid">
                                {{ $settings->last_verified_at?->format('d/m/Y H:i') ?: 'Todavía no verificada' }}
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end gap-3">
                        <button class="btn btn-primary" type="submit">Guardar configuración</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-xl-4">
            <div class="card mb-5">
                <div class="card-header">
                    <div class="card-title"><h3 class="fw-bold m-0">Comprobar conexión</h3></div>
                </div>
                <div class="card-body">
                    <p class="text-gray-700">Primero guarda los cambios. Después comprueba que la clave puede acceder al bucket y generar enlaces temporales. La prueba funciona aunque la reproducción todavía esté desactivada.</p>

                    @if($settings->download_url)
                        <div class="notice d-flex bg-light-success rounded border-success border border-dashed p-4 mb-5">
                            <div class="d-flex flex-stack flex-grow-1">
                                <div class="fw-semibold">
                                    <div class="fs-7 text-gray-700">Endpoint detectado</div>
                                    <div class="fs-8 text-muted text-break">{{ $settings->download_url }}</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.settings.backblaze-b2.verify') }}">
                        @csrf
                        <button class="btn btn-light-primary w-100" type="submit">Probar conexión</button>
                    </form>
                </div>
            </div>

            <div class="alert alert-warning">
                <h4 class="alert-heading">Clave recomendada</h4>
                <p class="mb-0">Crea una Application Key exclusiva para este bucket con <code>listBuckets</code>, <code>readFiles</code> y <code>shareFiles</code>. No uses la Master Application Key en producción.</p>
            </div>
        </div>
    </div>
@endsection
