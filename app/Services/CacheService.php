<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Cache TTL constants (in seconds)
     */
    public const TTL_MENU = 3600;        // 1 hour
    public const TTL_STATISTICS = 300;    // 5 minutes
    public const TTL_TENANT = 86400;      // 24 hours
    public const TTL_THEME = 86400;       // 24 hours

    /**
     * Cache key prefixes
     */
    public const PREFIX_MENU = 'menu';
    public const PREFIX_STATS = 'stats';
    public const PREFIX_TENANT = 'tenant';
    public const PREFIX_THEME = 'theme';

    /**
     * Get cached menu for a tenant.
     */
    public function getMenu(int $tenantId): ?array
    {
        return Cache::tags([$this->tenantTag($tenantId), self::PREFIX_MENU])
            ->get($this->menuKey($tenantId));
    }

    /**
     * Cache menu for a tenant.
     */
    public function putMenu(int $tenantId, array $menuData): void
    {
        Cache::tags([$this->tenantTag($tenantId), self::PREFIX_MENU])
            ->put($this->menuKey($tenantId), $menuData, self::TTL_MENU);
    }

    /**
     * Get or cache menu for a tenant.
     */
    public function rememberMenu(int $tenantId, callable $callback): array
    {
        return Cache::tags([$this->tenantTag($tenantId), self::PREFIX_MENU])
            ->remember($this->menuKey($tenantId), self::TTL_MENU, $callback);
    }

    /**
     * Invalidate menu cache for a tenant.
     */
    public function forgetMenu(int $tenantId): void
    {
        Cache::tags([$this->tenantTag($tenantId), self::PREFIX_MENU])->flush();
    }

    /**
     * Get cached statistics for a tenant.
     */
    public function getStatistics(int $tenantId, string $period = 'today'): ?array
    {
        return Cache::tags([$this->tenantTag($tenantId), self::PREFIX_STATS])
            ->get($this->statsKey($tenantId, $period));
    }

    /**
     * Cache statistics for a tenant.
     */
    public function putStatistics(int $tenantId, string $period, array $stats): void
    {
        Cache::tags([$this->tenantTag($tenantId), self::PREFIX_STATS])
            ->put($this->statsKey($tenantId, $period), $stats, self::TTL_STATISTICS);
    }

    /**
     * Get or cache statistics for a tenant.
     */
    public function rememberStatistics(int $tenantId, string $period, callable $callback): array
    {
        return Cache::tags([$this->tenantTag($tenantId), self::PREFIX_STATS])
            ->remember($this->statsKey($tenantId, $period), self::TTL_STATISTICS, $callback);
    }

    /**
     * Invalidate statistics cache for a tenant.
     */
    public function forgetStatistics(int $tenantId): void
    {
        Cache::tags([$this->tenantTag($tenantId), self::PREFIX_STATS])->flush();
    }

    /**
     * Get cached tenant data.
     */
    public function getTenant(int $tenantId): ?array
    {
        return Cache::tags([self::PREFIX_TENANT])
            ->get($this->tenantDataKey($tenantId));
    }

    /**
     * Cache tenant data.
     */
    public function putTenant(int $tenantId, array $tenantData): void
    {
        Cache::tags([self::PREFIX_TENANT])
            ->put($this->tenantDataKey($tenantId), $tenantData, self::TTL_TENANT);
    }

    /**
     * Get or cache tenant data.
     */
    public function rememberTenant(int $tenantId, callable $callback): array
    {
        return Cache::tags([self::PREFIX_TENANT])
            ->remember($this->tenantDataKey($tenantId), self::TTL_TENANT, $callback);
    }

    /**
     * Invalidate tenant cache.
     */
    public function forgetTenant(int $tenantId): void
    {
        Cache::tags([self::PREFIX_TENANT])->forget($this->tenantDataKey($tenantId));
        // Also invalidate all tenant-specific caches
        Cache::tags([$this->tenantTag($tenantId)])->flush();
    }

    /**
     * Get cached theme for a tenant.
     */
    public function getTheme(int $tenantId): ?array
    {
        return Cache::tags([$this->tenantTag($tenantId), self::PREFIX_THEME])
            ->get($this->themeKey($tenantId));
    }

    /**
     * Cache theme for a tenant.
     */
    public function putTheme(int $tenantId, array $themeData): void
    {
        Cache::tags([$this->tenantTag($tenantId), self::PREFIX_THEME])
            ->put($this->themeKey($tenantId), $themeData, self::TTL_THEME);
    }

    /**
     * Get or cache theme for a tenant.
     */
    public function rememberTheme(int $tenantId, callable $callback): array
    {
        return Cache::tags([$this->tenantTag($tenantId), self::PREFIX_THEME])
            ->remember($this->themeKey($tenantId), self::TTL_THEME, $callback);
    }

    /**
     * Invalidate theme cache for a tenant.
     */
    public function forgetTheme(int $tenantId): void
    {
        Cache::tags([$this->tenantTag($tenantId), self::PREFIX_THEME])->flush();
    }

    /**
     * Warm all caches for a tenant.
     */
    public function warmTenantCaches(int $tenantId): void
    {
        $tenant = Tenant::with(['theme'])->find($tenantId);

        if (!$tenant) {
            return;
        }

        // Cache tenant data
        $this->putTenant($tenantId, $tenant->toArray());

        // Cache theme
        if ($tenant->theme) {
            $this->putTheme($tenantId, $tenant->theme->toArray());
        }

        // Cache menu with full data
        $menu = Menu::with([
            'categories' => function ($query) {
                $query->orderBy('position');
            },
            'categories.dishes' => function ($query) {
                $query->where('active', true)
                    ->orderBy('position');
            },
            'categories.dishes.variants',
            'categories.dishes.options',
        ])
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->first();

        if ($menu) {
            $this->putMenu($tenantId, $menu->toArray());
        }
    }

    /**
     * Flush all caches for a tenant.
     */
    public function flushTenantCaches(int $tenantId): void
    {
        Cache::tags([$this->tenantTag($tenantId)])->flush();
    }

    /**
     * Flush all caches.
     */
    public function flushAll(): void
    {
        Cache::tags([self::PREFIX_MENU])->flush();
        Cache::tags([self::PREFIX_STATS])->flush();
        Cache::tags([self::PREFIX_TENANT])->flush();
        Cache::tags([self::PREFIX_THEME])->flush();
    }

    /**
     * Get cache statistics.
     */
    public function getCacheStats(): array
    {
        return [
            'driver' => config('cache.default'),
            'prefix' => config('cache.prefix'),
        ];
    }

    /**
     * Generate tenant tag.
     */
    protected function tenantTag(int $tenantId): string
    {
        return "tenant:{$tenantId}";
    }

    /**
     * Generate menu cache key.
     */
    protected function menuKey(int $tenantId): string
    {
        return self::PREFIX_MENU . ":{$tenantId}";
    }

    /**
     * Generate statistics cache key.
     */
    protected function statsKey(int $tenantId, string $period): string
    {
        return self::PREFIX_STATS . ":{$tenantId}:{$period}";
    }

    /**
     * Generate tenant data cache key.
     */
    protected function tenantDataKey(int $tenantId): string
    {
        return self::PREFIX_TENANT . ":{$tenantId}";
    }

    /**
     * Generate theme cache key.
     */
    protected function themeKey(int $tenantId): string
    {
        return self::PREFIX_THEME . ":{$tenantId}";
    }
}
