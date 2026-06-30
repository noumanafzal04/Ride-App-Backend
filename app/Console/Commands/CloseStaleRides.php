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
        // 1) Cancel posts that hit departure with no accepted passenger (no grace).
        $cancelled = $action->cancelEmptyExpired();
        // 2) Auto-complete rides that carried passengers but were left open past grace.
        $closed = $action->autoCloseStale((int) $this->option('hours'));

        $this->info("Cancelled {$cancelled} empty ride(s), closed {$closed} stale ride(s).");

        return self::SUCCESS;
    }
}
