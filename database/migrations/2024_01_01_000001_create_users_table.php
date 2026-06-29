<?php

use App\Enums\Status;
use App\Enums\UserType\UserType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->string('phone_number', 20)->unique();
            $table->string('email', 150)->unique()->nullable();
            $table->string('password');
            $table->enum('user_type', UserType::userDriver())->default(UserType::USER);
            $table->enum('status', Status::activeInactive())->default(Status::ACTIVE);
            // Team members who can review/manage inspection requests.
            $table->boolean('is_admin')->default(false);
            // Last-known city (set when the app resolves the nearest city) — for targeted admin broadcasts.
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('user_type');
            $table->index('status');
            $table->index('city_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
