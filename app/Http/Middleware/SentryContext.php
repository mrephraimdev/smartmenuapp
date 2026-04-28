<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SentryContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->bound('sentry') && \Sentry\SentrySdk::getCurrentHub()->getClient()) {
            $this->configureSentryScope($request);
        }

        return $next($request);
    }

    private function configureSentryScope(Request $request): void
    {
        \Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($request): void {
            $user = auth()->user();

            if ($user) {
                $scope->setUser([
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                    'role' => $user->role,
                ]);

                if ($user->tenant_id) {
                    $scope->setTag('tenant.id', (string) $user->tenant_id);
                    $scope->setContext('tenant', [
                        'id' => $user->tenant_id,
                        'slug' => $user->tenant?->slug ?? 'unknown',
                        'name' => $user->tenant?->name ?? 'unknown',
                    ]);
                }
            }

            // Contexte de la requête
            $scope->setTag('app.locale', app()->getLocale());
            $scope->setTag('request.route', $request->route()?->getName() ?? 'unnamed');

            // Extraire le tenant slug depuis l'URL si présent
            $tenantSlug = $request->route('tenantSlug');
            if ($tenantSlug) {
                $scope->setTag('tenant.slug', $tenantSlug);
            }
        });
    }
}
