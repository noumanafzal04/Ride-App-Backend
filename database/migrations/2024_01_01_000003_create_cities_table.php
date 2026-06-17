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
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name');          // 'Lahore'
            $table->string('province');      // 'Punjab'
            $table->decimal('lat', 10, 7);   // 31.5497000
            $table->decimal('lon', 10, 7);   // 74.3436000
            $table->timestamps();
            $table->index('name');           // fast search
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
