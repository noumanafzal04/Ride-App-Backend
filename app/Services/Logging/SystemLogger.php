<?php 

namespace App\Services\Logging;

use Illuminate\Support\Facades\Log;
use App\Jobs\PersistLogJob;

class SystemLogger
{
    protected array $allowedLevels = [
        'info', 'warning', 'error', 'debug', 'critical'
    ];

    public function log(string $level, string $action, string $message, array $context = []): void
    {
        if (! in_array($level, $this->allowedLevels, true)) {
            $level = 'info';
        }

        $baseContext = request()?->attributes->get('log_context', []);

        $logContext = array_merge($baseContext, $context, [
            'action' => $action,
        ]);

        $payload = [
            'level'   => $level,
            'message' => $message,
            'context' => $logContext,
        ];

        if (config('logging.async_logging')) {            
            PersistLogJob::dispatch($payload)->onQueue('logging');
            return;
        }

        $this->writeSync($payload);
    }

    protected function writeSync(array $payload): void
    {
        Log::channel('activity')->log(
            $payload['level'],
            $payload['message'],
            $payload['context']
        );
    }

    // Convenience methods
    public function info(string $action, string $message, array $context = []): void
    {
        $this->log('info', $action, $message, $context);
    }

    public function error(string $action, string $message, array $context = []): void
    {
        $this->log('error', $action, $message, $context);
    }
}
