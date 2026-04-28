<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Role;
use App\Models\Table;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant1;

    protected Tenant $tenant2;

    protected User $superAdmin;

    protected User $admin;

    protected User $chef;

    protected User $serveur;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'SUPER_ADMIN', 'label' => 'Super Admin']);
        Role::create(['name' => 'ADMIN', 'label' => 'Admin']);
        Role::create(['name' => 'CHEF', 'label' => 'Chef']);
        Role::create(['name' => 'SERVEUR', 'label' => 'Serveur']);
        Role::create(['name' => 'CLIENT', 'label' => 'Client']);

        // Create tenants
        $this->tenant1 = Tenant::create([
            'name' => 'Restaurant 1',
            'slug' => 'restaurant-1',
            'type' => 'restaurant',
            'currency' => 'XOF',
            'locale' => 'fr',
            'is_active' => true,
        ]);

        $this->tenant2 = Tenant::create([
            'name' => 'Restaurant 2',
            'slug' => 'restaurant-2',
            'type' => 'restaurant',
            'currency' => 'XOF',
            'locale' => 'fr',
            'is_active' => true,
        ]);

        // Create users
        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'super@admin.com',
            'password' => bcrypt('password'),
            'role' => 'SUPER_ADMIN',
        ]);

        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@restaurant1.com',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant1->id,
            'role' => 'ADMIN',
        ]);

        $this->chef = User::create([
            'name' => 'Chef',
            'email' => 'chef@restaurant1.com',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant1->id,
            'role' => 'CHEF',
        ]);

        $this->serveur = User::create([
            'name' => 'Serveur',
            'email' => 'serveur@restaurant1.com',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant1->id,
            'role' => 'SERVEUR',
        ]);

        // Create menu for tenant 1
        Menu::create([
            'tenant_id' => $this->tenant1->id,
            'title' => 'Menu Principal',
            'active' => true,
        ]);
    }

    /** @test */
    public function super_admin_can_access_super_admin_dashboard()
    {
        $response = $this->actingAs($this->superAdmin)
            ->get('/superadmin/dashboard');

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_cannot_access_super_admin_dashboard()
    {
        $response = $this->actingAs($this->admin)
            ->get('/superadmin/dashboard');

        $response->assertStatus(403);
    }

    /** @test */
    public function super_admin_can_access_any_tenant_dashboard()
    {
        $response = $this->actingAs($this->superAdmin)
            ->get("/admin/{$this->tenant1->slug}/dashboard");

        $response->assertStatus(200);

        $response = $this->actingAs($this->superAdmin)
            ->get("/admin/{$this->tenant2->slug}/dashboard");

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_access_own_tenant_dashboard()
    {
        $response = $this->actingAs($this->admin)
            ->get("/admin/{$this->tenant1->slug}/dashboard");

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_cannot_access_other_tenant_dashboard()
    {
        // Admin of tenant1 trying to access tenant2
        $response = $this->actingAs($this->admin)
            ->get("/admin/{$this->tenant2->slug}/dashboard");

        $response->assertStatus(403);
    }

    /** @test */
    public function chef_can_access_kds()
    {
        // Create a table for the KDS route
        Table::create([
            'tenant_id' => $this->tenant1->id,
            'code' => 'T01',
            'label' => 'Table 1',
            'capacity' => 4,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->chef)
            ->get("/kds/{$this->tenant1->slug}");

        $response->assertStatus(200);
    }

    /** @test */
    public function serveur_cannot_modify_menu()
    {
        $menu = Menu::where('tenant_id', $this->tenant1->id)->first();

        $response = $this->actingAs($this->serveur)
            ->post("/admin/{$this->tenant1->slug}/menus", [
                'title' => 'New Menu',
            ]);

        // Should be forbidden or redirect
        $this->assertTrue(in_array($response->status(), [302, 403]));
    }

    /** @test */
    public function admin_can_create_menu()
    {
        $response = $this->actingAs($this->admin)
            ->post("/admin/{$this->tenant1->slug}/menus", [
                'title' => 'New Menu',
                'active' => true,
            ]);

        $response->assertStatus(302); // Redirect after create
        $this->assertDatabaseHas('menus', [
            'title' => 'New Menu',
            'tenant_id' => $this->tenant1->id,
        ]);
    }

    /** @test */
    public function super_admin_can_access_all_tenants_list()
    {
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
                'type' => 'restaurant',
                'currency' => 'XOF',
                'locale' => 'fr',
            ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('tenants', [
            'name' => 'New Restaurant',
            'slug' => 'new-restaurant',
        ]);
    }

    /** @test */
    public function admin_cannot_create_tenant()
    {
        $response = $this->actingAs($this->admin)
            ->post('/superadmin/tenants', [
                'name' => 'New Restaurant',
                'slug' => 'new-restaurant',
                'type' => 'restaurant',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function super_admin_can_manage_users()
    {
        $response = $this->actingAs($this->superAdmin)
            ->get('/superadmin/users');

        $response->assertStatus(200);
    }

    /** @test */
    public function tenant_isolation_prevents_data_leakage()
    {
        // Create tables for each tenant
        $table1 = Table::create([
            'tenant_id' => $this->tenant1->id,
            'code' => 'T01',
            'label' => 'Table 1 - Restaurant 1',
            'capacity' => 4,
            'is_active' => true,
        ]);

        $table2 = Table::create([
            'tenant_id' => $this->tenant2->id,
            'code' => 'T02',
            'label' => 'Table 1 - Restaurant 2',
            'capacity' => 4,
            'is_active' => true,
        ]);

        // Admin of tenant1 should only see tenant1's tables
        $response = $this->actingAs($this->admin)
            ->get("/admin/{$this->tenant1->slug}/tables");

        $response->assertStatus(200);
        $response->assertSee('Table 1 - Restaurant 1');
        $response->assertDontSee('Table 1 - Restaurant 2');
    }
}
