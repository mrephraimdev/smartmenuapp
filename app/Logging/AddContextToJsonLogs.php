<?php

namespace App\Logging;

use Illuminate\Support\Facades\Auth;
use Monolog\LogRecord;

class AddContextToJsonLogs
{
    /**
     * Customize the given logger instance.
     *
     * @param  \Illuminate\Log\Logger  $logger
     * @return void
     */
    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->pushProcessor([$this, 'processLogRecord']);
        }
    }

    /**
     * Add context to log records.
     *
     * @param  \Monolog\LogRecord  $record
     * @return \Monolog\LogRecord
     */
    public function processLogRecord(LogRecord $record): LogRecord
    {
        $extra = $record->extra;

        // Add request ID for tracing
        if (request()->hasHeader('X-Request-ID')) {
            $extra['request_id'] = request()->header('X-Request-ID');
        } else {
            $extra['request_id'] = uniqid('req_', true);
        }

        // Add user context
        if (Auth::check()) {
            $user = Auth::user();
            $extra['user'] = [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role ?? null,
            ];

            // Add tenant context
            if ($user->tenant_id) {
                $extra['tenant'] = [
                    'id' => $user->tenant_id,
                    'name' => $user->tenant->name ?? null,
                ];
            }
        }

        // Add request context
        if (app()->runningInConsole()) {
            $extra['context'] = 'console';
            $extra['command'] = $_SERVER['argv'] ?? null;
        } else {
            $extra['context'] = 'web';
            $extra['request'] = [
                'method' => request()->method(),
                'url' => request()->fullUrl(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ];
        }

        // Add environment info
        $extra['environment'] = config('app.env');
        $extra['timestamp'] = now()->toIso8601String();

        return $record->with(extra: $extra);
    }
}
