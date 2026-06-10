<?php

use App\Enums\UserType\UserType;
use App\Enums\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('user_type', UserType::values())
                ->default(UserType::USER->value)
                ->after('phone_number');

            $table->enum('status', Status::values())
                ->default(Status::ACTIVE->value)
                ->after('user_type');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['user_type', 'status']);
        });
    }
};
