<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_plans', function (Blueprint $table) {
            $table->id();
            $table->string('module');                 // ride | service | buysell | rental
            $table->string('name');                   // Basic, Weekly, Monthly…
            $table->unsignedInteger('duration_days'); // 1, 7, 30…
            $table->unsignedInteger('post_limit');    // posts allowed within the window
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['module', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_plans');
    }
};
