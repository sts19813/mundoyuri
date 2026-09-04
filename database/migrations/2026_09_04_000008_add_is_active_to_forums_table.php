<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forums', function (Blueprint $table): void {
            $table->boolean('is_active')->default(true)->after('sort_order');
            $table->index(['is_active', 'forum_category_id', 'sort_order'], 'forums_public_listing_index');
        });
    }

    public function down(): void
    {
        Schema::table('forums', function (Blueprint $table): void {
            $table->dropIndex('forums_public_listing_index');
            $table->dropColumn('is_active');
        });
    }
};
