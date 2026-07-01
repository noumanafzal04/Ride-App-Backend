<?php

namespace App\Console\Commands;

use App\Models\CarListing;
use App\Models\RentalCar;
use Illuminate\Console\Command;

class ExpireFeatured extends Command
{
    protected $signature = 'features:expire';
    protected $description = 'Un-feature listings/rentals whose paid feature window has ended';

    public function handle(): int
    {
        $now = now();
        $a = CarListing::where('is_featured', true)->whereNotNull('featured_until')
            ->where('featured_until', '<', $now)->update(['is_featured' => false]);
        $b = RentalCar::where('is_featured', true)->whereNotNull('featured_until')
            ->where('featured_until', '<', $now)->update(['is_featured' => false]);

        $this->info("Un-featured {$a} listing(s) and {$b} rental(s).");
        return self::SUCCESS;
    }
}
