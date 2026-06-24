<?php

namespace Database\Seeders;

use App\Models\CarListing;
use App\Models\City;
use App\Models\RentalCar;
use App\Models\RidePost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $cities  = City::take(6)->get();
        $users   = User::take(4)->get();
        $drivers = User::where('user_type', 'driver')->take(3)->get();
        if ($drivers->isEmpty()) $drivers = $users;
        if ($cities->count() < 2 || $users->isEmpty()) {
            $this->command?->warn('DemoSeeder: need cities + users first.');
            return;
        }

        $cityAt = fn($i) => $cities[$i % $cities->count()];
        $userAt = fn($i) => $users[$i % $users->count()];

        // ── Buy/Sell listings ──
        $cars = [
            ['Honda', 'City', 2021, 7200000, 32000, 'automatic', 'petrol', 'sedan'],
            ['Toyota', 'Corolla', 2020, 6500000, 45000, 'automatic', 'petrol', 'sedan'],
            ['Suzuki', 'Alto', 2022, 2600000, 12000, 'manual', 'petrol', 'economy'],
            ['Toyota', 'Fortuner', 2019, 16500000, 70000, 'automatic', 'diesel', 'suv'],
            ['Honda', 'Civic', 2022, 9800000, 18000, 'automatic', 'petrol', 'sedan'],
            ['Kia', 'Sportage', 2021, 9200000, 40000, 'automatic', 'petrol', 'suv'],
        ];
        foreach ($cars as $i => [$mk, $md, $yr, $price, $km, $tr, $fuel]) {
            CarListing::create([
                'user_id' => $userAt($i)->id, 'listing_type' => 'self', 'status' => 'active',
                'make' => $mk, 'model' => $md, 'year' => $yr, 'price' => $price, 'mileage' => $km,
                'condition' => 'used', 'transmission' => $tr, 'fuel_type' => $fuel, 'color' => 'White',
                'city_id' => $cityAt($i)->id, 'description' => "Well-maintained {$mk} {$md} for sale.",
            ]);
        }

        // ── Rent-a-Car ──
        $rentals = [
            ['Toyota', 'Fortuner', 2022, 'suv', 7, 15000, 'with_driver'],
            ['Honda', 'Civic', 2021, 'sedan', 5, 9000, 'with_driver'],
            ['Toyota', 'Corolla', 2020, 'sedan', 5, 7000, 'both'],
            ['Suzuki', 'APV', 2019, 'van', 8, 11000, 'with_driver'],
            ['Toyota', 'Prado', 2018, 'luxury', 7, 25000, 'with_driver'],
            ['Suzuki', 'Cultus', 2021, 'economy', 4, 6000, 'self_drive'],
        ];
        foreach ($rentals as $i => [$mk, $md, $yr, $cat, $seats, $rate, $rt]) {
            RentalCar::create([
                'user_id' => $userAt($i)->id, 'listing_type' => 'self', 'status' => 'active',
                'make' => $mk, 'model' => $md, 'year' => $yr, 'category' => $cat, 'seats' => $seats,
                'transmission' => 'automatic', 'fuel_type' => 'petrol', 'color' => 'White',
                'rental_type' => $rt, 'price_per_day' => $rate,
                'price_per_day_self' => $rt === 'self_drive' ? $rate : ($rt === 'both' ? $rate - 1500 : null),
                'deposit' => $rt === 'with_driver' ? null : 50000, 'min_days' => 1,
                'city_id' => $cityAt($i)->id, 'description' => "{$mk} {$md} available for rent.",
                'features' => ['AC', 'Bluetooth'],
            ]);
        }

        // ── Ride posts ──
        $driver = $drivers->first();
        for ($i = 0; $i < 6; $i++) {
            $from = $cityAt($i);
            $to   = $cityAt($i + 1);
            RidePost::create([
                'driver_id'      => ($drivers[$i % $drivers->count()])->id,
                'from_city_id'   => $from->id,
                'to_city_id'     => $to->id,
                'from_address'   => $from->name . ' city center',
                'to_address'     => $to->name . ' city center',
                'from_latitude'  => $from->lat,
                'from_longitude' => $from->lon,
                'to_latitude'    => $to->lat,
                'to_longitude'   => $to->lon,
                'departure_at'   => Carbon::now()->addDays($i + 1)->setHour(9)->setMinute(0),
                'available_seats' => 3,
                'price_per_seat' => 2000 + $i * 500,
                'post_type'      => 'shared',
                'status'         => 'active',
            ]);
        }

        $this->command?->info('DemoSeeder: 6 listings, 6 rentals, 6 ride posts created.');
    }
}
