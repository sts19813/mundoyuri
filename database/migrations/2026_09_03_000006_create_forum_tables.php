<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 150)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 80)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('forums', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_category_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('slug', 150)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 80)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_locked')->default(false);
            $table->string('minimum_role', 40)->nullable();
            $table->timestamps();
            $table->index(['forum_category_id', 'sort_order']);
        });

        Schema::create('forum_threads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('author_name_snapshot', 120)->nullable();
            $table->string('title', 180);
            $table->string('slug', 190);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedInteger('replies_count')->default(0);
            $table->timestamp('last_post_at')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->unique('slug');
            $table->index(['forum_id', 'is_hidden', 'is_pinned', 'last_post_at'], 'forum_threads_listing_index');
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('forum_posts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_thread_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('author_name_snapshot', 120)->nullable();
            $table->text('body');
            $table->timestamp('edited_at')->nullable();
            $table->boolean('is_initial')->default(false);
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['forum_thread_id', 'is_hidden', 'created_at'], 'forum_posts_thread_index');
            $table->index(['user_id', 'is_hidden', 'created_at'], 'forum_posts_user_index');
        });

        Schema::create('forum_thread_subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_thread_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['forum_thread_id', 'user_id']);
        });

        Schema::create('forum_mentions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mentioned_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mentioner_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['forum_post_id', 'mentioned_user_id']);
            $table->index(['mentioned_user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_mentions');
        Schema::dropIfExists('forum_thread_subscriptions');
        Schema::dropIfExists('forum_posts');
        Schema::dropIfExists('forum_threads');
        Schema::dropIfExists('forums');
        Schema::dropIfExists('forum_categories');
    }
};
