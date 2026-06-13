<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ride_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('passenger_id')->constrained('users')->cascadeOnDelete();
            $table->string('from_city', 100);
            $table->string('to_city', 100);
            $table->text('from_address')->nullable();
            $table->text('to_address')->nullable();
            $table->decimal('from_latitude', 10, 8)->nullable();
            $table->decimal('from_longitude', 11, 8)->nullable();
            $table->decimal('to_latitude', 10, 8)->nullable();
            $table->decimal('to_longitude', 11, 8)->nullable();
            $table->date('travel_date');
            $table->unsignedTinyInteger('required_seats');
            $table->decimal('budget', 10, 2);
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'matched', 'completed', 'cancelled'])->default('active');
            $table->timestamps();

            $table->index(['from_city', 'to_city', 'travel_date', 'status']);
            $table->index('passenger_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ride_requests');
    }
};
