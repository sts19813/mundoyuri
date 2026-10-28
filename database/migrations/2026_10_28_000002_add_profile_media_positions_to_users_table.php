<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedTinyInteger('profile_image_position_x')->default(50)->after('profile_image');
            $table->unsignedTinyInteger('profile_image_position_y')->default(50)->after('profile_image_position_x');
            $table->unsignedTinyInteger('cover_image_position_x')->default(50)->after('cover_image');
            $table->unsignedTinyInteger('cover_image_position_y')->default(50)->after('cover_image_position_x');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'profile_image_position_x',
                'profile_image_position_y',
                'cover_image_position_x',
                'cover_image_position_y',
            ]);
        });
    }
};
