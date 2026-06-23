<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_billing_settings', function (Blueprint $table) {
            $table->id();
            $table->string('module')->unique();      // ride | service | buysell | rental
            $table->string('free_mode');             // active_cap | category_cap | intro_credit
            $table->unsignedInteger('free_limit')->default(0);
            $table->boolean('enforcement_enabled')->default(false); // OFF at launch → everyone free
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_billing_settings');
    }
};
