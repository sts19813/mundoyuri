<?php

namespace App\Services;

use App\Models\ForumPost;
use App\Models\ForumPostVote;
use App\Models\ForumThread;
use App\Models\ForumThreadVote;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class QuestionVoteService
{
    public function voteQuestion(ForumThread $question, User $voter): bool
    {
        return DB::transaction(function () use ($question, $voter): bool {
            $question = ForumThread::query()->with('author')->lockForUpdate()->findOrFail($question->id);

            try {
                ForumThreadVote::query()->create(['forum_thread_id' => $question->id, 'user_id' => $voter->id]);
            } catch (QueryException) {
                return false;
            }

            $question->increment('upvotes_count');
            $question->author?->increment('community_reputation');

            return true;
        });
    }

    public function voteAnswer(ForumPost $answer, User $voter): bool
    {
        return DB::transaction(function () use ($answer, $voter): bool {
            $answer = ForumPost::query()->with('author')->lockForUpdate()->findOrFail($answer->id);

            try {
                ForumPostVote::query()->create(['forum_post_id' => $answer->id, 'user_id' => $voter->id]);
            } catch (QueryException) {
                return false;
            }

            $answer->increment('upvotes_count');
            $answer->author?->increment('community_reputation');

            return true;
        });
    }
}
