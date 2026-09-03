<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->enum('profile_visibility', ['public', 'members', 'private'])->default('public');
            $table->boolean('show_last_seen')->default(false);
            $table->boolean('show_join_date')->default(true);
            $table->boolean('show_favorites')->default(true);
            $table->boolean('show_activity')->default(true);
            $table->text('signature_text')->nullable();
            $table->string('signature_image')->nullable();
            $table->string('location', 120)->nullable();
            $table->string('website', 2048)->nullable();
            $table->string('occupation', 160)->nullable();
            $table->text('interests')->nullable();
            $table->unsignedInteger('community_message_count')->default(0);
            $table->integer('community_reputation')->default(0);
            $table->foreignId('community_rank_id')->nullable()->constrained('community_ranks')->nullOnDelete();
            $table->boolean('is_legacy')->default(false);
            $table->timestamp('legacy_joined_at')->nullable();
            $table->string('legacy_source')->nullable();
            $table->text('legacy_notes')->nullable();
            $table->boolean('legacy_verified')->default(false);
            $table->timestamp('profile_claimed_at')->nullable();

            $table->index(['is_active', 'profile_visibility', 'created_at'], 'users_community_directory_index');
            $table->index(['is_legacy', 'legacy_joined_at'], 'users_legacy_directory_index');
            $table->index('community_message_count', 'users_community_messages_index');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_community_directory_index');
            $table->dropIndex('users_legacy_directory_index');
            $table->dropIndex('users_community_messages_index');
            $table->dropConstrainedForeignId('community_rank_id');
            $table->dropColumn([
                'profile_visibility',
                'show_last_seen',
                'show_join_date',
                'show_favorites',
                'show_activity',
                'signature_text',
                'signature_image',
                'location',
                'website',
                'occupation',
                'interests',
                'community_message_count',
                'community_reputation',
                'is_legacy',
                'legacy_joined_at',
                'legacy_source',
                'legacy_notes',
                'legacy_verified',
                'profile_claimed_at',
            ]);
        });
    }
};
