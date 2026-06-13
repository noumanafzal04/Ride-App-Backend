<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ride_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ride_post_id')->constrained('ride_posts')->cascadeOnDelete();
            $table->foreignId('passenger_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('seats_booked');
            $table->decimal('price_per_seat', 10, 2);   // snapshot at booking time
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['pending', 'accepted', 'rejected', 'cancelled', 'completed'])->default('pending');
            $table->timestamps();

            $table->unique(['ride_post_id', 'passenger_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ride_bookings');
    }
};
