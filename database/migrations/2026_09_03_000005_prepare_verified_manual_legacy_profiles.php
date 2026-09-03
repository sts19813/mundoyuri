<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('legacy_profiles', function (Blueprint $table): void {
            $table->unsignedInteger('legacy_message_count')->nullable()->change();
            $table->boolean('is_legacy')->default(true)->after('id');
            $table->boolean('legacy_verified')->default(false)->after('is_legacy');
            $table->string('legacy_avatar_url', 2048)->nullable()->after('legacy_avatar_path');
            $table->string('legacy_source_url', 2048)->nullable()->after('source');
            $table->text('legacy_source_description')->nullable()->after('legacy_source_url');

            $table->index(['is_legacy', 'legacy_verified'], 'legacy_profiles_verification_index');
        });
    }

    public function down(): void
    {
        DB::table('legacy_profiles')
            ->whereNull('legacy_message_count')
            ->update(['legacy_message_count' => 0]);

        Schema::table('legacy_profiles', function (Blueprint $table): void {
            $table->dropIndex('legacy_profiles_verification_index');
            $table->dropColumn([
                'is_legacy',
                'legacy_verified',
                'legacy_avatar_url',
                'legacy_source_url',
                'legacy_source_description',
            ]);
            $table->unsignedInteger('legacy_message_count')->default(0)->change();
        });
    }
};
