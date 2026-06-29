<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Generic 2-party chat. Originally rider↔driver scoped to a ride booking;
     * now also carries service / marketplace / rental chats via `type` + the
     * matching nullable context FK (driver_id/rider_id are the two participants
     * generically — e.g. provider=driver_id, customer=rider_id for services).
     * Opened on accept, closed when the context completes/cancels, purged 30 days
     * after (chat:purge-closed). Per-side unread counters keep the badge 1 query.
     */
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('ride'); // ride | service | marketplace | rental
            // Context — exactly one is set depending on `type` (all nullable).
            $table->foreignId('booking_id')->nullable()->unique()->constrained('ride_bookings')->cascadeOnDelete();
            $table->foreignId('ride_post_id')->nullable()->constrained('ride_posts')->nullOnDelete();
            $table->foreignId('service_booking_id')->nullable()->constrained('service_bookings')->nullOnDelete();
            $table->foreignId('car_listing_id')->nullable()->constrained('car_listings')->nullOnDelete();
            $table->foreignId('rental_booking_id')->nullable()->constrained('rental_bookings')->nullOnDelete();
            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('rider_id')->constrained('users')->cascadeOnDelete();

            $table->string('status')->default('open');           // open | closed
            $table->string('last_message_preview', 160)->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->unsignedInteger('driver_unread')->default(0);
            $table->unsignedInteger('rider_unread')->default(0);
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index('driver_id');
            $table->index('rider_id');
            $table->index(['status', 'closed_at']); // purge scan
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'id']); // thread paging, newest-first
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
    }
};
