<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\BadgeAwardLog;
use App\Models\Comment;
use App\Models\CommunityReaction;
use App\Models\DirectMessage;
use App\Models\Episode;
use App\Models\EpisodeWatchProgress;
use App\Models\User;
use Carbon\CarbonInterface;

class BadgeAutomationService
{
    /**
     * The catalog rules deliberately live in code: every automatic criterion is
     * reviewed, versioned and visible in the administration module.
     *
     * @return array<string, array{condition: string, evaluator: callable(array<string, mixed>, User): bool}>
     */
    public function definitions(): array
    {
        return [
            'miembro-historico' => $this->rule('Es un miembro histórico con identidad verificada.', fn ($m, $u) => $u->is_legacy && $u->legacy_verified),
            'pionera-2007' => $this->rule('Es miembro histórico verificado y su fecha histórica es de 2007 o anterior.', fn ($m, $u) => $u->is_legacy && $u->legacy_verified && $this->legacyYearAtMost($u, 2007)),
            'generacion-2008' => $this->rule('Es miembro histórico verificado y su fecha histórica corresponde a 2008.', fn ($m, $u) => $u->is_legacy && $u->legacy_verified && $u->legacy_joined_at?->year === 2008),
            'staff' => $this->rule('Tiene el rol de administración o moderación.', fn ($m, $u) => $m['is_admin'] || $m['is_moderator']),
            'moderacion' => $this->rule('Tiene el rol de moderación.', fn ($m) => $m['is_moderator']),
            'administracion' => $this->rule('Tiene el rol de administración.', fn ($m) => $m['is_admin']),
            'primeros-pasos' => $this->rule('Envió al menos 1 mensaje por chat o publicó 1 comentario aprobado en un capítulo.', fn ($m) => $m['participation'] >= 1),
            'primera-conversacion' => $this->rule('Envió al menos 1 mensaje por chat que no fue eliminado.', fn ($m) => $m['sent_messages'] >= 1),
            'autora-de-temas' => $this->rule('Publicó al menos 1 comentario aprobado en un capítulo.', fn ($m) => $m['chapter_comments'] >= 1),
            'tema-en-llamas' => $this->rule('Un comentario suyo en un capítulo recibió 10 o más reacciones positivas.', fn ($m) => $m['hot_comments'] >= 1),
            'centenar' => $this->rule('Suma 100 mensajes de chat enviados y comentarios aprobados en capítulos.', fn ($m) => $m['participation'] >= 100),
            'conversadora' => $this->rule('Suma 250 mensajes de chat enviados y comentarios aprobados en capítulos.', fn ($m) => $m['participation'] >= 250),
            'cronista' => $this->rule('Suma 500 mensajes de chat enviados y comentarios aprobados en capítulos.', fn ($m) => $m['participation'] >= 500),
            'mil-historias' => $this->rule('Suma 1,000 mensajes de chat enviados y comentarios aprobados en capítulos.', fn ($m) => $m['participation'] >= 1000),
            'curiosa' => $this->rule('Publicó su primer comentario aprobado en un capítulo.', fn ($m) => $m['chapter_comments'] >= 1),
            'tengo-la-respuesta' => $this->rule('Recibió al menos 1 respuesta directa a uno de sus mensajes de chat.', fn ($m) => $m['received_replies'] >= 1),
            'yuri-scholar' => $this->rule('Recibió 5 o más respuestas directas a sus mensajes de chat.', fn ($m) => $m['received_replies'] >= 5),
            'sensei' => $this->rule('Recibió 20 o más respuestas directas a sus mensajes de chat.', fn ($m) => $m['received_replies'] >= 20),
            'primer-amor' => $this->rule('Agregó al menos 1 obra a favoritos.', fn ($m) => $m['favorites'] >= 1),
            'coleccionista-gl' => $this->rule('Agregó 10 o más obras distintas a favoritos.', fn ($m) => $m['favorites'] >= 10),
            'exploradora-gl' => $this->rule('Agregó 20 o más obras distintas a favoritos.', fn ($m) => $m['favorites'] >= 20),
            'maratonista' => $this->rule('Completó 25 o más episodios distintos.', fn ($m) => $m['completed_episodes'] >= 25),
            'yuri-lover' => $this->rule('Suma 30 o más obras favoritas y episodios completados.', fn ($m) => $m['catalog_activity'] >= 30),
            'buen-ojo' => $this->rule('Su contenido recibió 10 o más reacciones positivas.', fn ($m) => $m['positive_reactions'] >= 10),
            'querida-por-la-comunidad' => $this->rule('Su contenido recibió 50 o más reacciones positivas.', fn ($m) => $m['positive_reactions'] >= 50),
            'conectada' => $this->rule('Suma 10 o más conexiones entre personas seguidas y seguidoras.', fn ($m) => $m['connections'] >= 10),
            'mariposa-social' => $this->rule('Suma 30 o más conexiones entre personas seguidas y seguidoras.', fn ($m) => $m['connections'] >= 30),
            'primer-aniversario' => $this->rule('Su cuenta moderna tiene al menos 1 año de antigüedad.', fn ($m, $u) => $this->registeredFor($u, 1)),
            'tres-primaveras' => $this->rule('Su cuenta moderna tiene al menos 3 años de antigüedad.', fn ($m, $u) => $this->registeredFor($u, 3)),
            'cinco-primaveras' => $this->rule('Su cuenta moderna tiene al menos 5 años de antigüedad.', fn ($m, $u) => $this->registeredFor($u, 5)),
            'una-decada-juntas' => $this->rule('Su cuenta moderna tiene al menos 10 años de antigüedad.', fn ($m, $u) => $this->registeredFor($u, 10)),
        ];
    }

    public function definitionFor(?Badge $badge): ?array
    {
        return $badge ? ($this->definitions()[$badge->slug] ?? null) : null;
    }

    /** @return array{awarded: int, revoked: int} */
    public function sync(User $user): array
    {
        if ($user->id === 1) {
            return $this->revokeAutomaticAwardsForExcludedAccount($user);
        }

        // The founder's profile is curator-owned: automation never adds,
        // recalculates or removes recognitions from an account with this badge.
        if ($user->badges()->where('slug', 'fundadora')->exists()) {
            return ['awarded' => 0, 'revoked' => 0];
        }

        $definitions = $this->definitions();
        $badges = Badge::query()->active()->whereIn('slug', array_keys($definitions))->get()->keyBy('slug');
        if ($badges->isEmpty()) {
            return ['awarded' => 0, 'revoked' => 0];
        }

        $user->loadMissing('roles');
        $metrics = $this->metricsFor($user);
        $current = $user->badges()->whereIn('badges.id', $badges->pluck('id'))->get()->keyBy('id');
        $awarded = 0;
        $revoked = 0;

        foreach ($badges as $slug => $badge) {
            $definition = $definitions[$slug];
            $qualifies = (bool) ($definition['evaluator'])($metrics, $user);
            $award = $current->get($badge->id);

            if ($qualifies && ! $award) {
                $user->badges()->attach($badge->id, [
                    'awarded_at' => now(),
                    'note' => 'Asignación automática: '.$definition['condition'],
                    'assignment_source' => 'automatic',
                    'automation_rule' => $slug,
                ]);
                $this->log($user, $badge, 'assigned', 'automatic', $slug, $definition['condition'], $metrics);
                $awarded++;
            }

            if (! $qualifies && $award && $award->pivot->assignment_source === 'automatic') {
                $user->badges()->detach($badge->id);
                $this->log($user, $badge, 'revoked', 'automatic', $slug, $definition['condition'], $metrics);
                $revoked++;
            }
        }

        return compact('awarded', 'revoked');
    }

    /** @return array{users: int, awarded: int, revoked: int} */
    public function syncAll(): array
    {
        $result = ['users' => 0, 'awarded' => 0, 'revoked' => 0];

        User::query()->orderBy('id')->each(function (User $user) use (&$result): void {
            $sync = $this->sync($user);
            $result['users']++;
            $result['awarded'] += $sync['awarded'];
            $result['revoked'] += $sync['revoked'];
        });

        return $result;
    }

    public function recordManual(User $user, Badge $badge, User $actor, string $action, ?string $note = null): void
    {
        $this->log($user, $badge, $action, 'manual', null, null, [], $actor, $note);
    }

    /** @return array<string, mixed> */
    private function metricsFor(User $user): array
    {
        $userId = $user->id;
        $positiveTypes = ['like', 'love', 'fire', 'yuri', 'pray'];
        $episodeType = (new Episode)->getMorphClass();
        $commentType = (new Comment)->getMorphClass();
        $favorites = $user->favoriteSeries()->count();
        $completedEpisodes = EpisodeWatchProgress::query()->where('user_id', $userId)->where('completed', true)->count();
        $chapterComments = Comment::query()
            ->where('user_id', $userId)
            ->where('is_approved', true)
            ->where('commentable_type', $episodeType);
        $sentMessages = DirectMessage::query()->where('sender_id', $userId)->whereNull('deleted_at');

        $positiveReactions = CommunityReaction::query()
            ->whereIn('type', $positiveTypes)
            ->where(function ($query) use ($userId, $commentType): void {
                $query->where(function ($query) use ($userId, $commentType): void {
                    $query->where('reactable_type', $commentType)
                        ->whereIn('reactable_id', Comment::query()->select('id')->where('user_id', $userId)->where('is_approved', true));
                });
            })->count();

        $hotComments = CommunityReaction::query()
            ->where('reactable_type', $commentType)
            ->whereIn('type', $positiveTypes)
            ->whereIn('reactable_id', (clone $chapterComments)->select('id'))
            ->select('reactable_id')
            ->groupBy('reactable_id')
            ->havingRaw('COUNT(*) >= 10')
            ->get()
            ->count();

        return [
            'is_admin' => $user->hasRole('admin') || $user->role === 'admin',
            'is_moderator' => $user->hasRole('moderator') || $user->role === 'moderator',
            'sent_messages' => (clone $sentMessages)->count(),
            'chapter_comments' => (clone $chapterComments)->count(),
            'participation' => (clone $sentMessages)->count() + (clone $chapterComments)->count(),
            'hot_comments' => $hotComments,
            'received_replies' => DirectMessage::query()
                ->where('recipient_id', $userId)
                ->whereNull('deleted_at')
                ->whereIn('reply_to_message_id', DirectMessage::query()->select('id')->where('sender_id', $userId)->whereNull('deleted_at'))
                ->count(),
            'favorites' => $favorites,
            'completed_episodes' => $completedEpisodes,
            'positive_reactions' => $positiveReactions,
            'connections' => $user->following()->count() + $user->followers()->count(),
            'catalog_activity' => $favorites + $completedEpisodes,
        ];
    }

    private function legacyYearAtMost(User $user, int $year): bool
    {
        return $user->legacy_joined_at !== null && $user->legacy_joined_at->year <= $year;
    }

    private function registeredFor(User $user, int $years): bool
    {
        return $user->created_at instanceof CarbonInterface && $user->created_at->lessThanOrEqualTo(now()->subYears($years));
    }

    /** @return array{condition: string, evaluator: callable(array<string, mixed>, User): bool} */
    private function rule(string $condition, callable $evaluator): array
    {
        return compact('condition', 'evaluator');
    }

    /** @return array{awarded: int, revoked: int} */
    private function revokeAutomaticAwardsForExcludedAccount(User $user): array
    {
        $awards = $user->badges()
            ->wherePivot('assignment_source', 'automatic')
            ->get();

        foreach ($awards as $badge) {
            $user->badges()->detach($badge->id);
            $this->log(
                $user,
                $badge,
                'revoked',
                'automatic',
                $badge->pivot->automation_rule,
                'La cuenta administrativa ID 1 está excluida de las insignias automáticas.',
                [],
            );
        }

        return ['awarded' => 0, 'revoked' => $awards->count()];
    }

    /** @param array<string, mixed> $metadata */
    private function log(User $user, Badge $badge, string $action, string $source, ?string $rule, ?string $condition, array $metadata, ?User $actor = null, ?string $note = null): void
    {
        BadgeAwardLog::query()->create([
            'user_id' => $user->id,
            'badge_id' => $badge->id,
            'actor_id' => $actor?->id,
            'badge_name' => $badge->name,
            'badge_slug' => $badge->slug,
            'action' => $action,
            'source' => $source,
            'automation_rule' => $rule,
            'condition' => $condition,
            'note' => $note,
            'metadata' => $metadata ?: null,
        ]);
    }
}
