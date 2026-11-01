<?php

use App\Models\Badge;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Badge::query()->firstOrCreate(['slug' => 'halloween-yuri-2026'], [
            'name' => 'Halloween Yuri 2026',
            'description' => 'Se conectó a Mundo Yuri el 31 de octubre de 2026.',
            'icon' => '🎃',
            'type' => 'event',
            'priority' => 25,
            'color' => '#f97316',
            'is_active' => true,
        ]);
    }

    public function down(): void
    {
        // Preserve awards if the migration is rolled back.
    }
};
