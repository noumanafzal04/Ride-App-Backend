<?php

use App\Enums\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->unique()  // one driver_profile per user
                ->constrained('users')
                ->cascadeOnDelete();

            // Identity Verification
            $table->string('cnic_number', 15)->unique()->nullable();
            $table->string('cnic_front_image')->nullable();
            $table->string('cnic_back_image')->nullable();
            $table->string('driving_license_no')->unique()->nullable();
            $table->string('license_front_image')->nullable();
            $table->string('license_back_image')->nullable();
            $table->date('license_expiry_date')->nullable();

            // App Performance
            $table->decimal('rating', 3, 2)->default(0.00); // e.g. 4.85
            $table->unsignedInteger('total_trips')->default(0);
            $table->decimal('total_earnings', 12, 2)->default(0.00);

            // Verification & Status
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])
                ->default('pending');
            $table->enum('status', Status::values())
                ->default(Status::ACTIVE->value);
            $table->boolean('online_status')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_profiles');
    }
};
