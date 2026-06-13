<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('model_id')->constrained('vehicle_models')->restrictOnDelete();
            $table->string('vehicle_image_path');
            $table->smallInteger('manufacture_year');
            $table->string('color', 40);
            $table->string('registration_number', 30)->unique();
            $table->unsignedTinyInteger('seating_capacity');
            $table->unsignedTinyInteger('luggage_capacity')->nullable();
            $table->boolean('has_air_conditioner')->default(false);
            $table->enum('status', ['active', 'inactive', 'pending_verification'])->default('pending_verification');
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
