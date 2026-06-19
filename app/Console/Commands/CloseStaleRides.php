<?php

namespace App\Console\Commands;

use App\Actions\Ride\BookingAction;
use Illuminate\Console\Command;

class CloseStaleRides extends Command
{
    protected $signature = 'rides:close-stale {--hours=2}';

    protected $description = 'Auto-close rides left open past their departure time + grace period';

    public function handle(BookingAction $action): int
    {
        $count = $action->autoCloseStale((int) $this->option('hours'));

        $this->info("Closed {$count} stale ride(s).");

        return self::SUCCESS;
    }
}
