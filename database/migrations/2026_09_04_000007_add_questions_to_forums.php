<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('slug', 60)->unique();
            $table->timestamps();

            $table->unique('name');
        });

        Schema::table('forum_threads', function (Blueprint $table) {
            $table->string('type', 20)->default('discussion')->index()->after('slug');
            $table->unsignedInteger('upvotes_count')->default(0)->after('views_count');
            $table->foreignId('accepted_answer_post_id')->nullable()->after('last_post_at')
                ->constrained('forum_posts')->nullOnDelete();
            $table->timestamp('accepted_answer_at')->nullable()->after('accepted_answer_post_id');
            $table->index(['type', 'is_hidden', 'created_at'], 'forum_threads_question_listing_index');
            $table->index(['type', 'replies_count', 'created_at'], 'forum_threads_question_unanswered_index');
            $table->index(['type', 'upvotes_count', 'views_count'], 'forum_threads_question_popular_index');
        });

        Schema::table('forum_posts', function (Blueprint $table) {
            $table->unsignedInteger('upvotes_count')->default(0)->after('is_hidden');
        });

        Schema::create('forum_thread_question_tag', function (Blueprint $table) {
            $table->foreignId('forum_thread_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_tag_id')->constrained()->cascadeOnDelete();

            $table->primary(['forum_thread_id', 'question_tag_id']);
            $table->index('question_tag_id');
        });

        Schema::create('forum_thread_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_thread_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['forum_thread_id', 'user_id']);
            $table->index('user_id');
        });

        Schema::create('forum_post_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['forum_post_id', 'user_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_post_votes');
        Schema::dropIfExists('forum_thread_votes');
        Schema::dropIfExists('forum_thread_question_tag');

        Schema::table('forum_posts', function (Blueprint $table) {
            $table->dropColumn('upvotes_count');
        });

        Schema::table('forum_threads', function (Blueprint $table) {
            $table->dropForeign(['accepted_answer_post_id']);
            $table->dropIndex('forum_threads_question_listing_index');
            $table->dropIndex('forum_threads_question_unanswered_index');
            $table->dropIndex('forum_threads_question_popular_index');
            $table->dropColumn(['type', 'upvotes_count', 'accepted_answer_post_id', 'accepted_answer_at']);
        });

        Schema::dropIfExists('question_tags');
    }
};
