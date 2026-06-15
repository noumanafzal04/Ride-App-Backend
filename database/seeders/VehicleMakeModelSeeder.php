<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleMakeModelSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Toyota' => [
                'Corolla' => 5,
                'Yaris' => 5,
                'Camry' => 5,
                'Prado' => 7,
                'Land Cruiser' => 8,
                'Fortuner' => 7,
                'Hilux' => 5,
                'Vitz' => 5,
                'Aqua' => 5,
                'Passo' => 5,
                'Premio' => 5,
                'Belta' => 5,
                'Hiace' => 15,
                'Rush' => 7,
                'Avanza' => 7,
            ],
            'Honda' => [
                'Civic' => 5,
                'City' => 5,
                'BR-V' => 7,
                'HR-V' => 5,
                'Accord' => 5,
                'Vezel' => 5,
                'Fit' => 5,
                'Freed' => 7,
                'N-Wgn' => 4,
                'N-One' => 4,
            ],
            'Suzuki' => [
                'Alto' => 4,
                'Mehran' => 4,
                'Cultus' => 5,
                'Wagon R' => 5,
                'Swift' => 5,
                'Bolan' => 7,
                'Ravi' => 2,
                'Ciaz' => 5,
                'Liana' => 5,
                'Khyber' => 5,
                'Margalla' => 5,
                'Baleno' => 5,
                'Every' => 8,
                'APV' => 7,
            ],
            'Daihatsu' => [
                'Mira' => 4,
                'Cuore' => 4,
                'Move' => 4,
                'Hijet' => 6,
                'Tanto' => 4,
                'Boon' => 5,
                'Terios' => 5,
            ],
            'Nissan' => [
                'Dayz' => 4,
                'Sunny' => 5,
                'March' => 5,
                'Note' => 5,
                'Juke' => 5,
                'Moco' => 4,
                'Clipper' => 4,
                'Patrol' => 7,
            ],
            'KIA' => [
                'Sportage' => 5,
                'Picanto' => 5,
                'Sorento' => 7,
                'Stonic' => 5,
                'Carnival' => 8,
                'Cerato' => 5,
            ],
            'Hyundai' => [
                'Tucson' => 5,
                'Elantra' => 5,
                'Sonata' => 5,
                'Santa Fe' => 7,
                'Porter' => 3,
                'Ioniq' => 5,
            ],
            'MG' => [
                'HS' => 5,
                'ZS' => 5,
                'ZS EV' => 5,
                'MG5' => 5,
            ],
            'Changan' => [
                'Alsvin' => 5,
                'Oshan X7' => 7,
                'Karvaan' => 7,
                'M9' => 7,
            ],
            'Proton' => [
                'Saga' => 5,
                'X70' => 5,
                'X50' => 5,
            ],
            'FAW' => [
                'V2' => 5,
                'X-PV' => 7,
                'Carrier' => 2,
                'Sirius' => 5,
            ],
            'Mitsubishi' => [
                'Lancer' => 5,
                'Pajero' => 7,
                'Mirage' => 5,
                'Outlander' => 7,
                'Ek Wagon' => 4,
            ],
            'Mazda' => [
                'Mazda2' => 5,
                'Mazda3' => 5,
                'CX-5' => 5,
                'Carol' => 4,
                'Flair' => 4,
            ],
            'Mercedes-Benz' => [
                'C-Class' => 5,
                'E-Class' => 5,
                'S-Class' => 5,
                'A-Class' => 5,
                'GLC' => 5,
            ],
            'BMW' => [
                '3 Series' => 5,
                '5 Series' => 5,
                '7 Series' => 5,
                'X1' => 5,
                'X5' => 5,
            ],
            'Audi' => [
                'A3' => 5,
                'A4' => 5,
                'A6' => 5,
                'Q2' => 5,
                'Q5' => 5,
            ],
            'DFSK' => [
                'Glory 580' => 5,
                'Glory 500' => 5,
                'Glory 330' => 7,
            ],
            'Haval' => [
                'H6' => 5,
                'Jolion' => 5,
                'H6 HEV' => 5,
            ],
            'Chery' => [
                'Tiggo 4 Pro' => 5,
                'Tiggo 8 Pro' => 7,
            ],
            'BAIC' => [
                'BJ40' => 5,
                'X55' => 5,
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
            foreach ($models as $modelName => $seatingCapacity) {
                $rows[] = [
                    'make_id'          => $makeId,
                    'name'             => $modelName,
                    'seating_capacity' => $seatingCapacity,
                    'status'           => 'active',
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];
            }
            DB::table('vehicle_models')->insert($rows);
        }
    }
}
