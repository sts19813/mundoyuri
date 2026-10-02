@props([
    'id',
    'name',
    'value' => '',
    'placeholder' => '',
    'minHeight' => '260px',
])

@once
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js" defer></script>
    <script src="{{ asset('assets/js/rich-editor.js') }}?v={{ filemtime(public_path('assets/js/rich-editor.js')) }}" defer></script>
@endonce

<div
    class="rich-editor"
    data-rich-editor
    data-upload-url="{{ route('editor.images.store') }}"
    data-placeholder="{{ $placeholder }}"
>
    <textarea
        id="{{ $id }}"
        name="{{ $name }}"
        class="rich-editor-source"
        data-rich-editor-source
    >{{ $value }}</textarea>
    <div class="rich-editor-toolbar" data-rich-editor-toolbar>
        <span class="ql-formats">
            <select class="ql-header">
                <option selected></option>
                <option value="2">Título</option>
                <option value="3">Subtítulo</option>
            </select>
        </span>
        <span class="ql-formats">
            <button type="button" class="ql-bold" aria-label="Negrita"></button>
            <button type="button" class="ql-italic" aria-label="Cursiva"></button>
            <button type="button" class="ql-underline" aria-label="Subrayado"></button>
            <button type="button" class="ql-strike" aria-label="Tachado"></button>
        </span>
        <span class="ql-formats">
            <button type="button" class="ql-blockquote" aria-label="Cita"></button>
            <button type="button" class="ql-code-block" aria-label="Código"></button>
        </span>
        <span class="ql-formats">
            <button type="button" class="ql-list" value="ordered" aria-label="Lista numerada"></button>
            <button type="button" class="ql-list" value="bullet" aria-label="Lista"></button>
        </span>
        <span class="ql-formats">
            <select class="ql-align">
                <option selected></option>
                <option value="center"></option>
                <option value="right"></option>
                <option value="justify"></option>
            </select>
        </span>
        <span class="ql-formats">
            <button type="button" class="ql-link" aria-label="Enlace"></button>
            <button type="button" class="ql-image" aria-label="Subir imagen"></button>
            <button type="button" class="ql-video" aria-label="Insertar video"></button>
        </span>
        <span class="ql-formats">
            <button type="button" class="ql-clean" aria-label="Limpiar formato"></button>
        </span>
    </div>
    <div
        class="rich-editor-field"
        data-rich-editor-field
        style="--rich-editor-min-height: {{ $minHeight }}"
    ></div>
    <p class="rich-editor-status" data-rich-editor-status aria-live="polite"></p>
</div>
