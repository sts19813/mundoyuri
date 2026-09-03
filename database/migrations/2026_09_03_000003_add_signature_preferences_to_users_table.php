<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('signature_enabled')->default(true)->after('signature_image');
            $table->boolean('show_signatures')->default(true)->after('signature_enabled');
            $table->timestamp('signature_suspended_until')->nullable()->after('show_signatures');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'signature_enabled',
                'show_signatures',
                'signature_suspended_until',
            ]);
        });
    }
};
