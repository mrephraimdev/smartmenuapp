<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Dish;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Table;
use App\Models\Tenant;
use App\Observers\CategoryObserver;
use App\Observers\DishObserver;
use App\Observers\MenuObserver;
use App\Observers\OrderObserver;
use App\Observers\TableObserver;
use App\Observers\TenantObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix pour l'erreur "La clé est trop longue"
        Schema::defaultStringLength(191);

        // Configuration Rate Limiting
        $this->configureRateLimiting();

        // Register Observers for Audit Logging
        $this->registerObservers();
    }

    /**
     * Register model observers for audit logging.
     */
    protected function registerObservers(): void
    {
        Dish::observe(DishObserver::class);
        Order::observe(OrderObserver::class);
        Category::observe(CategoryObserver::class);
        Menu::observe(MenuObserver::class);
        Table::observe(TableObserver::class);
        Tenant::observe(TenantObserver::class);
    }

    /**
     * Configure les limiteurs de taux (Rate Limiting)
     */
    protected function configureRateLimiting(): void
    {
        // Rate Limiting API global : 60 requêtes par minute par IP
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        // Rate Limiting par tenant : 1000 requêtes par minute par tenant
        RateLimiter::for('api-tenant', function (Request $request) {
            $tenantId = $request->user()?->tenant_id ?? 'guest';
            return Limit::perMinute(1000)->by($tenantId);
        });

        // Rate Limiting strict pour routes sensibles : 10 requêtes par minute
        RateLimiter::for('api-strict', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?? $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'error' => 'Trop de requêtes. Veuillez réessayer dans une minute.',
                        'message' => 'Rate limit exceeded'
                    ], 429, $headers);
                });
        });

        // Rate Limiting pour authentification : 5 tentatives par minute
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->email . '|' . $request->ip());
        });

        // ========================================
        // Rate Limiting pour les endpoints publics
        // ========================================

        // Création de commandes : 5 par minute par IP + table
        // Empêche le spam de commandes depuis un même appareil/table
        RateLimiter::for('orders', function (Request $request) {
            $key = $request->ip() . '|' . ($request->table_id ?? 'unknown');
            return Limit::perMinute(5)->by($key)
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Trop de commandes. Veuillez patienter avant de repasser une commande.',
                    ], 429, $headers);
                });
        });

        // Appels serveur : 3 par minute par table
        // Le contrôleur a déjà une limite de 2 min entre appels
        RateLimiter::for('waiter-calls', function (Request $request) {
            $key = $request->ip() . '|' . ($request->table_id ?? 'unknown');
            return Limit::perMinute(3)->by($key)
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Trop d\'appels. Veuillez patienter.',
                    ], 429, $headers);
                });
        });

        // Réservations : 3 par heure par IP
        // Empêche le spam de réservations
        RateLimiter::for('reservations', function (Request $request) {
            return Limit::perHour(3)->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Trop de réservations. Veuillez réessayer plus tard.',
                    ], 429, $headers);
                });
        });

        // Avis clients : 2 par jour par IP
        RateLimiter::for('reviews', function (Request $request) {
            return Limit::perDay(2)->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Vous avez déjà soumis des avis aujourd\'hui.',
                    ], 429, $headers);
                });
        });
    }
}
