<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('direct_messages', function (Blueprint $table): void {
            $table->timestamp('deleted_at')->nullable()->after('read_at')->index();
            $table->foreignId('deleted_by')->nullable()->after('deleted_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('direct_messages', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('deleted_by');
            $table->dropColumn('deleted_at');
        });
    }
};
