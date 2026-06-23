<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_car_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_car_id')->constrained('rental_cars')->cascadeOnDelete();
            $table->string('path');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->index('rental_car_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_car_images');
    }
};
