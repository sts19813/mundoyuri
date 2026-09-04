<?php

namespace App\Services;

use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CommunityActivityService
{
    /**
     * Builds a read-only feed from public community records. It intentionally
     * does not persist a second activity log, so private/admin activity cannot
     * leak into a profile feed.
     */
    public function forUser(User $user, bool $includeFavorites, int $perPage = 12): LengthAwarePaginator
    {
        $queries = [
            $this->threadActivities($user),
            $this->postActivities($user),
            $this->acceptedAnswerActivities($user),
            $this->badgeActivities($user),
        ];

        if ($includeFavorites) {
            $queries[] = $this->favoriteActivities($user);
        }

        /** @var Builder $union */
        $union = array_shift($queries);
        foreach ($queries as $query) {
            $union->unionAll($query);
        }

        return DB::query()
            ->fromSub($union, 'community_activity')
            ->orderByDesc('activity_at')
            ->orderByDesc('target_id')
            ->paginate($perPage, ['*'], 'activity_page')
            ->through(function (object $activity): object {
                $activity->occurred_at = Carbon::parse($activity->activity_at);
                $activity->url = $this->urlFor($activity);

                return $activity;
            });
    }

    private function threadActivities(User $user): Builder
    {
        return ForumThread::query()
            ->leftJoin('forums', 'forums.id', '=', 'forum_threads.forum_id')
            ->leftJoin('forum_categories', 'forum_categories.id', '=', 'forums.forum_category_id')
            ->where('forum_threads.user_id', $user->id)
            ->where('forum_threads.is_hidden', false)
            ->where(function ($query): void {
                $query->where('forum_threads.type', 'question')
                    ->orWhere(function ($query): void {
                        $query->where('forums.is_active', true)
                            ->where('forum_categories.is_active', true);
                    });
            })
            ->selectRaw("CASE WHEN forum_threads.type = 'question' THEN 'created_question' ELSE 'created_thread' END AS activity_type")
            ->selectRaw('forum_threads.id AS target_id, NULL AS post_id, forum_threads.title, forum_threads.slug, forum_threads.type AS thread_type, forum_threads.created_at AS activity_at')
            ->toBase();
    }

    private function postActivities(User $user): Builder
    {
        return ForumPost::query()
            ->join('forum_threads', 'forum_threads.id', '=', 'forum_posts.forum_thread_id')
            ->leftJoin('forums', 'forums.id', '=', 'forum_threads.forum_id')
            ->leftJoin('forum_categories', 'forum_categories.id', '=', 'forums.forum_category_id')
            ->where('forum_posts.user_id', $user->id)
            ->where('forum_posts.is_initial', false)
            ->where('forum_posts.is_hidden', false)
            ->whereNull('forum_threads.deleted_at')
            ->where('forum_threads.is_hidden', false)
            ->where(function ($query): void {
                $query->where('forum_threads.type', 'question')
                    ->orWhere(function ($query): void {
                        $query->where('forums.is_active', true)
                            ->where('forum_categories.is_active', true);
                    });
            })
            ->selectRaw("CASE WHEN forum_threads.type = 'question' THEN 'answered_question' ELSE 'replied_thread' END AS activity_type")
            ->selectRaw('forum_threads.id AS target_id, forum_posts.id AS post_id, forum_threads.title, forum_threads.slug, forum_threads.type AS thread_type, forum_posts.created_at AS activity_at')
            ->toBase();
    }

    private function acceptedAnswerActivities(User $user): Builder
    {
        return ForumThread::query()
            ->join('forum_posts AS accepted_posts', 'accepted_posts.id', '=', 'forum_threads.accepted_answer_post_id')
            ->where('accepted_posts.user_id', $user->id)
            ->where('accepted_posts.is_initial', false)
            ->where('accepted_posts.is_hidden', false)
            ->whereNull('accepted_posts.deleted_at')
            ->where('forum_threads.type', 'question')
            ->where('forum_threads.is_hidden', false)
            ->selectRaw("'accepted_answer' AS activity_type")
            ->selectRaw('forum_threads.id AS target_id, accepted_posts.id AS post_id, forum_threads.title, forum_threads.slug, forum_threads.type AS thread_type, forum_threads.accepted_answer_at AS activity_at')
            ->toBase();
    }

    private function favoriteActivities(User $user): Builder
    {
        return DB::table('series_favorites')
            ->join('series', 'series.id', '=', 'series_favorites.series_id')
            ->where('series_favorites.user_id', $user->id)
            ->where('series.moderation_status', 'approved')
            ->whereNotNull('series.published_at')
            ->selectRaw("'favorite_series' AS activity_type")
            ->selectRaw('series.id AS target_id, NULL AS post_id, series.title, series.slug, NULL AS thread_type, series_favorites.created_at AS activity_at');
    }

    private function badgeActivities(User $user): Builder
    {
        return DB::table('badge_user')
            ->join('badges', 'badges.id', '=', 'badge_user.badge_id')
            ->where('badge_user.user_id', $user->id)
            ->where('badges.is_active', true)
            ->selectRaw("'badge_awarded' AS activity_type")
            ->selectRaw('badges.id AS target_id, NULL AS post_id, badges.name AS title, NULL AS slug, NULL AS thread_type, badge_user.awarded_at AS activity_at');
    }

    private function urlFor(object $activity): ?string
    {
        return match ($activity->activity_type) {
            'created_thread', 'replied_thread' => route('forum.threads.show', ['thread' => $activity->slug]).($activity->post_id ? '#post-'.$activity->post_id : ''),
            'created_question', 'answered_question', 'accepted_answer' => route('questions.show', ['thread' => $activity->slug]).($activity->post_id ? '#post-'.$activity->post_id : ''),
            'favorite_series' => route('catalog.series.show', ['series' => $activity->slug]),
            default => null,
        };
    }
}
