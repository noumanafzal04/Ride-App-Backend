<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleMakeModelSeeder extends Seeder
{
    /**
     * Pakistani-market car makes and their common models.
     * Structure: 'Make' => ['Model', 'Model', ...]
     * On selecting a make in the app, return that make's models.
     */
    public function run(): void
    {
        $data = [
            'Toyota' => [
                'Corolla', 'Yaris', 'Camry', 'Prado', 'Land Cruiser',
                'Fortuner', 'Hilux', 'Vitz', 'Aqua', 'Passo',
                'Premio', 'Belta', 'Hiace', 'Rush', 'Avanza',
            ],
            'Honda' => [
                'Civic', 'City', 'BR-V', 'HR-V', 'Accord',
                'Vezel', 'Fit', 'Freed', 'N-Wgn', 'N-One',
            ],
            'Suzuki' => [
                'Alto', 'Mehran', 'Cultus', 'Wagon R', 'Swift',
                'Bolan', 'Ravi', 'Ciaz', 'Liana', 'Khyber',
                'Margalla', 'Baleno', 'Every', 'APV',
            ],
            'Daihatsu' => [
                'Mira', 'Cuore', 'Move', 'Hijet', 'Tanto',
                'Boon', 'Terios',
            ],
            'Nissan' => [
                'Dayz', 'Sunny', 'March', 'Note', 'Juke',
                'Moco', 'Clipper', 'Patrol',
            ],
            'KIA' => [
                'Sportage', 'Picanto', 'Sorento', 'Stonic', 'Carnival',
                'Cerato',
            ],
            'Hyundai' => [
                'Tucson', 'Elantra', 'Sonata', 'Santa Fe', 'Porter',
                'Ioniq',
            ],
            'MG' => [
                'HS', 'ZS', 'ZS EV', 'MG5',
            ],
            'Changan' => [
                'Alsvin', 'Oshan X7', 'Karvaan', 'M9',
            ],
            'Proton' => [
                'Saga', 'X70', 'X50',
            ],
            'FAW' => [
                'V2', 'X-PV', 'Carrier', 'Sirius',
            ],
            'Mitsubishi' => [
                'Lancer', 'Pajero', 'Mirage', 'Outlander', 'Ek Wagon',
            ],
            'Mazda' => [
                'Mazda2', 'Mazda3', 'CX-5', 'Carol', 'Flair',
            ],
            'Mercedes-Benz' => [
                'C-Class', 'E-Class', 'S-Class', 'A-Class', 'GLC',
            ],
            'BMW' => [
                '3 Series', '5 Series', '7 Series', 'X1', 'X5',
            ],
            'Audi' => [
                'A3', 'A4', 'A6', 'Q2', 'Q5',
            ],
            'DFSK' => [
                'Glory 580', 'Glory 500', 'Glory 330',
            ],
            'Haval' => [
                'H6', 'Jolion', 'H6 HEV',
            ],
            'Chery' => [
                'Tiggo 4 Pro', 'Tiggo 8 Pro',
            ],
            'BAIC' => [
                'BJ40', 'X55',
            ],
        ];

        $now = now();

        foreach ($data as $makeName => $models) {
            $makeId = DB::table('vehicle_makes')->insertGetId([
                'name'       => $makeName,
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $rows = [];
            foreach ($models as $modelName) {
                $rows[] = [
                    'make_id'    => $makeId,
                    'name'       => $modelName,
                    'status'     => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::table('vehicle_models')->insert($rows);
        }
    }
}
