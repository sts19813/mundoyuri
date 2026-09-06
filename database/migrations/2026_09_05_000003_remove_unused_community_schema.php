<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('forum_thread_question_tag');
        Schema::dropIfExists('question_tags');

        DB::table('comments')
            ->whereNotNull('reply_to_comment_id')
            ->orderBy('id')
            ->chunkById(500, function ($comments): void {
                $targets = DB::table('comments')
                    ->whereIn('id', $comments->pluck('reply_to_comment_id')->unique())
                    ->get(['id', 'commentable_type', 'commentable_id'])
                    ->keyBy('id');

                foreach ($comments as $comment) {
                    $target = $targets->get($comment->reply_to_comment_id);
                    if ($target
                        && $target->commentable_type === $comment->commentable_type
                        && (int) $target->commentable_id === (int) $comment->commentable_id) {
                        DB::table('comments')->where('id', $comment->id)->update([
                            'parent_id' => $comment->reply_to_comment_id,
                        ]);
                    }
                }
            }, 'id');

        Schema::table('comments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('reply_to_comment_id');
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table): void {
            $table->foreignId('reply_to_comment_id')->nullable()->constrained('comments')->nullOnDelete();
        });
        DB::table('comments')->whereNotNull('parent_id')->update([
            'reply_to_comment_id' => DB::raw('parent_id'),
        ]);

        Schema::create('question_tags', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('slug', 60)->unique();
            $table->timestamps();
        });

        Schema::create('forum_thread_question_tag', function (Blueprint $table): void {
            $table->foreignId('forum_thread_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['forum_thread_id', 'question_tag_id']);
            $table->index('question_tag_id');
        });
    }
};
