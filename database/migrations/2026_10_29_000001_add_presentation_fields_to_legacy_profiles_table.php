<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('legacy_profiles', function (Blueprint $table): void {
            $table->text('legacy_biography')->nullable()->after('legacy_website');
            $table->text('legacy_signature_text')->nullable()->after('legacy_biography');
            $table->foreignId('legacy_soulmate_profile_id')
                ->nullable()
                ->after('legacy_signature_text')
                ->constrained('legacy_profiles')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('legacy_profiles', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('legacy_soulmate_profile_id');
            $table->dropColumn(['legacy_biography', 'legacy_signature_text']);
        });
    }
};
