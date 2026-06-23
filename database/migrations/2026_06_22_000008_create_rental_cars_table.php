<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('listing_type', ['self', 'managed'])->default('self');
            $table->enum('status', ['pending', 'active', 'paused', 'rejected', 'inactive'])->default('active');

            $table->string('make');
            $table->string('model');
            $table->string('variant')->nullable();
            $table->unsignedSmallInteger('year');
            $table->enum('category', ['economy', 'sedan', 'suv', 'luxury', 'van'])->nullable();
            $table->unsignedTinyInteger('seats')->nullable();
            $table->enum('transmission', ['automatic', 'manual'])->nullable();
            $table->enum('fuel_type', ['petrol', 'diesel', 'hybrid', 'electric', 'cng'])->nullable();
            $table->string('color')->nullable();

            // with_driver dominates the local market; self_drive needs deposit.
            $table->enum('rental_type', ['with_driver', 'self_drive', 'both'])->default('with_driver');
            $table->decimal('price_per_day', 10, 2)->nullable();       // with driver
            $table->decimal('price_per_day_self', 10, 2)->nullable();  // self-drive
            $table->decimal('deposit', 10, 2)->nullable();
            $table->unsignedSmallInteger('min_days')->default(1);

            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->string('area')->nullable();
            $table->text('description')->nullable();
            $table->json('features')->nullable();

            $table->foreignId('inspection_request_id')->nullable()->constrained('inspection_requests')->nullOnDelete();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();

            $table->index(['status', 'listing_type']);
            $table->index('city_id');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_cars');
    }
};
