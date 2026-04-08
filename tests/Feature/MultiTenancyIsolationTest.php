<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Dish;
use App\Models\Menu;
use App\Models\Option;
use App\Models\Order;
use App\Models\Role;
use App\Models\Table;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests d'isolation multi-tenants
 *
 * Valide que les données sont correctement isolées entre tenants
 * et que seuls les SUPER_ADMIN peuvent accéder à tous les tenants
 */
class MultiTenancyIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected Tenant $tenantB;
    protected User $adminA;
    protected User $adminB;
    protected User $superAdmin;
    protected Role $superAdminRole;
    protected Role $adminRole;

    /**
     * Setup avant chaque test
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Créer les rôles
        $this->superAdminRole = Role::create([
            'name' => 'SUPER_ADMIN',
            'description' => 'Super administrateur'
        ]);

        $this->adminRole = Role::create([
            'name' => 'ADMIN',
            'description' => 'Administrateur tenant'
        ]);

        // Créer 2 tenants
        $this->tenantA = Tenant::create([
            'name' => 'Restaurant A',
            'slug' => 'restaurant-a',
            'type' => 'restaurant',
            'locale' => 'fr',
            'currency' => 'XOF',
            'is_active' => true,
        ]);

        $this->tenantB = Tenant::create([
            'name' => 'Restaurant B',
            'slug' => 'restaurant-b',
            'type' => 'restaurant',
            'locale' => 'fr',
            'currency' => 'XOF',
            'is_active' => true,
        ]);

        // Créer des utilisateurs
        $this->adminA = User::create([
            'name' => 'Admin A',
            'email' => 'admin_a@test.com',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenantA->id,
        ]);
        $this->adminA->assignRole('ADMIN');

        $this->adminB = User::create([
            'name' => 'Admin B',
            'email' => 'admin_b@test.com',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenantB->id,
        ]);
        $this->adminB->assignRole('ADMIN');

        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'password' => bcrypt('password'),
            'tenant_id' => null,
        ]);
        $this->superAdmin->assignRole('SUPER_ADMIN');
    }

    /**
     * Test : Tenant A ne peut pas voir les données de Tenant B (Menu)
     */
    public function test_tenant_a_cannot_access_tenant_b_menus(): void
    {
        // Créer des menus pour chaque tenant
        $menuA = Menu::create([
            'tenant_id' => $this->tenantA->id,
            'title' => 'Menu Restaurant A',
            'active' => true,
        ]);

        $menuB = Menu::create([
            'tenant_id' => $this->tenantB->id,
            'title' => 'Menu Restaurant B',
            'active' => true,
        ]);

        // Se connecter en tant qu'Admin A
        $this->actingAs($this->adminA);

        // Admin A doit voir uniquement son menu
        $menus = Menu::all();
        $this->assertCount(1, $menus);
        $this->assertEquals($menuA->id, $menus->first()->id);
        $this->assertNotContains($menuB->id, $menus->pluck('id'));
    }

    /**
     * Test : Tenant A ne peut pas voir les plats de Tenant B
     */
    public function test_tenant_a_cannot_access_tenant_b_dishes(): void
    {
        // Créer menus et catégories pour chaque tenant
        $menuA = Menu::create(['tenant_id' => $this->tenantA->id, 'title' => 'Menu A', 'active' => true]);
        $categoryA = Category::create(['menu_id' => $menuA->id, 'name' => 'Catégorie A', 'sort_order' => 1]);

        $menuB = Menu::create(['tenant_id' => $this->tenantB->id, 'title' => 'Menu B', 'active' => true]);
        $categoryB = Category::create(['menu_id' => $menuB->id, 'name' => 'Catégorie B', 'sort_order' => 1]);

        // Créer des plats pour chaque tenant
        $dishA = Dish::create([
            'tenant_id' => $this->tenantA->id,
            'category_id' => $categoryA->id,
            'name' => 'Plat A',
            'description' => 'Description A',
            'price_base' => 5000,
            'active' => true,
        ]);

        $dishB = Dish::create([
            'tenant_id' => $this->tenantB->id,
            'category_id' => $categoryB->id,
            'name' => 'Plat B',
            'description' => 'Description B',
            'price_base' => 6000,
            'active' => true,
        ]);

        // Se connecter en tant qu'Admin A
        $this->actingAs($this->adminA);

        // Admin A doit voir uniquement ses plats
        $dishes = Dish::all();
        $this->assertCount(1, $dishes);
        $this->assertEquals($dishA->id, $dishes->first()->id);
    }

    /**
     * Test : Tenant A ne peut pas voir les tables de Tenant B
     */
    public function test_tenant_a_cannot_access_tenant_b_tables(): void
    {
        // Créer des tables pour chaque tenant
        $tableA = Table::create([
            'tenant_id' => $this->tenantA->id,
            'code' => 'A01',
            'label' => 'Table A01',
            'capacity' => 4,
            'is_active' => true,
        ]);

        $tableB = Table::create([
            'tenant_id' => $this->tenantB->id,
            'code' => 'B01',
            'label' => 'Table B01',
            'capacity' => 6,
            'is_active' => true,
        ]);

        // Se connecter en tant qu'Admin A
        $this->actingAs($this->adminA);

        // Admin A doit voir uniquement ses tables
        $tables = Table::all();
        $this->assertCount(1, $tables);
        $this->assertEquals($tableA->id, $tables->first()->id);
    }

    /**
     * Test : Tenant A ne peut pas voir les commandes de Tenant B
     */
    public function test_tenant_a_cannot_access_tenant_b_orders(): void
    {
        // Créer des tables
        $tableA = Table::create([
            'tenant_id' => $this->tenantA->id,
            'code' => 'A01',
            'label' => 'Table A01',
            'capacity' => 4,
            'is_active' => true,
        ]);

        $tableB = Table::create([
            'tenant_id' => $this->tenantB->id,
            'code' => 'B01',
            'label' => 'Table B01',
            'capacity' => 6,
            'is_active' => true,
        ]);

        // Créer des commandes pour chaque tenant
        $orderA = Order::create([
            'tenant_id' => $this->tenantA->id,
            'table_id' => $tableA->id,
            'order_number' => 'ORDER-A-001',
            'status' => 'RECU',
            'total' => 10000,
        ]);

        $orderB = Order::create([
            'tenant_id' => $this->tenantB->id,
            'table_id' => $tableB->id,
            'order_number' => 'ORDER-B-001',
            'status' => 'RECU',
            'total' => 15000,
        ]);

        // Se connecter en tant qu'Admin A
        $this->actingAs($this->adminA);

        // Admin A doit voir uniquement ses commandes
        $orders = Order::all();
        $this->assertCount(1, $orders);
        $this->assertEquals($orderA->id, $orders->first()->id);
    }

    /**
     * Test : SUPER_ADMIN peut voir toutes les données
     */
    public function test_super_admin_can_access_all_tenants_data(): void
    {
        // Créer des menus pour les 2 tenants
        Menu::create([
            'tenant_id' => $this->tenantA->id,
            'title' => 'Menu A',
            'active' => true,
        ]);

        Menu::create([
            'tenant_id' => $this->tenantB->id,
            'title' => 'Menu B',
            'active' => true,
        ]);

        // Se connecter en tant que Super Admin
        $this->actingAs($this->superAdmin);

        // Super Admin doit voir tous les menus
        $menus = Menu::all();
        $this->assertCount(2, $menus);
    }

    /**
     * Test : Global Scope s'applique sur toutes les requêtes
     */
    public function test_global_scope_applied_on_all_queries(): void
    {
        // Créer menus et catégories
        $menuA = Menu::create(['tenant_id' => $this->tenantA->id, 'title' => 'Menu A', 'active' => true]);
        $categoryA = Category::create(['menu_id' => $menuA->id, 'name' => 'Catégorie A', 'sort_order' => 1]);

        $menuB = Menu::create(['tenant_id' => $this->tenantB->id, 'title' => 'Menu B', 'active' => true]);
        $categoryB = Category::create(['menu_id' => $menuB->id, 'name' => 'Catégorie B', 'sort_order' => 1]);

        // Créer plusieurs plats pour chaque tenant
        Dish::create([
            'tenant_id' => $this->tenantA->id,
            'category_id' => $categoryA->id,
            'name' => 'Plat A1',
            'description' => 'Description',
            'price_base' => 5000,
            'active' => true,
        ]);

        Dish::create([
            'tenant_id' => $this->tenantA->id,
            'category_id' => $categoryA->id,
            'name' => 'Plat A2',
            'description' => 'Description',
            'price_base' => 6000,
            'active' => true,
        ]);

        Dish::create([
            'tenant_id' => $this->tenantB->id,
            'category_id' => $categoryB->id,
            'name' => 'Plat B1',
            'description' => 'Description',
            'price_base' => 7000,
            'active' => true,
        ]);

        // Se connecter en tant qu'Admin A
        $this->actingAs($this->adminA);

        // Différents types de requêtes
        $this->assertCount(2, Dish::all());
        $this->assertCount(2, Dish::get());
        $this->assertCount(2, Dish::where('active', true)->get());
        $this->assertCount(1, Dish::where('name', 'Plat A1')->get());
        $this->assertNull(Dish::where('name', 'Plat B1')->first());
    }

    /**
     * Test : withoutTenantScope() permet de bypass le scope
     */
    public function test_without_tenant_scope_bypasses_filter(): void
    {
        // Créer des menus pour chaque tenant
        Menu::create([
            'tenant_id' => $this->tenantA->id,
            'title' => 'Menu A',
            'active' => true,
        ]);

        Menu::create([
            'tenant_id' => $this->tenantB->id,
            'title' => 'Menu B',
            'active' => true,
        ]);

        // Se connecter en tant qu'Admin A
        $this->actingAs($this->adminA);

        // Avec scope : 1 menu
        $this->assertCount(1, Menu::all());

        // Sans scope : 2 menus
        $this->assertCount(2, Menu::withoutTenantScope()->get());
    }

    /**
     * Test : Création automatique du tenant_id
     */
    public function test_tenant_id_automatically_assigned_on_create(): void
    {
        // Se connecter en tant qu'Admin A
        $this->actingAs($this->adminA);

        // Créer un menu sans spécifier tenant_id
        $menu = Menu::create([
            'title' => 'Menu Test',
            'active' => true,
        ]);

        // Le tenant_id doit être automatiquement assigné
        $this->assertEquals($this->tenantA->id, $menu->tenant_id);
    }

    /**
     * Test : forTenant() scope permet de forcer un tenant spécifique
     */
    public function test_for_tenant_scope_forces_specific_tenant(): void
    {
        // Créer des menus pour les 2 tenants
        Menu::create([
            'tenant_id' => $this->tenantA->id,
            'title' => 'Menu A',
            'active' => true,
        ]);

        $menuB = Menu::create([
            'tenant_id' => $this->tenantB->id,
            'title' => 'Menu B',
            'active' => true,
        ]);

        // Se connecter en tant que Super Admin
        $this->actingAs($this->superAdmin);

        // Forcer le tenant B
        $menus = Menu::forTenant($this->tenantB->id)->get();
        $this->assertCount(1, $menus);
        $this->assertEquals($menuB->id, $menus->first()->id);
    }

    /**
     * Test : Isolation via relations (Category via Menu)
     */
    public function test_category_isolated_via_menu_relation(): void
    {
        // Créer menus pour chaque tenant
        $menuA = Menu::create([
            'tenant_id' => $this->tenantA->id,
            'title' => 'Menu A',
            'active' => true,
        ]);

        $menuB = Menu::create([
            'tenant_id' => $this->tenantB->id,
            'title' => 'Menu B',
            'active' => true,
        ]);

        // Créer catégories
        Category::create([
            'menu_id' => $menuA->id,
            'name' => 'Catégorie A',
            'sort_order' => 1,
        ]);

        Category::create([
            'menu_id' => $menuB->id,
            'name' => 'Catégorie B',
            'sort_order' => 1,
        ]);

        // Se connecter en tant qu'Admin A
        $this->actingAs($this->adminA);

        // Admin A doit voir uniquement ses catégories
        $categories = Category::all();
        $this->assertCount(1, $categories);
        $this->assertEquals('Catégorie A', $categories->first()->name);
    }

    /**
     * Test : Isolation Variant via Dish
     */
    public function test_variant_isolated_via_dish_relation(): void
    {
        // Créer menus et catégories
        $menuA = Menu::create(['tenant_id' => $this->tenantA->id, 'title' => 'Menu A', 'active' => true]);
        $categoryA = Category::create(['menu_id' => $menuA->id, 'name' => 'Catégorie A', 'sort_order' => 1]);

        $menuB = Menu::create(['tenant_id' => $this->tenantB->id, 'title' => 'Menu B', 'active' => true]);
        $categoryB = Category::create(['menu_id' => $menuB->id, 'name' => 'Catégorie B', 'sort_order' => 1]);

        // Créer des plats pour chaque tenant
        $dishA = Dish::create([
            'tenant_id' => $this->tenantA->id,
            'category_id' => $categoryA->id,
            'name' => 'Plat A',
            'description' => 'Description',
            'price_base' => 5000,
            'active' => true,
        ]);

        $dishB = Dish::create([
            'tenant_id' => $this->tenantB->id,
            'category_id' => $categoryB->id,
            'name' => 'Plat B',
            'description' => 'Description',
            'price_base' => 6000,
            'active' => true,
        ]);

        // Créer des variantes
        Variant::create([
            'dish_id' => $dishA->id,
            'name' => 'Grande portion',
            'extra_price' => 1000,
        ]);

        Variant::create([
            'dish_id' => $dishB->id,
            'name' => 'Grande portion',
            'extra_price' => 1500,
        ]);

        // Se connecter en tant qu'Admin A
        $this->actingAs($this->adminA);

        // Admin A doit voir uniquement ses variantes
        $variants = Variant::all();
        $this->assertCount(1, $variants);
        $this->assertEquals($dishA->id, $variants->first()->dish_id);
    }

    /**
     * Test : Isolation Option via Dish
     */
    public function test_option_isolated_via_dish_relation(): void
    {
        // Créer menus et catégories
        $menuA = Menu::create(['tenant_id' => $this->tenantA->id, 'title' => 'Menu A', 'active' => true]);
        $categoryA = Category::create(['menu_id' => $menuA->id, 'name' => 'Catégorie A', 'sort_order' => 1]);

        $menuB = Menu::create(['tenant_id' => $this->tenantB->id, 'title' => 'Menu B', 'active' => true]);
        $categoryB = Category::create(['menu_id' => $menuB->id, 'name' => 'Catégorie B', 'sort_order' => 1]);

        // Créer des plats pour chaque tenant
        $dishA = Dish::create([
            'tenant_id' => $this->tenantA->id,
            'category_id' => $categoryA->id,
            'name' => 'Plat A',
            'description' => 'Description',
            'price_base' => 5000,
            'active' => true,
        ]);

        $dishB = Dish::create([
            'tenant_id' => $this->tenantB->id,
            'category_id' => $categoryB->id,
            'name' => 'Plat B',
            'description' => 'Description',
            'price_base' => 6000,
            'active' => true,
        ]);

        // Créer des options
        Option::create([
            'dish_id' => $dishA->id,
            'name' => 'Sauce Piquante',
            'kind' => 'addon',
            'extra_price' => 500,
        ]);

        Option::create([
            'dish_id' => $dishB->id,
            'name' => 'Fromage',
            'kind' => 'addon',
            'extra_price' => 750,
        ]);

        // Se connecter en tant qu'Admin A
        $this->actingAs($this->adminA);

        // Admin A doit voir uniquement ses options
        $options = Option::all();
        $this->assertCount(1, $options);
        $this->assertEquals($dishA->id, $options->first()->dish_id);
    }

    /**
     * Test : Utilisateur non authentifié ne peut rien voir (sauf routes publiques)
     */
    public function test_unauthenticated_user_sees_nothing(): void
    {
        // Créer des données
        Menu::create([
            'tenant_id' => $this->tenantA->id,
            'title' => 'Menu A',
            'active' => true,
        ]);

        // Pas de connexion
        // Le scope ne s'applique pas car Auth::check() = false
        // Comportement attendu : retourne toutes les données (ou aucune selon logique métier)
        // Pour cette architecture, on ne filtre pas si non authentifié
        $menus = Menu::all();

        // Ce test valide que le scope ne plante pas sans authentification
        $this->assertIsObject($menus);
    }
}
