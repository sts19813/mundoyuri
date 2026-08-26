<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('episode_email_notifications_enabled')
                ->default(true)
                ->after('is_active');
        });

        // Las cuentas creadas antes de esta preferencia comienzan suscritas.
        DB::table('users')->update(['episode_email_notifications_enabled' => true]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('episode_email_notifications_enabled');
        });
    }
};
