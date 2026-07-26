@extends('layouts.admin')

@section('title', 'Configurar Miyu - Mundo Yuri')

@section('toolbar')
    <div class="d-flex align-items-center gap-3">
        <div>
            <h1 class="fs-2 fw-bold mb-1">Configurar Miyu</h1>
            <div class="text-muted fs-7">Ajusta cuándo aparece, qué pregunta y cómo puede responder el visitante.</div>
        </div>
    </div>
@endsection

@section('content')
    @php
        $formMessages = old('messages', $settings->messages ?: \App\Models\AssistantSetting::defaultMessages());
    @endphp

    <form method="POST" action="{{ route('admin.settings.assistant.update') }}" data-assistant-settings-form>
        @csrf
        @method('PUT')

        <div class="row g-5 g-xl-8">
            <div class="col-xl-5">
                <div class="card mb-5">
                    <div class="card-header">
                        <div class="card-title"><h3 class="fw-bold m-0">Comportamiento</h3></div>
                    </div>
                    <div class="card-body row g-5">
                        <div class="col-12">
                            <label class="form-check form-switch form-check-custom form-check-solid">
                                <input type="hidden" name="enabled" value="0">
                                <input class="form-check-input" type="checkbox" name="enabled" value="1"
                                    @checked((bool) old('enabled', $settings->enabled))>
                                <span class="form-check-label fw-semibold text-gray-700">Mostrar a Miyu en el sitio</span>
                            </label>
                        </div>

                        <div class="col-12">
                            <label class="form-label required">Estado al iniciar</label>
                            <select class="form-select @error('initial_state') is-invalid @enderror" name="initial_state">
                                <option value="minimized" @selected(old('initial_state', $settings->initial_state) === 'minimized')>
                                    Modo huella
                                </option>
                                <option value="expanded" @selected(old('initial_state', $settings->initial_state) === 'expanded')>
                                    Miyu abierta
                                </option>
                            </select>
                            @error('initial_state')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-check form-switch form-check-custom form-check-solid">
                                <input type="hidden" name="remember_user_state" value="0">
                                <input class="form-check-input" type="checkbox" name="remember_user_state" value="1"
                                    @checked((bool) old('remember_user_state', $settings->remember_user_state))>
                                <span class="form-check-label fw-semibold text-gray-700">Recordar si cada visitante la abrió o minimizó</span>
                            </label>
                            <div class="form-text">Si se desactiva, Miyu siempre respetará el estado inicial elegido al cargar una página.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Primera pregunta</label>
                            <div class="input-group">
                                <input class="form-control @error('initial_delay_seconds') is-invalid @enderror"
                                    name="initial_delay_seconds" type="number" min="0" max="600"
                                    value="{{ old('initial_delay_seconds', $settings->initial_delay_seconds) }}" required>
                                <span class="input-group-text">segundos</span>
                            </div>
                            @error('initial_delay_seconds')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div class="form-text">Usa 0 para mostrarla inmediatamente.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Repetir cada</label>
                            <div class="input-group">
                                <input class="form-control @error('message_interval_seconds') is-invalid @enderror"
                                    name="message_interval_seconds" type="number" min="5" max="3600"
                                    value="{{ old('message_interval_seconds', $settings->message_interval_seconds) }}" required>
                                <span class="input-group-text">segundos</span>
                            </div>
                            @error('message_interval_seconds')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Burbuja abierta visible</label>
                            <div class="input-group">
                                <input class="form-control @error('bubble_duration_seconds') is-invalid @enderror"
                                    name="bubble_duration_seconds" type="number" min="3" max="60"
                                    value="{{ old('bubble_duration_seconds', $settings->bubble_duration_seconds) }}" required>
                                <span class="input-group-text">segundos</span>
                            </div>
                            @error('bubble_duration_seconds')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Aviso junto a la huella</label>
                            <div class="input-group">
                                <input class="form-control @error('peek_duration_seconds') is-invalid @enderror"
                                    name="peek_duration_seconds" type="number" min="3" max="60"
                                    value="{{ old('peek_duration_seconds', $settings->peek_duration_seconds) }}" required>
                                <span class="input-group-text">segundos</span>
                            </div>
                            @error('peek_duration_seconds')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <h4 class="alert-heading">Cómo funcionan los tiempos</h4>
                    <p class="mb-0">La primera pregunta usa el retraso inicial. Las siguientes recorren la lista, en el orden mostrado, usando el intervalo configurado.</p>
                </div>
            </div>

            <div class="col-xl-7">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <div>
                                <h3 class="fw-bold mb-1">Preguntas y mensajes</h3>
                                <div class="text-muted fs-7">Puedes ordenar, eliminar o agregar hasta 20 mensajes.</div>
                            </div>
                        </div>
                        <div class="card-toolbar">
                            <button class="btn btn-light-primary" type="button" data-add-message>
                                <i class="ki-outline ki-plus fs-2"></i> Agregar mensaje
                            </button>
                        </div>
                    </div>
                    <div class="card-body" data-message-list>
                        @foreach($formMessages as $index => $message)
                            @include('admin.settings.partials.assistant-message-fields', compact('message', 'index'))
                        @endforeach
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <span class="text-muted fs-7"><span data-message-count>{{ count($formMessages) }}</span> de 20 mensajes</span>
                        <button class="btn btn-primary" type="submit">Guardar configuración</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <template data-message-template>
        @include('admin.settings.partials.assistant-message-fields', [
            'message' => [
                'text' => '',
                'peek' => '',
                'audience' => 'all',
                'action_type' => 'none',
                'label' => '',
                'url' => '',
            ],
            'index' => 0,
        ])
    </template>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('[data-assistant-settings-form]');

            if (!form) {
                return;
            }

            const list = form.querySelector('[data-message-list]');
            const template = document.querySelector('[data-message-template]');
            const addButton = form.querySelector('[data-add-message]');
            const count = form.querySelector('[data-message-count]');

            const updateActionFields = (item) => {
                const actionType = item.querySelector('[data-action-type]').value;
                const label = item.querySelector('[data-action-label]');
                const externalUrl = item.querySelector('[data-external-url]');
                const urlInput = externalUrl.querySelector('input');

                label.hidden = actionType === 'none';
                externalUrl.hidden = actionType !== 'external';
                urlInput.required = actionType === 'external';
            };

            const reindex = () => {
                const items = [...list.querySelectorAll('[data-message-item]')];

                items.forEach((item, index) => {
                    item.querySelector('[data-message-number]').textContent = `#${index + 1}`;
                    item.querySelectorAll('[name]').forEach((field) => {
                        field.name = field.name.replace(/messages\[\d+]/, `messages[${index}]`);
                    });
                    item.querySelector('[data-move-up]').disabled = index === 0;
                    item.querySelector('[data-move-down]').disabled = index === items.length - 1;
                    item.querySelector('[data-remove-message]').disabled = items.length === 1;
                    updateActionFields(item);
                });

                count.textContent = String(items.length);
                addButton.disabled = items.length >= 20;
            };

            list.addEventListener('change', (event) => {
                const actionSelect = event.target.closest('[data-action-type]');

                if (actionSelect) {
                    updateActionFields(actionSelect.closest('[data-message-item]'));
                }
            });

            list.addEventListener('click', (event) => {
                const item = event.target.closest('[data-message-item]');

                if (!item) {
                    return;
                }

                if (event.target.closest('[data-remove-message]')) {
                    item.remove();
                } else if (event.target.closest('[data-move-up]') && item.previousElementSibling) {
                    list.insertBefore(item, item.previousElementSibling);
                } else if (event.target.closest('[data-move-down]') && item.nextElementSibling) {
                    list.insertBefore(item.nextElementSibling, item);
                } else {
                    return;
                }

                reindex();
            });

            addButton.addEventListener('click', () => {
                if (list.querySelectorAll('[data-message-item]').length >= 20) {
                    return;
                }

                list.append(template.content.cloneNode(true));
                reindex();
                list.lastElementChild.querySelector('textarea').focus();
            });

            form.addEventListener('submit', reindex);
            reindex();
        });
    </script>
@endpush
