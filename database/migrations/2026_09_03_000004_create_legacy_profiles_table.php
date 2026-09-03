<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legacy_profiles', function (Blueprint $table): void {
            $table->id();
            $table->string('legacy_external_key', 191)->unique();
            $table->string('slug', 150)->unique();
            $table->string('nickname', 120);
            $table->timestamp('legacy_joined_at')->nullable();
            $table->string('legacy_rank', 120)->nullable();
            $table->unsignedInteger('legacy_message_count')->default(0);
            $table->string('legacy_location', 120)->nullable();
            $table->string('legacy_occupation', 160)->nullable();
            $table->text('legacy_interests')->nullable();
            $table->string('legacy_website', 2048)->nullable();
            $table->string('legacy_avatar_path')->nullable();
            $table->string('source', 255);
            $table->text('evidence')->nullable();
            $table->text('admin_notes')->nullable();
            $table->enum('claim_status', ['unclaimed', 'pending', 'claimed', 'rejected'])->default('unclaimed');
            $table->foreignId('claimed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('claimed_at')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['is_published', 'legacy_joined_at'], 'legacy_profiles_public_index');
            $table->index(['claim_status', 'claimed_at'], 'legacy_profiles_claim_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_profiles');
    }
};
