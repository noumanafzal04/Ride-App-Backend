<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_listing_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_listing_id')->constrained('car_listings')->cascadeOnDelete();
            $table->string('path');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index('car_listing_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_listing_images');
    }
};
