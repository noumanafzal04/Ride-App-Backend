<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use App\Models\City;

class PunjabCitiesAndDistancesSeeder extends Seeder
{
    private string $geoapifyKey = 'a098706f8c7b4c948ea5b6993e1615c7';

    public function run(): void
    {
        $this->command->info('Fetching all Punjab cities from Geoapify...');
        $this->fetchAndSaveCities();
        $this->command->info('✅ All done!');
    }

    private function fetchAndSaveCities(): void
    {
        $response = Http::get('https://api.geoapify.com/v2/places', [
            'categories' => 'populated_place.city,populated_place.town',
            'filter'     => 'rect:69.314,27.703,75.385,34.026',
            'limit'      => 500,
            'apiKey'     => $this->geoapifyKey,
        ]);

        if (!$response->successful()) {
            $this->command->error('Geoapify request failed: ' . $response->status());
            $this->command->error('Response: ' . $response->body());
            return;
        }

        $features = $response->json('features', []);
        $this->command->line('Geoapify returned ' . count($features) . ' places total.');

        $saved   = 0;
        $skipped = 0;
        $seen    = [];

        foreach ($features as $feature) {
            $props = $feature['properties'] ?? [];

            // Only Pakistan
            if (strtolower($props['country_code'] ?? '') !== 'pk') {
                $skipped++;
                continue;
            }

            // English name from 'city' field
            $name = $props['city'] ?? '';
            if (empty($name)) {
                $skipped++;
                continue;
            }

            // Skip duplicates
            if (isset($seen[$name])) {
                $skipped++;
                continue;
            }
            $seen[$name] = true;

            $lat = $feature['geometry']['coordinates'][1] ?? null;
            $lon = $feature['geometry']['coordinates'][0] ?? null;

            if (!$lat || !$lon) {
                $skipped++;
                continue;
            }

            City::updateOrCreate(
                ['name' => $name],
                [
                    'province' => 'Punjab',
                    'lat'      => $lat,
                    'lon'      => $lon,
                ]
            );

            $saved++;
            $this->command->line("  ✓ [{$saved}] {$name} (lat:{$lat}, lon:{$lon})");
        }

        $this->command->info("Cities saved: {$saved} | Skipped: {$skipped}");
    }
}
