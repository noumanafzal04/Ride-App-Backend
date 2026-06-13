<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);                         // Free, 1 Day, 7 Days, 30 Days
            $table->string('slug', 80)->unique();               // free, day_1, day_7, day_30
            $table->unsignedSmallInteger('duration_days')->default(0); // 0 = free/unlimited time
            $table->decimal('price', 10, 2)->default(0);        // 0 for now (free), set later
            $table->integer('post_limit')->default(0);          // posts allowed; -1 = unlimited
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
