<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Generic, typed ratings — reusable across modules (ride, service, rent, sell…).
     * `type` = module category, `rateable` (morphs) = the exact record being rated.
     */
    public function up(): void
    {
        Schema::dropIfExists('ratings');

        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30)->index();           // 'ride' | future: 'service','rent','sell'
            $table->morphs('rateable');                     // rateable_type + rateable_id
            $table->foreignId('from_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('to_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('rated_as', 30);                 // ratee role: 'driver' | 'passenger'
            $table->unsignedTinyInteger('rating');          // 1–5
            $table->text('review')->nullable();
            $table->timestamps();

            // one review per rater per rated record
            $table->unique(['rateable_type', 'rateable_id', 'from_user_id'], 'ratings_rater_unique');
            $table->index(['to_user_id', 'type']);          // fast per-user aggregates
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');

        // restore the original trip-scoped table
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('trips')->cascadeOnDelete();
            $table->foreignId('from_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('to_user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('rated_as', ['driver', 'passenger']);
            $table->unsignedTinyInteger('rating');
            $table->text('review')->nullable();
            $table->timestamps();
            $table->unique(['trip_id', 'from_user_id', 'to_user_id']);
        });
    }
};
