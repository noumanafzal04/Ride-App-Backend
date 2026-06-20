<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Category-level inspection report (the simple version of the report).
     * - inspection_categories: fixed catalog (seeded below).
     * - inspection_category_results: one row per (request, category) with a
     *   condition + optional note. Overall score/grade is computed from these.
     * Designed so granular per-point checks can later hang under a category
     * without reworking these tables.
     */
    public function up(): void
    {
        Schema::create('inspection_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('inspection_category_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('inspection_categories')->cascadeOnDelete();
            $table->string('condition');        // excellent | good | fair | poor | na
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['inspection_request_id', 'category_id'], 'insp_cat_result_unique');
        });

        // Seed the fixed catalog (idempotent on slug).
        $now = now();
        $cats = [
            'Engine', 'Transmission & Clutch', 'Brakes & Suspension', 'Steering',
            'Exterior & Body', 'Interior', 'Electrical & Electronics',
            'AC / Heating', 'Tyres & Wheels', 'Test Drive',
        ];
        $rows = [];
        foreach ($cats as $i => $name) {
            $rows[] = [
                'name'       => $name,
                'slug'       => \Illuminate\Support\Str::slug($name),
                'sort'       => $i + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('inspection_categories')->insertOrIgnore($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_category_results');
        Schema::dropIfExists('inspection_categories');
    }
};
