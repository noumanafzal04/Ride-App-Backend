<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Global admin activity feed (new driver to verify, new inspection request,
     * new provider awaiting approval, …). Per-admin read state is tracked by
     * admin_users.notifications_read_at (unread = created after that time).
     */
    public function up(): void
    {
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('title');
            $table->string('message');
            $table->json('data')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });

        Schema::table('admin_users', function (Blueprint $table) {
            $table->timestamp('notifications_read_at')->nullable()->after('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
        Schema::table('admin_users', function (Blueprint $table) {
            $table->dropColumn('notifications_read_at');
        });
    }
};
