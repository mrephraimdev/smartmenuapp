<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust the reverse proxy (nginx host) so Laravel generates HTTPS URLs
        $middleware->trustProxies(at: '*', headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO);

        // Ajouter les headers de sécurité à toutes les réponses web
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\SentryContext::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\SentryContext::class,
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
        $exceptions->reportable(function (\Throwable $e): void {
            if (app()->bound('sentry')) {
                \Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($e): void {
                    // Ajouter le contexte exception
                    $scope->setTag('exception.class', get_class($e));

                    // Ajouter le tenant depuis la requête courante si disponible
                    $request = request();
                    $tenantSlug = $request->route('tenantSlug');
                    if ($tenantSlug) {
                        $scope->setTag('tenant.slug', $tenantSlug);
                    }
                });
            }
        });
    })->create();
