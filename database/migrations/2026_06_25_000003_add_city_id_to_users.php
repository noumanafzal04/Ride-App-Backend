<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Last-known city (set when the app resolves the nearest city) —
            // used for targeted admin broadcasts.
            $table->foreignId('city_id')->nullable()->after('status')->constrained('cities')->nullOnDelete();
            $table->index('city_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('city_id');
        });
    }
};
