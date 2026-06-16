<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cities')->insert([
            ['id' => 1, 'name' => 'Lahore', 'status' => 1],
            ['id' => 2, 'name' => 'Faisalabad', 'status' => 1],
            ['id' => 3, 'name' => 'Rawalpindi', 'status' => 1],
            ['id' => 4, 'name' => 'Multan', 'status' => 1],
            ['id' => 5, 'name' => 'Gujranwala', 'status' => 1],
            ['id' => 6, 'name' => 'Sargodha', 'status' => 1],
            ['id' => 7, 'name' => 'Bahawalpur', 'status' => 1],
            ['id' => 8, 'name' => 'Sialkot', 'status' => 1],
            ['id' => 9, 'name' => 'Sheikhupura', 'status' => 1],
            ['id' => 10, 'name' => 'Rahim Yar Khan', 'status' => 1],
            ['id' => 11, 'name' => 'Gujrat', 'status' => 1],
            ['id' => 12, 'name' => 'Jhelum', 'status' => 1],
            ['id' => 13, 'name' => 'Kasur', 'status' => 1],
            ['id' => 14, 'name' => 'Okara', 'status' => 1],
            ['id' => 15, 'name' => 'Sahiwal', 'status' => 1],
            ['id' => 16, 'name' => 'Chiniot', 'status' => 1],
            ['id' => 17, 'name' => 'Jhang', 'status' => 1],
            ['id' => 18, 'name' => 'Vehari', 'status' => 1],
            ['id' => 19, 'name' => 'Khanewal', 'status' => 1],
            ['id' => 20, 'name' => 'Mianwali', 'status' => 1],
            ['id' => 21, 'name' => 'Bhakkar', 'status' => 1],
            ['id' => 22, 'name' => 'Attock', 'status' => 1],
            ['id' => 23, 'name' => 'Narowal', 'status' => 1],
            ['id' => 24, 'name' => 'Pakpattan', 'status' => 1],
            ['id' => 25, 'name' => 'Layyah', 'status' => 1],
            ['id' => 26, 'name' => 'Muzaffargarh', 'status' => 1],
            ['id' => 27, 'name' => 'Dera Ghazi Khan', 'status' => 1],
            ['id' => 28, 'name' => 'Rajanpur', 'status' => 1],
            ['id' => 29, 'name' => 'Toba Tek Singh', 'status' => 1],
            ['id' => 30, 'name' => 'Hafizabad', 'status' => 1],
            ['id' => 31, 'name' => 'Mandi Bahauddin', 'status' => 1],
            ['id' => 32, 'name' => 'Nankana Sahib', 'status' => 1],
            ['id' => 33, 'name' => 'Khushab', 'status' => 1],
            ['id' => 34, 'name' => 'Lodhran', 'status' => 1],
            ['id' => 35, 'name' => 'Bahawalnagar', 'status' => 1],
            ['id' => 36, 'name' => 'Chakwal', 'status' => 1],
            ['id' => 37, 'name' => 'Talagang', 'status' => 1],
            ['id' => 38, 'name' => 'Wazirabad', 'status' => 1],
            ['id' => 39, 'name' => 'Kamoke', 'status' => 1],
            ['id' => 40, 'name' => 'Murree', 'status' => 1],
        ]);
    }
}
