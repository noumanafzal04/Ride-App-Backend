<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // No payment processing for now, but table exists so data/history is preserved.
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('trips')->cascadeOnDelete();
            $table->foreignId('payer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->restrictOnDelete();
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'jazzcash', 'easypaisa', 'card'])->default('cash');
            $table->string('transaction_ref', 120)->nullable();
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
