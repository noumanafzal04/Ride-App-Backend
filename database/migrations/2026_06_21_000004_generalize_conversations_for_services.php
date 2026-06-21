<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Let conversations belong to either a ride booking or a service booking.
     * `type` distinguishes them; booking_id becomes nullable (null for service
     * chats). driver_id/rider_id stay as the two participants generically
     * (for service: provider user = driver_id, customer = rider_id).
     */
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->string('type')->default('ride')->after('id');
            $table->foreignId('service_booking_id')->nullable()->after('ride_post_id')
                ->constrained('service_bookings')->nullOnDelete();
        });

        // booking_id (ride) → nullable so service chats can omit it.
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['booking_id']);
        });
        Schema::table('conversations', function (Blueprint $table) {
            $table->unsignedBigInteger('booking_id')->nullable()->change();
        });
        Schema::table('conversations', function (Blueprint $table) {
            $table->foreign('booking_id')->references('id')->on('ride_bookings')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['service_booking_id']);
            $table->dropColumn(['type', 'service_booking_id']);
        });
    }
};
