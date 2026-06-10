<?php

use App\Enums\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Vehicle Details
            $table->enum('vehicle_type', ['car', 'suv', 'van', 'bike']);
            $table->string('make');           // Toyota, Honda
            $table->string('model');          // Corolla, Civic
            $table->unsignedSmallInteger('manufacturing_year');
            $table->string('color');
            $table->string('registration_number')->unique();
            $table->unsignedTinyInteger('seating_capacity');
            $table->unsignedTinyInteger('luggage_capacity')->default(0);

            // Documents & Photos
            $table->string('vehicle_front_image')->nullable();
            $table->string('vehicle_back_image')->nullable();
            $table->string('vehicle_side_image')->nullable();

            // Features & Status
            $table->boolean('air_conditioned')->default(false);
            $table->boolean('wifi_available')->default(false);
            $table->enum('status', Status::values())
                ->default(Status::ACTIVE->value);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
