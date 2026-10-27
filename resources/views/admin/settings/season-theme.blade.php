@extends('layouts.admin')

@section('title', 'Tema estacional - Mundo Yuri')

@section('toolbar')
    <div>
        <h1 class="fs-2 fw-bold mb-1">Tema estacional</h1>
        <div class="text-muted fs-7">Activa, cambia o retira el ambiente visual del sitio con un solo ajuste.</div>
    </div>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.settings.season-theme.update') }}" class="mw-700px">
        @csrf
        @method('PUT')
        <div class="card">
            <div class="card-body p-8">
                <label class="form-check form-switch form-check-custom form-check-solid mb-8">
                    <input type="hidden" name="enabled" value="0">
                    <input class="form-check-input" type="checkbox" name="enabled" value="1" @checked(old('enabled', $settings->enabled))>
                    <span class="form-check-label fw-semibold text-gray-800">Mostrar tema estacional en el sitio</span>
                </label>

                <label class="form-label required" for="season-theme">Tema activo</label>
                <select id="season-theme" name="theme" class="form-select @error('theme') is-invalid @enderror">
                    @foreach($themes as $key => $label)
                        <option value="{{ $key }}" @selected(old('theme', $settings->theme) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('theme')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text mt-3">El logo principal y el del pie de página cambian con el tema elegido. Al desactivar el tema vuelve automáticamente el logo original de Mundo Yuri.</div>
                <div class="form-text mt-3">Halloween incluye dos arañas pequeñas y hormigas. La interferencia de pantalla aparece como máximo dos veces por sesión. Los demás temas usan animaciones suaves.</div>
                <div class="form-text mt-3">Al desactivarlo y guardar, las siguientes cargas del sitio no incluyen estilos, scripts ni imágenes estacionales. Las pestañas que ya estaban abiertas deben recargarse para aplicar el cambio.</div>
            </div>
            <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Guardar tema</button></div>
        </div>
    </form>
@endsection
