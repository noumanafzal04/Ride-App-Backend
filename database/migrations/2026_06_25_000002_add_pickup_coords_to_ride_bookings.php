<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ride_bookings', function (Blueprint $table) {
            // Rider's pickup point captured at booking time → driver sees the
            // distance from the ride origin on the offer card.
            $table->decimal('pickup_lat', 10, 7)->nullable()->after('note');
            $table->decimal('pickup_lng', 10, 7)->nullable()->after('pickup_lat');
        });
    }

    public function down(): void
    {
        Schema::table('ride_bookings', function (Blueprint $table) {
            $table->dropColumn(['pickup_lat', 'pickup_lng']);
        });
    }
};
