<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ride_posts', function (Blueprint $table) {
            // Private posts book the whole vehicle, so seats are not tracked → allow null
            $table->unsignedTinyInteger('available_seats')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ride_posts', function (Blueprint $table) {
            $table->unsignedTinyInteger('available_seats')->nullable(false)->change();
        });
    }
};
