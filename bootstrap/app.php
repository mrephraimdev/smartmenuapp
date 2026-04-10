<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Ajouter les headers de sécurité à toutes les réponses web
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'api/*',
            'order/*',
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'security.headers' => \App\Http\Middleware\SecurityHeaders::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Backup cleanup - Daily at 1:00 AM
        $schedule->command('backup:clean')->daily()->at('01:00');

        // Run backup - Daily at 2:00 AM
        $schedule->command('backup:run')->daily()->at('02:00');

        // Monitor backups health
        $schedule->command('backup:monitor')->daily()->at('03:00');

        // Clear old logs
        $schedule->command('logs:clear')->weekly();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
