<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('module');
            $table->foreignId('plan_id')->nullable()->constrained('billing_plans')->nullOnDelete();
            $table->unsignedInteger('posts_allowed');
            $table->unsignedInteger('posts_used')->default(0);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('status')->default('active');  // active | expired | cancelled
            $table->string('source')->default('self');     // self | admin
            $table->decimal('price_paid', 10, 2)->default(0);
            $table->timestamps();

            $table->index(['user_id', 'module', 'status']);
            $table->index('ends_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_subscriptions');
    }
};
