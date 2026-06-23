<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_car_id')->constrained('rental_cars')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('owner_id'); // snapshot of the car owner

            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedSmallInteger('days')->default(1);
            $table->boolean('with_driver')->default(true);
            $table->string('pickup_location')->nullable();
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->decimal('deposit', 10, 2)->nullable();
            $table->enum('status', ['requested', 'confirmed', 'active', 'completed', 'cancelled', 'rejected'])->default('requested');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index(['owner_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_bookings');
    }
};
