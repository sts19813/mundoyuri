@props(['action', 'submit' => 'Publicar', 'body' => '', 'title' => null, 'httpMethod' => 'POST'])

<form method="POST" action="{{ $action }}" class="forum-composer profile-panel">
    @csrf
    @if($httpMethod !== 'POST')@method($httpMethod)@endif
    @if(! is_null($title))
        <div class="profile-field"><label for="forum-title">Título</label><input id="forum-title" name="title" maxlength="180" required value="{{ old('title', $title) }}">@error('title')<small class="text-danger">{{ $message }}</small>@enderror</div>
    @endif
    <div class="profile-field"><label for="forum-body">Mensaje</label><textarea id="forum-body" name="body" rows="7" maxlength="12000" required placeholder="Escribe tu mensaje. Puedes mencionar a alguien con @alias.">{{ old('body', $body) }}</textarea><small class="forum-composer-help">Texto plano: no se permite HTML. Máximo 12 000 caracteres.</small>@error('body')<small class="text-danger">{{ $message }}</small>@enderror</div>
    <button type="submit" class="profile-btn profile-btn-primary">{{ $submit }}</button>
</form>
