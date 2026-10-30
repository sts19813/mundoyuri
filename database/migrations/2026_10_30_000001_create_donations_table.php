<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('show_star')->default(false);
            $table->unsignedInteger('amount');
            $table->string('currency', 3)->default('mxn');
            $table->string('status')->default('started')->index();
            $table->string('stripe_payment_intent_id')->nullable()->unique();
            $table->string('stripe_charge_id')->nullable();
            $table->unsignedInteger('stripe_fee')->nullable();
            $table->integer('stripe_net')->nullable();
            $table->string('balance_currency', 3)->nullable();
            $table->unsignedInteger('refunded_amount')->default(0);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('donations'); }
};
