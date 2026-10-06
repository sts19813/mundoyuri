<?php

use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('last_seen_at')->nullable()->after('last_login_at');
        });

        DB::table('users')
            ->whereNotNull('last_login_at')
            ->update(['last_seen_at' => DB::raw('last_login_at')]);

        if (! Schema::hasTable('sessions')) {
            return;
        }

        DB::table('sessions')
            ->whereNotNull('user_id')
            ->select('user_id')
            ->selectRaw('MAX(last_activity) as last_activity')
            ->groupBy('user_id')
            ->orderBy('user_id')
            ->get()
            ->each(function (object $session): void {
                DB::table('users')
                    ->where('id', $session->user_id)
                    ->update([
                        'last_seen_at' => CarbonImmutable::createFromTimestamp(
                            (int) $session->last_activity,
                            config('app.timezone')
                        )->format('Y-m-d H:i:s'),
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('last_seen_at');
        });
    }
};
