<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ride_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('from_city_id')
                ->constrained('cities')
                ->restrictOnDelete();

            $table->foreignId('to_city_id')
                ->constrained('cities')
                ->restrictOnDelete();

            $table->text('from_address')->nullable();
            $table->text('to_address')->nullable();

            $table->decimal('from_latitude', 10, 7)->nullable();
            $table->decimal('from_longitude', 10, 7)->nullable();
            $table->decimal('to_latitude', 10, 7)->nullable();
            $table->decimal('to_longitude', 10, 7)->nullable();

            $table->dateTime('departure_at');
            $table->unsignedTinyInteger('available_seats');
            $table->decimal('price_per_seat', 10, 2);
            $table->boolean('luggage_allowed')->default(true);
            $table->text('notes')->nullable();

            $table->enum('post_type', ['private', 'shared'])->default('shared');
            $table->enum('status', ['active', 'full', 'completed', 'cancelled'])->default('active');

            $table->timestamps();

            $table->index(['from_city_id', 'to_city_id', 'departure_at', 'status']);
            $table->index('driver_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ride_posts');
    }
};
