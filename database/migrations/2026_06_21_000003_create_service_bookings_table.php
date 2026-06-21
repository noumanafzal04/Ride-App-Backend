<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A customer's service request to a provider.
     * Lifecycle: requested → accepted → in_progress → completed
     *            (or rejected / cancelled).
     */
    public function up(): void
    {
        Schema::create('service_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('service_providers')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('service_categories')->nullOnDelete();

            $table->dateTime('scheduled_at')->nullable();
            $table->string('location_type')->default('at_shop'); // at_shop | at_home
            $table->text('address')->nullable();
            $table->string('car_info')->nullable();              // e.g. "Toyota Corolla 2018"
            $table->text('notes')->nullable();

            $table->string('status')->default('requested');      // requested|accepted|in_progress|completed|cancelled|rejected
            $table->decimal('price', 10, 2)->nullable();         // optional quote set by provider
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->index('customer_id');
            $table->index(['provider_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_bookings');
    }
};
