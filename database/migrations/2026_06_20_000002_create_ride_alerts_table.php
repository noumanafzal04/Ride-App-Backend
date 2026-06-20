<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A rider's "notify me" subscription for a route (+ optional date). When a
     * driver posts a matching ride, we notify every active alert's owner.
     */
    public function up(): void
    {
        Schema::create('ride_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_city_id')->constrained('cities')->cascadeOnDelete();
            $table->foreignId('to_city_id')->constrained('cities')->cascadeOnDelete();
            $table->date('alert_date')->nullable();          // null = any date
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_notified_at')->nullable();
            $table->timestamps();

            // Hot path: on post-create, find active alerts for a route fast.
            $table->index(['from_city_id', 'to_city_id', 'is_active'], 'ride_alerts_match_idx');
            // A rider's own alerts list.
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ride_alerts');
    }
};
