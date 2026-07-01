<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Expiry for a paid "feature" boost (null = manual/admin feature, no expiry).
        Schema::table('car_listings', function (Blueprint $table) {
            $table->timestamp('featured_until')->nullable()->after('is_featured');
        });
        Schema::table('rental_cars', function (Blueprint $table) {
            $table->timestamp('featured_until')->nullable()->after('is_featured');
        });

        // Admin-editable price + duration for featuring, per module.
        Schema::create('feature_settings', function (Blueprint $table) {
            $table->id();
            $table->string('module')->unique();          // buysell | rental
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedSmallInteger('duration_days')->default(7);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // One row per "feature" purchase — audit + future gateway hook-in.
        Schema::create('feature_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('orderable_type');
            $table->unsignedBigInteger('orderable_id');
            $table->string('module');                    // buysell | rental
            $table->unsignedSmallInteger('days');
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('paid');   // paid (marked instantly for now)
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['orderable_type', 'orderable_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_orders');
        Schema::dropIfExists('feature_settings');
        Schema::table('rental_cars', fn (Blueprint $t) => $t->dropColumn('featured_until'));
        Schema::table('car_listings', fn (Blueprint $t) => $t->dropColumn('featured_until'));
    }
};
