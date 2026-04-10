<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Theme;
use App\Models\Menu;
use App\Models\User;
use App\Enums\TenantType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantService
{
    /**
     * Create a new tenant with default setup
     */
    public function createTenant(array $data): Tenant
    {
        return DB::transaction(function () use ($data) {
            $slug = $data['slug'] ?? Str::slug($data['name']);
            $slug = $this->ensureUniqueSlug($slug);

            $tenant = Tenant::create([
                'name' => $data['name'],
                'slug' => $slug,
                'type' => $data['type'] ?? TenantType::RESTAURANT->value,
                'locale' => $data['locale'] ?? 'fr',
                'currency' => $data['currency'] ?? 'XOF',
                'logo_url' => $data['logo_url'] ?? null,
                'cover_url' => $data['cover_url'] ?? null,
                'branding' => $data['branding'] ?? [],
                'is_active' => $data['is_active'] ?? true,
                'theme_id' => $data['theme_id'] ?? $this->getDefaultThemeId($data['type'] ?? null),
            ]);

            // Create default menu
            Menu::create([
                'tenant_id' => $tenant->id,
                'name' => 'Menu Principal',
                'active' => true,
            ]);

            return $tenant->load('theme');
        });
    }

    /**
     * Update tenant
     */
    public function updateTenant(Tenant $tenant, array $data): Tenant
    {
        $updateData = [];

        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }

        if (isset($data['slug'])) {
            $slug = Str::slug($data['slug']);
            if ($slug !== $tenant->slug) {
                $updateData['slug'] = $this->ensureUniqueSlug($slug, $tenant->id);
            }
        }

        if (isset($data['type'])) {
            $updateData['type'] = $data['type'];
        }

        if (isset($data['locale'])) {
            $updateData['locale'] = $data['locale'];
        }

        if (isset($data['currency'])) {
            $updateData['currency'] = $data['currency'];
        }

        if (isset($data['is_active'])) {
            $updateData['is_active'] = $data['is_active'];
        }

        if (isset($data['theme_id'])) {
            $updateData['theme_id'] = $data['theme_id'];
        }

        $tenant->update($updateData);

        return $tenant->fresh('theme');
    }

    /**
     * Update branding
     */
    public function updateBranding(Tenant $tenant, array $branding): Tenant
    {
        $currentBranding = $tenant->branding ?? [];
        $newBranding = array_merge($currentBranding, $branding);

        $tenant->update(['branding' => $newBranding]);

        return $tenant->fresh();
    }

    /**
     * Update logo
     */
    public function updateLogo(Tenant $tenant, string $logoUrl): Tenant
    {
        $tenant->update(['logo_url' => $logoUrl]);
        return $tenant->fresh();
    }

    /**
     * Update cover image
     */
    public function updateCover(Tenant $tenant, string $coverUrl): Tenant
    {
        $tenant->update(['cover_url' => $coverUrl]);
        return $tenant->fresh();
    }

    /**
     * Apply theme to tenant
     */
    public function applyTheme(Tenant $tenant, int $themeId): Tenant
    {
        $theme = Theme::findOrFail($themeId);
        $tenant->update(['theme_id' => $theme->id]);

        return $tenant->fresh('theme');
    }

    /**
     * Get tenant with all related data
     */
    public function getTenantWithDetails(int $tenantId): ?Tenant
    {
        return Tenant::with(['theme', 'menus', 'tables', 'users'])
            ->find($tenantId);
    }

    /**
     * Get tenant by slug
     */
    public function getTenantBySlug(string $slug): ?Tenant
    {
        return Tenant::with('theme')
            ->where('slug', $slug)
            ->first();
    }

    /**
     * Get all active tenants
     */
    public function getActiveTenants(): Collection
    {
        return Tenant::with('theme')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get tenant statistics
     */
    public function getTenantStats(Tenant $tenant): array
    {
        return [
            'users_count' => $tenant->users()->count(),
            'tables_count' => $tenant->tables()->count(),
            'menus_count' => $tenant->menus()->count(),
            'dishes_count' => $tenant->dishes()->count(),
            'orders_count' => $tenant->orders()->count(),
            'orders_today' => $tenant->orders()->whereDate('created_at', now())->count(),
            'revenue_today' => $tenant->orders()
                ->whereDate('created_at', now())
                ->whereIn('status', ['PRET', 'SERVI'])
                ->sum('total'),
        ];
    }

    /**
     * Activate tenant
     */
    public function activateTenant(Tenant $tenant): Tenant
    {
        $tenant->update(['is_active' => true]);
        return $tenant->fresh();
    }

    /**
     * Deactivate tenant
     */
    public function deactivateTenant(Tenant $tenant): Tenant
    {
        $tenant->update(['is_active' => false]);
        return $tenant->fresh();
    }

    /**
     * Get available themes for tenant type
     */
    public function getAvailableThemes(?string $tenantType = null): Collection
    {
        $query = Theme::query();

        if ($tenantType) {
            $query->where(function ($q) use ($tenantType) {
                $q->where('category', $tenantType)
                  ->orWhereNull('category');
            });
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Ensure unique slug
     */
    private function ensureUniqueSlug(string $slug, ?int $excludeId = null): string
    {
        $originalSlug = $slug;
        $counter = 1;

        $query = Tenant::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;

            $query = Tenant::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }

    /**
     * Get default theme ID based on tenant type
     */
    private function getDefaultThemeId(?string $type): ?int
    {
        if (!$type) {
            return Theme::where('is_default', true)->value('id');
        }

        $tenantType = TenantType::tryFrom($type);
        if ($tenantType) {
            $themeSlug = $tenantType->defaultTheme();
            $theme = Theme::where('slug', $themeSlug)->first();
            if ($theme) {
                return $theme->id;
            }
        }

        return Theme::where('is_default', true)->value('id');
    }
}
