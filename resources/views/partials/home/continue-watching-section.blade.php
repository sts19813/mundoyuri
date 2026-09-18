@if(($continueWatching ?? collect())->isNotEmpty())
    <section class="continue-section" id="continuar-mirando">
        <div class="container-xl px-4">
            <div class="section-header">
                <h2 class="section-title">Continuar mirando</h2>
                <span class="section-link section-link-muted">Tus últimos {{ $continueWatching->count() }}</span>
            </div>
            <div class="continue-rail">
                @foreach($continueWatching as $progress)
                    @php
                        $episode = $progress->episode;
                        $series = $episode?->series;
                        $isNextEpisode = (bool) data_get($progress, 'is_next_episode', false);
                        $percent = $progress->progress_percent ?? ($progress->duration_seconds ? ($progress->position_seconds / $progress->duration_seconds) * 100 : 0);
                        $percent = $isNextEpisode ? 0 : max(3, min(100, (int) round($percent)));
                        $minutes = intdiv($progress->position_seconds, 60);
                        $seconds = $progress->position_seconds % 60;
                        $resumeUrl = $episode
                            ? route('public.episodes.show', $episode->slug, false).'?'.http_build_query(array_filter([
                                'resume' => $isNextEpisode ? null : 1,
                                't' => $isNextEpisode ? null : $progress->position_seconds,
                                'source' => $progress->episode_source_id,
                            ], fn ($value) => $value !== null))
                            : '#';
                    @endphp
                    @if($episode && $series)
                        <a href="{{ $resumeUrl }}" class="continue-card">
                            <div class="continue-thumb">
                                <x-media-preview
                                    :src="$series->bannerMediaUrl() ?: $episode->previewMediaUrl('640/360')"
                                    :type="$series->bannerMediaUrl() ? $series->bannerMediaType() : $episode->previewMediaType()"
                                    :alt="$series->title"
                                    class="continue-thumb-media"
                                />
                                <span class="continue-play" aria-hidden="true">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z" /></svg>
                                </span>
                                <div class="continue-progress" aria-hidden="true"><span style="width: {{ $percent }}%"></span></div>
                            </div>
                            <div class="continue-info">
                                <span class="continue-kicker">
                                    @if($isNextEpisode)
                                        Continuar con T{{ $episode->season_number }} · E{{ $episode->episode_number }}
                                    @else
                                        T{{ $episode->season_number }} · E{{ $episode->episode_number }} · {{ sprintf('%d:%02d', $minutes, $seconds) }}
                                    @endif
                                </span>
                                <h3>{{ $series->title }}</h3>
                                <small>{{ $episode->title }}</small>
                            </div>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    </section>
@endif
