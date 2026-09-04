@props(['reportable'])

@php($target = match (true) { $reportable instanceof \App\Models\ForumThread => 'thread', $reportable instanceof \App\Models\ForumPost => 'post', $reportable instanceof \App\Models\User => 'user', default => null })

@auth
    @if($target && ! auth()->user()->is($reportable))
        <details class="community-report-form">
            <summary>Reportar</summary>
            <form method="POST" action="{{ route('community.reports.store') }}">
                @csrf
                <input type="hidden" name="target" value="{{ $target }}">
                <input type="hidden" name="target_id" value="{{ $reportable->id }}">
                <label>Motivo
                    <select name="reason" required>
                        <option value="">Selecciona un motivo</option>
                        <option value="spam">Spam</option>
                        <option value="harassment">Acoso</option>
                        <option value="inappropriate_content">Contenido inapropiado</option>
                        <option value="unmarked_spoiler">Spoiler sin marcar</option>
                        <option value="personal_information">Información personal</option>
                        <option value="other">Otro</option>
                    </select>
                </label>
                <label>Detalles <small>(opcional)</small><textarea name="details" maxlength="2000" rows="3"></textarea></label>
                <button type="submit">Enviar reporte</button>
            </form>
        </details>
    @endif
@endauth
