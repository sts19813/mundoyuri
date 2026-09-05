<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forum_posts', function (Blueprint $table): void {
            $table->foreignId('reply_to_post_id')->nullable()->constrained('forum_posts')->nullOnDelete();
        });
        Schema::table('comments', function (Blueprint $table): void {
            $table->foreignId('reply_to_comment_id')->nullable()->constrained('comments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('comments', fn (Blueprint $table) => $table->dropConstrainedForeignId('reply_to_comment_id'));
        Schema::table('forum_posts', fn (Blueprint $table) => $table->dropConstrainedForeignId('reply_to_post_id'));
    }
};
