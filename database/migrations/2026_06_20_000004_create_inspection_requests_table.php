<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A car-inspection lead. Anyone (guest or logged-in) can submit; the team
     * reviews, schedules, inspects, and delivers a report. Logged-in requests
     * carry user_id so the requester can track status + get notifications.
     * The 200-point structured report lives in separate tables (Phase 2).
     */
    public function up(): void
    {
        Schema::create('inspection_requests', function (Blueprint $table) {
            $table->id();
            // null = guest submission (reached only by phone).
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Requester contact (captured even for guests).
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();

            // Car under inspection.
            $table->string('car_make');
            $table->string('car_model');
            $table->unsignedSmallInteger('car_year')->nullable();
            $table->string('variant')->nullable();
            $table->string('registration_no')->nullable();

            // Where the car is, and when the requester wants it inspected.
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->text('address')->nullable();
            $table->dateTime('preferred_at')->nullable();
            $table->text('notes')->nullable();

            // Workflow. String (not enum) so statuses extend without a migration.
            // pending → reviewing → scheduled → in_progress → completed | cancelled
            $table->string('status')->default('pending');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete(); // inspector/team member
            $table->dateTime('scheduled_at')->nullable();

            // Report summary (full per-point results in Phase 2 tables).
            $table->string('overall_grade')->nullable();        // e.g. A / B / C
            $table->decimal('overall_score', 5, 2)->nullable();  // e.g. 78.50 (%)
            $table->text('inspector_comments')->nullable();      // shown to requester
            $table->text('admin_notes')->nullable();             // internal only
            $table->dateTime('completed_at')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_requests');
    }
};
