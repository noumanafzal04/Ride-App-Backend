<?php

use App\Enums\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users_location', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('address')->nullable();
            $table->timestamp('located_at');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users_location');
    }
};
