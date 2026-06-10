<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModelSeeder extends Seeder
{
    public function run(): void
    {
        $makes = DB::table('makes')->pluck('id', 'name');

        $models = [
            'Toyota' => ['Corolla', 'Yaris', 'Aqua', 'Vitz', 'Prado', 'Fortuner', 'Hilux', 'Camry'],
            'Honda' => ['Civic', 'City', 'Accord', 'BR-V', 'HR-V', 'CR-V'],
            'Suzuki' => ['Alto', 'Cultus', 'Wagon R', 'Swift', 'Bolan', 'Mehran'],
            'Hyundai' => ['Elantra', 'Tucson', 'Sonata', 'Santa Fe', 'Porter'],
            'Kia' => ['Picanto', 'Sportage', 'Stonic', 'Sorento', 'Carnival'],
            'Changan' => ['Alsvin', 'Oshan X7', 'Karvaan'],
            'Nissan' => ['Dayz', 'Note', 'Sunny', 'X-Trail', 'Juke'],
            'BMW' => ['3 Series', '5 Series', '7 Series', 'X1', 'X3', 'X5'],
            'Mercedes-Benz' => ['C-Class', 'E-Class', 'S-Class', 'GLA', 'GLC', 'GLE'],
            'Audi' => ['A3', 'A4', 'A6', 'Q2', 'Q3', 'Q5']
        ];

        foreach ($models as $makeName => $modelList) {

            if (!isset($makes[$makeName])) {
                continue;
            }

            $makeId = $makes[$makeName];

            foreach ($modelList as $model) {
                DB::table('models')->insert([
                    'make_id' => $makeId,
                    'name' => $model,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
