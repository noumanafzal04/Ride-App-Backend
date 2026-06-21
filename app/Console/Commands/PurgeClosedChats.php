<?php

namespace App\Console\Commands;

use App\Repositories\Chat\ConversationRepository;
use Illuminate\Console\Command;

class PurgeClosedChats extends Command
{
    protected $signature = 'chat:purge-closed {--days=30 : Delete conversations closed more than this many days ago}';

    protected $description = 'Delete conversations (and their messages) closed beyond the retention window, to keep the DB lean.';

    public function handle(ConversationRepository $conversations): int
    {
        $days   = (int) $this->option('days');
        $cutoff = now()->subDays($days)->toDateTimeString();

        $ids = $conversations->closedBefore($cutoff);

        if (empty($ids)) {
            $this->info('No closed conversations to purge.');
            return self::SUCCESS;
        }

        // messages cascade-delete via the FK.
        $conversations->deleteWhereIn('id', $ids);

        $this->info('Purged ' . count($ids) . ' closed conversation(s).');
        return self::SUCCESS;
    }
}
