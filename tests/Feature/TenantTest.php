<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Role;
use App\Models\Theme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TenantTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected Theme $defaultTheme;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Create roles
        Role::create(['name' => 'SUPER_ADMIN', 'label' => 'Super Admin']);
        Role::create(['name' => 'ADMIN', 'label' => 'Admin']);

        // Create default theme
        $this->defaultTheme = Theme::create([
            'name' => 'Default Theme',
            'slug' => 'default',
            'colors' => ['primary' => '#000000'],
            'fonts' => ['heading' => 'Arial'],
            'is_default' => true,
            'is_active' => true,
        ]);

        // Create super admin
        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'super@admin.com',
            'password' => bcrypt('password'),
        ]);
        $this->superAdmin->roles()->attach(Role::where('name', 'SUPER_ADMIN')->first());
    }

    /** @test */
    public function super_admin_can_view_tenants_list()
    {
        Tenant::create([
            'name' => 'Restaurant 1',
            'slug' => 'restaurant-1',
            'type' => 'RESTAURANT',
            'currency' => 'XOF',
            'locale' => 'fr',
            'is_active' => true,
        ]);

        Tenant::create([
            'name' => 'Restaurant 2',
            'slug' => 'restaurant-2',
            'type' => 'RESTAURANT',
            'currency' => 'XOF',
            'locale' => 'fr',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->get('/superadmin/tenants');

        $response->assertStatus(200);
        $response->assertSee('Restaurant 1');
        $response->assertSee('Restaurant 2');
    }

    /** @test */
    public function super_admin_can_create_tenant()
    {
        $response = $this->actingAs($this->superAdmin)
            ->post('/superadmin/tenants', [
                'name' => 'New Restaurant',
                'slug' => 'new-restaurant',
                'type' => 'RESTAURANT',
                'currency' => 'XOF',
                'locale' => 'fr',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tenants', [
            'name' => 'New Restaurant',
            'slug' => 'new-restaurant',
        ]);
    }

    /** @test */
    public function tenant_slug_is_auto_generated_if_not_provided()
    {
        $response = $this->actingAs($this->superAdmin)
            ->post('/superadmin/tenants', [
                'name' => 'Mon Beau Restaurant',
                'type' => 'RESTAURANT',
                'currency' => 'XOF',
                'locale' => 'fr',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tenants', [
            'name' => 'Mon Beau Restaurant',
            'slug' => 'mon-beau-restaurant',
        ]);
    }

    /** @test */
    public function duplicate_slug_generates_unique_slug()
    {
        Tenant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'type' => 'RESTAURANT',
            'currency' => 'XOF',
            'locale' => 'fr',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->post('/superadmin/tenants', [
                'name' => 'Test Restaurant',
                'type' => 'RESTAURANT',
                'currency' => 'XOF',
                'locale' => 'fr',
            ]);

        $response->assertRedirect();

        // Should have created with unique slug
        $this->assertDatabaseHas('tenants', [
            'name' => 'Test Restaurant',
        ]);

        $tenants = Tenant::where('name', 'Test Restaurant')->get();
        $this->assertEquals(2, $tenants->count());
        $this->assertNotEquals($tenants[0]->slug, $tenants[1]->slug);
    }

    /** @test */
    public function super_admin_can_update_tenant()
    {
        $tenant = Tenant::create([
            'name' => 'Original Name',
            'slug' => 'original-slug',
            'type' => 'RESTAURANT',
            'currency' => 'XOF',
            'locale' => 'fr',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->put("/superadmin/tenants/{$tenant->id}", [
                'name' => 'Updated Name',
                'slug' => 'original-slug',
                'type' => 'RESTAURANT',
                'currency' => 'EUR',
                'locale' => 'en',
            ]);

        $response->assertRedirect();

        $tenant->refresh();
        $this->assertEquals('Updated Name', $tenant->name);
        $this->assertEquals('EUR', $tenant->currency);
        $this->assertEquals('en', $tenant->locale);
    }

    /** @test */
    public function super_admin_can_apply_theme_to_tenant()
    {
        $tenant = Tenant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'type' => 'RESTAURANT',
            'currency' => 'XOF',
            'locale' => 'fr',
            'is_active' => true,
        ]);

        $weddingTheme = Theme::create([
            'name' => 'Wedding Theme',
            'slug' => 'wedding',
            'colors' => ['primary' => '#FFD700'],
            'fonts' => ['heading' => 'Playfair Display'],
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->put("/superadmin/tenants/{$tenant->id}", [
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'type' => $tenant->type,
                'currency' => $tenant->currency,
                'locale' => $tenant->locale,
                'theme_id' => $weddingTheme->id,
            ]);

        $response->assertRedirect();

        $tenant->refresh();
        $this->assertEquals($weddingTheme->id, $tenant->theme_id);
    }

    /** @test */
    public function super_admin_can_upload_tenant_logo()
    {
        $tenant = Tenant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'type' => 'RESTAURANT',
            'currency' => 'XOF',
            'locale' => 'fr',
            'is_active' => true,
        ]);

        $logo = UploadedFile::fake()->image('logo.png', 200, 200);

        $response = $this->actingAs($this->superAdmin)
            ->put("/superadmin/tenants/{$tenant->id}", [
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'type' => $tenant->type,
                'currency' => $tenant->currency,
                'locale' => $tenant->locale,
                'logo' => $logo,
            ]);

        $response->assertRedirect();

        $tenant->refresh();
        $this->assertNotNull($tenant->logo_url);
    }

    /** @test */
    public function super_admin_can_delete_tenant()
    {
        $tenant = Tenant::create([
            'name' => 'To Delete',
            'slug' => 'to-delete',
            'type' => 'RESTAURANT',
            'currency' => 'XOF',
            'locale' => 'fr',
            'is_active' => true,
        ]);

        $tenantId = $tenant->id;

        $response = $this->actingAs($this->superAdmin)
            ->delete("/superadmin/tenants/{$tenant->id}");

        $response->assertRedirect();

        $this->assertDatabaseMissing('tenants', ['id' => $tenantId]);
    }

    /** @test */
    public function super_admin_can_activate_deactivate_tenant()
    {
        $tenant = Tenant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'type' => 'RESTAURANT',
            'currency' => 'XOF',
            'locale' => 'fr',
            'is_active' => true,
        ]);

        // Deactivate
        $response = $this->actingAs($this->superAdmin)
            ->put("/superadmin/tenants/{$tenant->id}", [
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'type' => $tenant->type,
                'currency' => $tenant->currency,
                'locale' => $tenant->locale,
                'is_active' => false,
            ]);

        $response->assertRedirect();
        $tenant->refresh();
        $this->assertFalse((bool) $tenant->is_active);
    }

    /** @test */
    public function tenant_type_validation_works()
    {
        $response = $this->actingAs($this->superAdmin)
            ->post('/superadmin/tenants', [
                'name' => 'Test Restaurant',
                'type' => 'INVALID_TYPE',
                'currency' => 'XOF',
                'locale' => 'fr',
            ]);

        $response->assertSessionHasErrors('type');
    }

    /** @test */
    public function tenant_requires_name()
    {
        $response = $this->actingAs($this->superAdmin)
            ->post('/superadmin/tenants', [
                'type' => 'RESTAURANT',
                'currency' => 'XOF',
                'locale' => 'fr',
            ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function super_admin_can_view_tenant_details()
    {
        $tenant = Tenant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'type' => 'RESTAURANT',
            'currency' => 'XOF',
            'locale' => 'fr',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->get("/superadmin/tenants/{$tenant->id}");

        $response->assertStatus(200);
        $response->assertSee('Test Restaurant');
    }

    /** @test */
    public function default_menu_is_created_with_new_tenant()
    {
        $response = $this->actingAs($this->superAdmin)
            ->post('/superadmin/tenants', [
                'name' => 'New Restaurant',
                'slug' => 'new-restaurant',
                'type' => 'RESTAURANT',
                'currency' => 'XOF',
                'locale' => 'fr',
            ]);

        $response->assertRedirect();

        $tenant = Tenant::where('slug', 'new-restaurant')->first();
        $this->assertNotNull($tenant);

        // Check that a default menu was created
        $this->assertTrue($tenant->menus()->exists());
    }
}
