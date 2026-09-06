@props(['id' => 'forum-image', 'showErrors' => true, 'buttonLabel' => 'Añadir imagen'])

<div class="forum-image-field" data-image-field>
    <input id="{{ $id }}" type="file" name="image" accept="image/jpeg,image/png,image/webp" data-image-input>
    <label for="{{ $id }}" class="forum-image-button">
        <span aria-hidden="true">▧</span> {{ $buttonLabel }}
    </label>
    <span class="forum-image-name" data-image-name>JPG, PNG o WebP · se optimiza a menos de 800 KB.</span>
    @if($showErrors)
        @error('image')<small class="text-danger">{{ $message }}</small>@enderror
    @endif
</div>
