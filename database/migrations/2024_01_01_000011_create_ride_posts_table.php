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
            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $table->string('from_city', 100);
            $table->string('to_city', 100);
            $table->text('from_address')->nullable();
            $table->text('to_address')->nullable();
            $table->decimal('from_latitude', 10, 8)->nullable();
            $table->decimal('from_longitude', 11, 8)->nullable();
            $table->decimal('to_latitude', 10, 8)->nullable();
            $table->decimal('to_longitude', 11, 8)->nullable();
            $table->dateTime('departure_at');
            $table->unsignedTinyInteger('available_seats');
            $table->decimal('price_per_seat', 10, 2);
            $table->boolean('luggage_allowed')->default(true);
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'full', 'completed', 'cancelled'])->default('active');
            $table->timestamps();

            $table->index(['from_city', 'to_city', 'departure_at', 'status']);
            $table->index('driver_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ride_posts');
    }
};
