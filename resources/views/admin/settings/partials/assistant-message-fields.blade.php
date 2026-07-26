@php
    $text = $message['text'] ?? '';
    $peek = $message['peek'] ?? '';
    $audience = $message['audience'] ?? 'all';
    $actionType = $message['action_type'] ?? 'none';
    $label = $message['label'] ?? '';
    $url = $message['url'] ?? '';
@endphp

<div class="card border border-gray-300 mb-5" data-message-item>
    <div class="card-header min-h-60px">
        <div class="card-title">
            <span class="badge badge-light-primary me-3" data-message-number></span>
            <h4 class="fw-bold m-0">Pregunta o mensaje</h4>
        </div>
        <div class="card-toolbar gap-2">
            <button class="btn btn-sm btn-icon btn-light" type="button" data-move-up title="Subir">
                <i class="ki-outline ki-up fs-3"></i>
            </button>
            <button class="btn btn-sm btn-icon btn-light" type="button" data-move-down title="Bajar">
                <i class="ki-outline ki-down fs-3"></i>
            </button>
            <button class="btn btn-sm btn-icon btn-light-danger" type="button" data-remove-message title="Eliminar">
                <i class="ki-outline ki-trash fs-3"></i>
            </button>
        </div>
    </div>

    <div class="card-body row g-4">
        <div class="col-12">
            <label class="form-label required">Texto que dirá Miyu</label>
            <textarea class="form-control @error("messages.$index.text") is-invalid @enderror"
                name="messages[{{ $index }}][text]" rows="2" maxlength="300"
                placeholder="¿Qué te gustaría preguntarle al visitante?" required>{{ $text }}</textarea>
            @error("messages.$index.text")<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12">
            <label class="form-label">Texto corto en modo huella</label>
            <input class="form-control @error("messages.$index.peek") is-invalid @enderror"
                name="messages[{{ $index }}][peek]" value="{{ $peek }}" maxlength="160"
                placeholder="Si lo dejas vacío se usa el texto principal">
            @error("messages.$index.peek")<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label class="form-label required">Mostrar a</label>
            <select class="form-select @error("messages.$index.audience") is-invalid @enderror"
                name="messages[{{ $index }}][audience]" required>
                <option value="all" @selected($audience === 'all')>Todas las personas</option>
                <option value="guest" @selected($audience === 'guest')>Solo visitantes sin cuenta</option>
                <option value="authenticated" @selected($audience === 'authenticated')>Solo usuarios con sesión</option>
            </select>
            @error("messages.$index.audience")<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label class="form-label required">Qué sucede al responder</label>
            <select class="form-select @error("messages.$index.action_type") is-invalid @enderror"
                name="messages[{{ $index }}][action_type]" data-action-type required>
                <option value="none" @selected($actionType === 'none')>Sin botón</option>
                <option value="report" @selected($actionType === 'report')>Formulario: reportar problema</option>
                <option value="request" @selected($actionType === 'request')>Formulario: solicitar contenido</option>
                <option value="message" @selected($actionType === 'message')>Formulario: enviar mensaje</option>
                <option value="register" @selected($actionType === 'register')>Ir al registro</option>
                <option value="catalog" @selected($actionType === 'catalog')>Ir al catálogo</option>
                <option value="external" @selected($actionType === 'external')>Abrir enlace externo</option>
            </select>
            @error("messages.$index.action_type")<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6" data-action-label @if($actionType === 'none') hidden @endif>
            <label class="form-label">Texto del botón</label>
            <input class="form-control @error("messages.$index.label") is-invalid @enderror"
                name="messages[{{ $index }}][label]" value="{{ $label }}" maxlength="80"
                placeholder="Se usará un texto predeterminado si queda vacío">
            @error("messages.$index.label")<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6" data-external-url @if($actionType !== 'external') hidden @endif>
            <label class="form-label required">Enlace externo</label>
            <input class="form-control @error("messages.$index.url") is-invalid @enderror"
                name="messages[{{ $index }}][url]" value="{{ $url }}" type="url"
                placeholder="https://ejemplo.com">
            @error("messages.$index.url")<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>
