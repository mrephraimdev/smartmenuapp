<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Role;
use App\Models\Menu;
use App\Models\Category;
use App\Models\Dish;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MenuCrudTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $admin;
    protected User $client;
    protected Menu $menu;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Create roles
        Role::create(['name' => 'SUPER_ADMIN', 'label' => 'Super Admin']);
        Role::create(['name' => 'ADMIN', 'label' => 'Admin']);
        Role::create(['name' => 'CLIENT', 'label' => 'Client']);

        // Create tenant
        $this->tenant = Tenant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'type' => 'RESTAURANT',
            'currency' => 'XOF',
            'locale' => 'fr',
            'is_active' => true,
        ]);

        // Create admin user
        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant->id,
        ]);
        $this->admin->roles()->attach(Role::where('name', 'ADMIN')->first());

        // Create client user
        $this->client = User::create([
            'name' => 'Client',
            'email' => 'client@test.com',
            'password' => bcrypt('password'),
        ]);
        $this->client->roles()->attach(Role::where('name', 'CLIENT')->first());

        // Create menu structure
        $this->menu = Menu::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Menu Principal',
            'active' => true,
        ]);

        $this->category = Category::create([
            'menu_id' => $this->menu->id,
            'name' => 'Plats Principaux',
            'sort_order' => 1,
        ]);
    }

    /** @test */
    public function admin_can_view_menus_page()
    {
        $response = $this->actingAs($this->admin)
            ->get("/admin/{$this->tenant->slug}/menus");

        $response->assertStatus(200);
        $response->assertSee('Menu Principal');
    }

    /** @test */
    public function admin_can_create_menu()
    {
        $response = $this->actingAs($this->admin)
            ->post("/admin/{$this->tenant->slug}/menus", [
                'title' => 'Menu du Soir',
                'active' => true,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('menus', [
            'title' => 'Menu du Soir',
            'tenant_id' => $this->tenant->id,
            'active' => true,
        ]);
    }

    /** @test */
    public function admin_can_create_category()
    {
        $response = $this->actingAs($this->admin)
            ->post("/admin/{$this->tenant->slug}/menus/{$this->menu->id}/categories", [
                'name' => 'Entrées',
                'sort_order' => 0,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('categories', [
            'name' => 'Entrées',
            'menu_id' => $this->menu->id,
        ]);
    }

    /** @test */
    public function admin_can_create_dish()
    {
        $response = $this->actingAs($this->admin)
            ->post("/admin/{$this->tenant->slug}/categories/{$this->category->id}/dishes", [
                'name' => 'Poulet Braisé',
                'description' => 'Délicieux poulet grillé',
                'price_base' => 5000,
                'active' => true,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('dishes', [
            'name' => 'Poulet Braisé',
            'category_id' => $this->category->id,
            'tenant_id' => $this->tenant->id,
            'price_base' => 5000,
        ]);
    }

    /** @test */
    public function admin_can_create_dish_with_allergens_and_tags()
    {
        $response = $this->actingAs($this->admin)
            ->post("/admin/{$this->tenant->slug}/categories/{$this->category->id}/dishes", [
                'name' => 'Pizza Margherita',
                'price_base' => 4500,
                'allergens' => ['gluten', 'lactose'],
                'tags' => ['végétarien', 'populaire'],
                'active' => true,
            ]);

        $response->assertRedirect();

        $dish = Dish::where('name', 'Pizza Margherita')->first();

        $this->assertNotNull($dish);
        $this->assertContains('gluten', $dish->allergens);
        $this->assertContains('lactose', $dish->allergens);
        $this->assertContains('végétarien', $dish->tags);
    }

    /** @test */
    public function admin_can_update_dish()
    {
        $dish = Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'Original Name',
            'price_base' => 5000,
            'active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->put("/admin/{$this->tenant->slug}/dishes/{$dish->id}", [
                'name' => 'Updated Name',
                'price_base' => 6000,
                'description' => 'New description',
            ]);

        $response->assertRedirect();

        $dish->refresh();
        $this->assertEquals('Updated Name', $dish->name);
        $this->assertEquals(6000, $dish->price_base);
        $this->assertEquals('New description', $dish->description);
    }

    /** @test */
    public function admin_can_toggle_dish_availability()
    {
        $dish = Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'Test Dish',
            'price_base' => 5000,
            'active' => true,
        ]);

        // Disable
        $response = $this->actingAs($this->admin)
            ->post("/admin/{$this->tenant->slug}/dishes/{$dish->id}/toggle");

        $response->assertRedirect();

        $dish->refresh();
        $this->assertFalse($dish->active);

        // Enable
        $response = $this->actingAs($this->admin)
            ->post("/admin/{$this->tenant->slug}/dishes/{$dish->id}/toggle");

        $dish->refresh();
        $this->assertTrue($dish->active);
    }

    /** @test */
    public function admin_can_delete_dish()
    {
        $dish = Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'To Delete',
            'price_base' => 5000,
            'active' => true,
        ]);

        $dishId = $dish->id;

        $response = $this->actingAs($this->admin)
            ->delete("/admin/{$this->tenant->slug}/dishes/{$dish->id}");

        $response->assertRedirect();

        $this->assertDatabaseMissing('dishes', ['id' => $dishId]);
    }

    /** @test */
    public function client_cannot_create_dish()
    {
        $response = $this->actingAs($this->client)
            ->post("/admin/{$this->tenant->slug}/categories/{$this->category->id}/dishes", [
                'name' => 'New Dish',
                'price_base' => 5000,
                'active' => true,
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function dish_requires_name()
    {
        $response = $this->actingAs($this->admin)
            ->post("/admin/{$this->tenant->slug}/categories/{$this->category->id}/dishes", [
                'price_base' => 5000,
                'active' => true,
            ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function dish_requires_positive_price()
    {
        $response = $this->actingAs($this->admin)
            ->post("/admin/{$this->tenant->slug}/categories/{$this->category->id}/dishes", [
                'name' => 'Test Dish',
                'price_base' => -100,
                'active' => true,
            ]);

        $response->assertSessionHasErrors('price_base');
    }

    /** @test */
    public function admin_can_view_dishes_list()
    {
        Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'Dish 1',
            'price_base' => 5000,
            'active' => true,
        ]);

        Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'Dish 2',
            'price_base' => 6000,
            'active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/admin/{$this->tenant->slug}/categories/{$this->category->id}/dishes");

        $response->assertStatus(200);
        $response->assertSee('Dish 1');
        $response->assertSee('Dish 2');
    }

    /** @test */
    public function admin_can_update_menu()
    {
        $response = $this->actingAs($this->admin)
            ->patch("/admin/{$this->tenant->slug}/menus/{$this->menu->id}", [
                'title' => 'Updated Menu Title',
                'active' => false,
            ]);

        $response->assertRedirect();

        $this->menu->refresh();
        $this->assertEquals('Updated Menu Title', $this->menu->title);
        $this->assertFalse((bool) $this->menu->active);
    }

    /** @test */
    public function admin_can_delete_menu()
    {
        $menuToDelete = Menu::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Menu to Delete',
            'active' => true,
        ]);

        $menuId = $menuToDelete->id;

        $response = $this->actingAs($this->admin)
            ->delete("/admin/{$this->tenant->slug}/menus/{$menuToDelete->id}");

        $response->assertRedirect();

        $this->assertDatabaseMissing('menus', ['id' => $menuId]);
    }
}
