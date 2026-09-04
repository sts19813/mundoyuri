<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forum_threads', function (Blueprint $table): void {
            $table->string('author_name_snapshot', 120)->nullable()->after('user_id');
        });

        Schema::table('forum_posts', function (Blueprint $table): void {
            $table->string('author_name_snapshot', 120)->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('forum_posts', function (Blueprint $table): void {
            $table->dropColumn('author_name_snapshot');
        });

        Schema::table('forum_threads', function (Blueprint $table): void {
            $table->dropColumn('author_name_snapshot');
        });
    }
};
