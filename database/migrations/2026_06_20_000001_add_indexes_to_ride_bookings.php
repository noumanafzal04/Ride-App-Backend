<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Booking lists always filter by passenger+status (rider screens) or
     * ride_post+status (driver screens / settle / auto-reject). Index both so
     * these stay fast as the table grows to millions of rows.
     */
    public function up(): void
    {
        Schema::table('ride_bookings', function (Blueprint $table) {
            $table->index(['passenger_id', 'status'], 'ride_bookings_passenger_status_idx');
            $table->index(['ride_post_id', 'status'], 'ride_bookings_post_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('ride_bookings', function (Blueprint $table) {
            $table->dropIndex('ride_bookings_passenger_status_idx');
            $table->dropIndex('ride_bookings_post_status_idx');
        });
    }
};
