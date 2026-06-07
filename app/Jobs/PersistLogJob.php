<?php

namespace App\Jobs;

use App\Models\SystemLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class PersistLogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function handle(): void
    {
        try {
            // SystemLog::create([
            //     'level'    => $this->payload['level'],
            //     'message'  => $this->payload['message'],
            //     'context'  => $this->payload['context'],
            //     'trace_id' => $this->payload['context']['trace_id'] ?? null,
            // ]);

            Log::channel('activity')->log(
                $this->payload['level'],
                $this->payload['message'],
                $this->payload['context']
            );
            
        } catch (Throwable $e) {
            
            Log::channel('emergency')->error(
                'Failed to persist system log',
                [
                    'original_payload' => $this->payload,
                    'exception' => [
                        'message' => $e->getMessage(),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine(),
                        'trace'   => $e->getTraceAsString(),
                    ],
                ]
            );

            /**
             * Mark job as failed (visible in Horizon / queue:failed)
             */
            $this->fail($e);
        }
    }
}
