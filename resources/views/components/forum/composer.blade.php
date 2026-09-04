@props(['action', 'submit' => 'Publicar', 'body' => '', 'title' => null, 'httpMethod' => 'POST', 'useOldInput' => true])

<form method="POST" action="{{ $action }}" class="forum-composer profile-panel">
    @csrf
    {{ $slot }}
    @if($httpMethod !== 'POST')@method($httpMethod)@endif
    @if(! is_null($title))
        <div class="profile-field">
            <label for="forum-title">Título</label>
            <input id="forum-title" name="title" maxlength="180" required value="{{ $useOldInput ? old('title', $title) : $title }}">
            @if($useOldInput)
                @error('title')<small class="text-danger">{{ $message }}</small>@enderror
            @endif
        </div>
    @endif
    <div class="profile-field">
        <label for="forum-body">Mensaje</label>
        <textarea id="forum-body" name="body" rows="7" maxlength="12000" required placeholder="Escribe tu mensaje. Puedes mencionar a alguien con @alias.">{{ $useOldInput ? old('body', $body) : $body }}</textarea>
        <small class="forum-composer-help">Puedes mencionar a alguien con @alias. Máximo 12 000 caracteres.</small>
        @if($useOldInput)
            @error('body')<small class="text-danger">{{ $message }}</small>@enderror
        @endif
    </div>
    <button type="submit" class="profile-btn profile-btn-primary">{{ $submit }}</button>
</form>
