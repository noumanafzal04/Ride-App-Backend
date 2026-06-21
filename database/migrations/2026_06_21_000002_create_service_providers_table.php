<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A user who offers car services. Created on registration with status
     * `pending`; an admin approves before they're listed / can take bookings.
     * Categories they offer live in the pivot.
     */
    public function up(): void
    {
        Schema::create('service_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete(); // one profile per user
            $table->string('business_name');
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->string('area')->nullable();          // service area / shop address
            $table->string('phone');
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // pending | approved | rejected | suspended
            $table->decimal('rating_avg', 3, 2)->nullable();
            $table->unsignedInteger('total_jobs')->default(0);
            $table->timestamps();

            $table->index('status');
            $table->index('city_id');
        });

        Schema::create('service_provider_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('service_categories')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['service_provider_id', 'category_id'], 'sp_category_unique');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_provider_categories');
        Schema::dropIfExists('service_providers');
    }
};
