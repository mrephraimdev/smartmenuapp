<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Theme;
use App\Models\Menu;
use App\Services\TenantService;
use App\Enums\TenantType;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TenantServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TenantService $tenantService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenantService = new TenantService();

        // Create a default theme
        Theme::create([
            'name' => 'Default Theme',
            'slug' => 'default',
            'colors' => ['primary' => '#000000'],
            'fonts' => ['heading' => 'Arial'],
            'is_default' => true,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_can_create_a_tenant_with_default_menu()
    {
        $tenantData = [
            'name' => 'Mon Restaurant',
            'type' => TenantType::RESTAURANT->value,
            'currency' => 'XOF',
            'locale' => 'fr',
        ];

        $tenant = $this->tenantService->createTenant($tenantData);

        $this->assertInstanceOf(Tenant::class, $tenant);
        $this->assertEquals('Mon Restaurant', $tenant->name);
        $this->assertEquals('mon-restaurant', $tenant->slug);
        $this->assertTrue($tenant->is_active);

        // Check default menu was created
        $this->assertCount(1, $tenant->menus);
        $this->assertEquals('Menu Principal', $tenant->menus->first()->name);
    }

    /** @test */
    public function it_generates_unique_slug_when_duplicate()
    {
        Tenant::create([
            'name' => 'Restaurant Test',
            'slug' => 'restaurant-test',
            'type' => TenantType::RESTAURANT->value,
            'currency' => 'XOF',
            'locale' => 'fr',
            'is_active' => true,
        ]);

        $tenant = $this->tenantService->createTenant([
            'name' => 'Restaurant Test',
            'type' => TenantType::RESTAURANT->value,
        ]);

        $this->assertEquals('restaurant-test-1', $tenant->slug);
    }

    /** @test */
    public function it_can_update_tenant_basic_info()
    {
        $tenant = Tenant::create([
            'name' => 'Original Name',
            'slug' => 'original-slug',
            'type' => TenantType::RESTAURANT->value,
            'currency' => 'XOF',
            'locale' => 'fr',
            'is_active' => true,
        ]);

        $updatedTenant = $this->tenantService->updateTenant($tenant, [
            'name' => 'Updated Name',
            'currency' => 'EUR',
            'locale' => 'en',
        ]);

        $this->assertEquals('Updated Name', $updatedTenant->name);
        $this->assertEquals('EUR', $updatedTenant->currency);
        $this->assertEquals('en', $updatedTenant->locale);
        $this->assertEquals('original-slug', $updatedTenant->slug); // Unchanged
    }

    /** @test */
    public function it_can_update_branding()
    {
        $tenant = Tenant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'type' => TenantType::RESTAURANT->value,
            'currency' => 'XOF',
            'locale' => 'fr',
            'is_active' => true,
            'branding' => ['primary_color' => '#FF0000'],
        ]);

        $updatedTenant = $this->tenantService->updateBranding($tenant, [
            'secondary_color' => '#00FF00',
            'heading_font' => 'Roboto',
        ]);

        $this->assertEquals('#FF0000', $updatedTenant->branding['primary_color']);
        $this->assertEquals('#00FF00', $updatedTenant->branding['secondary_color']);
        $this->assertEquals('Roboto', $updatedTenant->branding['heading_font']);
    }

    /** @test */
    public function it_can_update_logo_and_cover()
    {
        $tenant = Tenant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'type' => TenantType::RESTAURANT->value,
            'currency' => 'XOF',
            'locale' => 'fr',
            'is_active' => true,
        ]);

        $tenant = $this->tenantService->updateLogo($tenant, 'https://example.com/logo.png');
        $this->assertEquals('https://example.com/logo.png', $tenant->logo_url);

        $tenant = $this->tenantService->updateCover($tenant, 'https://example.com/cover.jpg');
        $this->assertEquals('https://example.com/cover.jpg', $tenant->cover_url);
    }

    /** @test */
    public function it_can_apply_theme()
    {
        $tenant = Tenant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'type' => TenantType::RESTAURANT->value,
            'currency' => 'XOF',
            'locale' => 'fr',
            'is_active' => true,
        ]);

        $theme = Theme::create([
            'name' => 'Wedding Theme',
            'slug' => 'wedding',
            'colors' => ['primary' => '#FFD700'],
            'fonts' => ['heading' => 'Playfair Display'],
            'is_active' => true,
        ]);

        $updatedTenant = $this->tenantService->applyTheme($tenant, $theme->id);

        $this->assertEquals($theme->id, $updatedTenant->theme_id);
        $this->assertNotNull($updatedTenant->theme);
    }

    /** @test */
    public function it_can_get_tenant_by_slug()
    {
        Tenant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'type' => TenantType::RESTAURANT->value,
            'currency' => 'XOF',
            'locale' => 'fr',
            'is_active' => true,
        ]);

        $tenant = $this->tenantService->getTenantBySlug('test-restaurant');

        $this->assertNotNull($tenant);
        $this->assertEquals('Test Restaurant', $tenant->name);
    }

    /** @test */
    public function it_returns_null_for_nonexistent_slug()
    {
        $tenant = $this->tenantService->getTenantBySlug('nonexistent');
        $this->assertNull($tenant);
    }

    /** @test */
    public function it_can_get_active_tenants()
    {
        Tenant::create([
            'name' => 'Active Restaurant',
            'slug' => 'active',
            'type' => TenantType::RESTAURANT->value,
            'currency' => 'XOF',
            'locale' => 'fr',
            'is_active' => true,
        ]);

        Tenant::create([
            'name' => 'Inactive Restaurant',
            'slug' => 'inactive',
            'type' => TenantType::RESTAURANT->value,
            'currency' => 'XOF',
            'locale' => 'fr',
            'is_active' => false,
        ]);

        $activeTenants = $this->tenantService->getActiveTenants();

        $this->assertCount(1, $activeTenants);
        $this->assertEquals('Active Restaurant', $activeTenants->first()->name);
    }

    /** @test */
    public function it_can_activate_and_deactivate_tenant()
    {
        $tenant = Tenant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'type' => TenantType::RESTAURANT->value,
            'currency' => 'XOF',
            'locale' => 'fr',
            'is_active' => true,
        ]);

        $tenant = $this->tenantService->deactivateTenant($tenant);
        $this->assertFalse($tenant->is_active);

        $tenant = $this->tenantService->activateTenant($tenant);
        $this->assertTrue($tenant->is_active);
    }

    /** @test */
    public function it_can_get_tenant_stats()
    {
        $tenant = Tenant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'type' => TenantType::RESTAURANT->value,
            'currency' => 'XOF',
            'locale' => 'fr',
            'is_active' => true,
        ]);

        $stats = $this->tenantService->getTenantStats($tenant);

        $this->assertArrayHasKey('users_count', $stats);
        $this->assertArrayHasKey('tables_count', $stats);
        $this->assertArrayHasKey('menus_count', $stats);
        $this->assertArrayHasKey('dishes_count', $stats);
        $this->assertArrayHasKey('orders_count', $stats);
        $this->assertArrayHasKey('orders_today', $stats);
        $this->assertArrayHasKey('revenue_today', $stats);
    }

    /** @test */
    public function it_can_get_tenant_with_details()
    {
        $tenant = Tenant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'type' => TenantType::RESTAURANT->value,
            'currency' => 'XOF',
            'locale' => 'fr',
            'is_active' => true,
        ]);

        Menu::create([
            'tenant_id' => $tenant->id,
            'title' => 'Test Menu',
            'active' => true,
        ]);

        $tenantWithDetails = $this->tenantService->getTenantWithDetails($tenant->id);

        $this->assertNotNull($tenantWithDetails);
        $this->assertTrue($tenantWithDetails->relationLoaded('menus'));
        $this->assertTrue($tenantWithDetails->relationLoaded('tables'));
        $this->assertTrue($tenantWithDetails->relationLoaded('users'));
    }
}
