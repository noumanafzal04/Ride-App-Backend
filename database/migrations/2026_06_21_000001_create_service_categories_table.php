<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Car-service categories (Mechanic, Car Wash, AC, …). Seeded below; admins
     * can add more later. Drives provider registration + customer browsing.
     */
    public function up(): void
    {
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();           // MaterialCommunityIcons name (mobile)
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        $cats = [
            ['General Mechanic',            'wrench'],
            ['Car Wash & Detailing',        'car-wash'],
            ['AC Service & Repair',         'air-conditioner'],
            ['Denting & Painting',          'format-paint'],
            ['Tyres & Wheels',              'tire'],
            ['Battery Service',             'car-battery'],
            ['Oil Change & Tuning',         'oil'],
            ['Auto Electrician',            'flash-outline'],
            ['Towing & Roadside Assistance','tow-truck'],
            ['Windscreen & Glass',          'car-windshield-outline'],
        ];

        $rows = [];
        foreach ($cats as $i => [$name, $icon]) {
            $rows[] = [
                'name'       => $name,
                'slug'       => Str::slug($name),
                'icon'       => $icon,
                'sort'       => $i + 1,
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('service_categories')->insertOrIgnore($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('service_categories');
    }
};
