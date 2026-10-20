@props(['marathoners', 'recentEpisodes'])

<section class="sidebar-section sidebar-tabs-section" data-sidebar-tabs>
    <div class="sidebar-tabs" role="tablist" aria-label="Descubrir en Mundo Yuri">
        <button
            type="button"
            class="sidebar-tab is-active"
            id="sidebar-tab-marathoners"
            role="tab"
            aria-selected="true"
            aria-controls="sidebar-panel-marathoners"
            tabindex="0"
            data-sidebar-tab="marathoners"
        >
            Maratonistas del GL
        </button>
        <button
            type="button"
            class="sidebar-tab"
            id="sidebar-tab-latest"
            role="tab"
            aria-selected="false"
            aria-controls="sidebar-panel-latest"
            tabindex="-1"
            data-sidebar-tab="latest"
        >
            Lo más nuevo
        </button>
    </div>

    <div
        class="sidebar-panel sidebar-marathoners-panel"
        id="sidebar-panel-marathoners"
        role="tabpanel"
        aria-labelledby="sidebar-tab-marathoners"
        data-sidebar-panel="marathoners"
    >
        <p class="sidebar-panel-intro">Quienes más tiempo comparten con la comunidad.</p>
        <ol class="marathoner-list">
            @forelse($marathoners as $marathoner)
                <li class="marathoner-row marathoner-rank-{{ $loop->iteration }}">
                    <span class="marathoner-position" aria-label="Puesto {{ $loop->iteration }}">
                        @if($loop->iteration === 2)
                            <span aria-hidden="true">🥈</span>
                        @elseif($loop->iteration === 3)
                            <span aria-hidden="true">🥉</span>
                        @else
                            {{ $loop->iteration }}
                        @endif
                    </span>
                    <div class="marathoner-profile">
                        @if($loop->first)
                            <span class="marathoner-crown" aria-label="Primer lugar">👑</span>
                        @endif
                        <x-community.author-card :author="$marathoner" :show-reputation="true" />
                    </div>
                </li>
            @empty
                <li class="sidebar-empty-state">
                    <span aria-hidden="true">🎬</span>
                    <strong>El ranking está por comenzar</strong>
                    <small>La actividad de la comunidad aparecerá aquí.</small>
                </li>
            @endforelse
        </ol>
    </div>

    <div
        class="sidebar-panel"
        id="sidebar-panel-latest"
        role="tabpanel"
        aria-labelledby="sidebar-tab-latest"
        data-sidebar-panel="latest"
        hidden
    >
        <div class="sidebar-list sidebar-latest-list">
            @forelse($recentEpisodes as $item)
                <a href="{{ route('public.episodes.show', $item->slug) }}" class="sidebar-item">
                    <span class="sidebar-rank {{ $loop->iteration <= 3 ? 'top3' : '' }}">{{ $loop->iteration }}</span>
                    <div class="sidebar-thumb">
                        <img src="{{ $item->imageUrl('200/130') }}" alt="" loading="lazy">
                        <div class="sidebar-thumb-overlay">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="white" aria-hidden="true">
                                <path d="M8 5v14l11-7z" />
                            </svg>
                        </div>
                    </div>
                    <div class="sidebar-info">
                        <div class="sidebar-series">{{ $item->series->title ?? 'Serie' }}</div>
                        <div class="sidebar-year">E{{ $item->episode_number }} · {{ optional($item->release_date ?: $item->published_at)->format('d/m/Y') ?: 'Sin fecha' }}</div>
                    </div>
                </a>
            @empty
                <div class="sidebar-empty-state">
                    <span aria-hidden="true">🌸</span>
                    <strong>Aún no hay novedades</strong>
                    <small>Los episodios recientes aparecerán aquí.</small>
                </div>
            @endforelse
        </div>
    </div>
</section>

@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-sidebar-tabs]').forEach((tabs) => {
                const buttons = Array.from(tabs.querySelectorAll('[data-sidebar-tab]'));
                const panels = Array.from(tabs.querySelectorAll('[data-sidebar-panel]'));

                const activate = (button) => {
                    const target = button.dataset.sidebarTab;

                    buttons.forEach((item) => {
                        const active = item === button;
                        item.classList.toggle('is-active', active);
                        item.setAttribute('aria-selected', active ? 'true' : 'false');
                        item.tabIndex = active ? 0 : -1;
                    });

                    panels.forEach((panel) => {
                        panel.hidden = panel.dataset.sidebarPanel !== target;
                    });
                };

                buttons.forEach((button, index) => {
                    button.addEventListener('click', () => activate(button));
                    button.addEventListener('keydown', (event) => {
                        if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
                        event.preventDefault();
                        const direction = event.key === 'ArrowRight' ? 1 : -1;
                        const next = buttons[(index + direction + buttons.length) % buttons.length];
                        activate(next);
                        next.focus();
                    });
                });
            });
        });
    </script>
@endonce
