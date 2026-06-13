<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            // exactly one source: a booking (Flow A) OR an offer (Flow B)
            $table->foreignId('ride_booking_id')->nullable()->constrained('ride_bookings')->nullOnDelete();
            $table->foreignId('ride_offer_id')->nullable()->constrained('ride_offers')->nullOnDelete();
            $table->foreignId('driver_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('passenger_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $table->unsignedTinyInteger('seats')->default(1);
            $table->decimal('final_amount', 10, 2);
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->enum('status', ['upcoming', 'ongoing', 'completed', 'cancelled'])->default('upcoming');
            $table->timestamps();

            $table->index('driver_id');
            $table->index('passenger_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
