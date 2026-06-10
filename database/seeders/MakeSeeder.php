<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MakeSeeder extends Seeder
{
    public function run(): void
    {
        $makes = [
            'Toyota',
            'Honda',
            'Suzuki',
            'Hyundai',
            'Kia',
            'Changan',
            'Nissan',
            'Mitsubishi',
            'Mazda',
            'Daihatsu',
            'FAW',
            'DFSK',
            'MG',
            'BMW',
            'Mercedes-Benz',
            'Audi',
            'Volkswagen',
            'Chevrolet',
            'Ford',
            'Peugeot',
            'Renault',
            'Haval',
            'Chery',
            'Jetour',
            'Lexus',
            'Land Rover',
            'Jeep',
            'Subaru',
            'Skoda',
            'Volvo',
            'Tesla'
        ];

        foreach ($makes as $make) {
            DB::table('makes')->insert([
                'name' => $make,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
