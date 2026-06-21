<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rider↔driver chat, scoped to an accepted booking. One conversation per
     * booking; opened on accept, closed when the ride completes/cancels, and
     * purged 30 days after closing (chat:purge-closed) to keep the DB lean.
     * Per-side unread counters make the inbox + footer badge a single query.
     */
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained('ride_bookings')->cascadeOnDelete();
            $table->foreignId('ride_post_id')->nullable()->constrained('ride_posts')->nullOnDelete();
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
