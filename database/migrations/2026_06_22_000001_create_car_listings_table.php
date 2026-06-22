<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // self = seller manages it; managed = "Sell with EZRide" (consignment)
            $table->enum('listing_type', ['self', 'managed'])->default('self');
            // self listings go live instantly; managed start as pending review
            $table->enum('status', ['draft', 'pending', 'active', 'sold', 'rejected', 'inactive'])->default('active');

            // Car details
            $table->string('make');
            $table->string('model');
            $table->string('variant')->nullable();
            $table->unsignedSmallInteger('year');
            $table->decimal('price', 12, 2)->nullable();      // managed may be priced by EZRide later
            $table->unsignedInteger('mileage')->nullable();    // km
            $table->enum('condition', ['new', 'used'])->default('used');
            $table->enum('transmission', ['automatic', 'manual'])->nullable();
            $table->enum('fuel_type', ['petrol', 'diesel', 'hybrid', 'electric', 'cng'])->nullable();
            $table->unsignedInteger('engine_cc')->nullable();
            $table->string('color')->nullable();

            // Location
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->string('area')->nullable();

            $table->text('description')->nullable();
            $table->json('features')->nullable();

            // Optional link to an EZRide inspection → "Inspected" trust badge
            $table->foreignId('inspection_request_id')->nullable()->constrained('inspection_requests')->nullOnDelete();

            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'listing_type']);
            $table->index('city_id');
            $table->index('make');
            $table->index('price');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_listings');
    }
};
