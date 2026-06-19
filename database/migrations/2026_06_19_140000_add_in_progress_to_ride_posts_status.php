<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add 'in_progress' so a driver can start a ride (lock cancellations)
     * before ending it. Order: active → full → in_progress → completed.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE ride_posts MODIFY COLUMN status ENUM('active','full','in_progress','completed','cancelled') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        DB::statement("UPDATE ride_posts SET status = 'full' WHERE status = 'in_progress'");
        DB::statement("ALTER TABLE ride_posts MODIFY COLUMN status ENUM('active','full','completed','cancelled') NOT NULL DEFAULT 'active'");
    }
};
