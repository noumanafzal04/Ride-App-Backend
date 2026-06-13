<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('cnic_number', 20)->unique();
            $table->string('cnic_front_image');
            $table->string('cnic_back_image');
            $table->string('license_number', 50)->unique();
            $table->string('license_front_image');
            $table->string('license_back_image');
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedInteger('total_trips')->default(0);
            $table->decimal('total_earnings', 12, 2)->default(0);
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->boolean('is_online')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('verification_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_profiles');
    }
};
