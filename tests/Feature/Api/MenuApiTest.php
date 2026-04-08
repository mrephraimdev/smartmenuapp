<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Menu;
use App\Models\Category;
use App\Models\Dish;
use App\Models\Table;
use App\Models\Theme;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MenuApiTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Menu $menu;
    protected Category $category;
    protected Table $table;

    protected function setUp(): void
    {
        parent::setUp();

        // Create default theme
        $theme = Theme::create([
            'name' => 'Default Theme',
            'slug' => 'default',
            'colors' => ['primary' => '#C1440E', 'secondary' => '#1A1A1A'],
            'fonts' => ['heading' => 'Arial', 'body' => 'Helvetica'],
            'is_default' => true,
            'is_active' => true,
        ]);

        // Create tenant
        $this->tenant = Tenant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'type' => 'RESTAURANT',
            'currency' => 'XOF',
            'locale' => 'fr',
            'is_active' => true,
            'theme_id' => $theme->id,
        ]);

        // Create table
        $this->table = Table::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'T01',
            'label' => 'Table 1',
            'capacity' => 4,
            'is_active' => true,
        ]);

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
    public function api_returns_menu_for_valid_tenant()
    {
        Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'Poulet Braisé',
            'price_base' => 5000,
            'active' => true,
        ]);

        $response = $this->getJson("/api/menu?tenant={$this->tenant->id}&table={$this->table->code}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'menu' => [
                    'id',
                    'title',
                    'categories',
                ],
                'tenant',
                'table',
            ]);
    }

    /** @test */
    public function api_returns_dishes_with_correct_structure()
    {
        $dish = Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'Test Dish',
            'description' => 'A test dish',
            'price_base' => 5000,
            'allergens' => ['gluten', 'lactose'],
            'tags' => ['populaire'],
            'active' => true,
        ]);

        $dish->variants()->create(['name' => 'Small', 'price_modifier' => -500]);
        $dish->options()->create(['name' => 'Extra sauce', 'price' => 200]);

        $response = $this->getJson("/api/menu?tenant={$this->tenant->id}&table={$this->table->code}");

        $response->assertStatus(200);

        $dishes = $response->json('menu.categories.0.dishes');
        $this->assertNotEmpty($dishes);

        $testDish = collect($dishes)->firstWhere('name', 'Test Dish');
        $this->assertNotNull($testDish);
        $this->assertEquals(5000, $testDish['price_base']);
        $this->assertContains('gluten', $testDish['allergens']);
    }

    /** @test */
    public function api_only_returns_active_dishes()
    {
        Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'Active Dish',
            'price_base' => 5000,
            'active' => true,
        ]);

        Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'Inactive Dish',
            'price_base' => 6000,
            'active' => false,
        ]);

        $response = $this->getJson("/api/menu?tenant={$this->tenant->id}&table={$this->table->code}");

        $response->assertStatus(200);

        $dishes = $response->json('menu.categories.0.dishes');
        $dishNames = collect($dishes)->pluck('name')->toArray();

        $this->assertContains('Active Dish', $dishNames);
        $this->assertNotContains('Inactive Dish', $dishNames);
    }

    /** @test */
    public function api_includes_theme_in_response()
    {
        Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'Test Dish',
            'price_base' => 5000,
            'active' => true,
        ]);

        $response = $this->getJson("/api/menu?tenant={$this->tenant->id}&table={$this->table->code}");

        $response->assertStatus(200)
            ->assertJsonPath('tenant.theme_id', $this->tenant->theme_id);
    }

    /** @test */
    public function api_returns_404_for_invalid_tenant()
    {
        $response = $this->getJson("/api/menu?tenant=99999&table={$this->table->code}");

        $response->assertStatus(404);
    }

    /** @test */
    public function api_returns_404_for_invalid_table()
    {
        $response = $this->getJson("/api/menu?tenant={$this->tenant->id}&table=INVALID");

        $response->assertStatus(404);
    }

    /** @test */
    public function api_categories_are_sorted_by_order()
    {
        $category2 = Category::create([
            'menu_id' => $this->menu->id,
            'name' => 'Entrées',
            'sort_order' => 0, // Should appear first
        ]);

        $category3 = Category::create([
            'menu_id' => $this->menu->id,
            'name' => 'Desserts',
            'sort_order' => 2, // Should appear last
        ]);

        // Add a dish to each category
        Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id, // sort_order 1
            'name' => 'Plat Principal',
            'price_base' => 5000,
            'active' => true,
        ]);

        Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $category2->id, // sort_order 0
            'name' => 'Entrée',
            'price_base' => 2000,
            'active' => true,
        ]);

        Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $category3->id, // sort_order 2
            'name' => 'Dessert',
            'price_base' => 3000,
            'active' => true,
        ]);

        $response = $this->getJson("/api/menu?tenant={$this->tenant->id}&table={$this->table->code}");

        $response->assertStatus(200);

        $categories = $response->json('menu.categories');
        $categoryNames = collect($categories)->pluck('name')->toArray();

        // First should be Entrées (sort_order 0)
        $this->assertEquals('Entrées', $categoryNames[0]);
    }

    /** @test */
    public function api_includes_dish_variants()
    {
        $dish = Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'Pizza',
            'price_base' => 5000,
            'active' => true,
        ]);

        $dish->variants()->create(['name' => 'Small', 'price_modifier' => -1000]);
        $dish->variants()->create(['name' => 'Large', 'price_modifier' => 2000]);

        $response = $this->getJson("/api/menu?tenant={$this->tenant->id}&table={$this->table->code}");

        $response->assertStatus(200);

        $dishes = $response->json('menu.categories.0.dishes');
        $pizza = collect($dishes)->firstWhere('name', 'Pizza');

        $this->assertNotNull($pizza);
        $this->assertCount(2, $pizza['variants']);
    }

    /** @test */
    public function api_includes_dish_options()
    {
        $dish = Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'Burger',
            'price_base' => 4000,
            'active' => true,
        ]);

        $dish->options()->create(['name' => 'Extra cheese', 'price' => 500]);
        $dish->options()->create(['name' => 'No onions', 'price' => 0]);

        $response = $this->getJson("/api/menu?tenant={$this->tenant->id}&table={$this->table->code}");

        $response->assertStatus(200);

        $dishes = $response->json('menu.categories.0.dishes');
        $burger = collect($dishes)->firstWhere('name', 'Burger');

        $this->assertNotNull($burger);
        $this->assertCount(2, $burger['options']);
    }

    /** @test */
    public function api_respects_rate_limiting()
    {
        // Make many requests quickly
        for ($i = 0; $i < 60; $i++) {
            $this->getJson("/api/menu?tenant={$this->tenant->id}&table={$this->table->code}");
        }

        // The 61st request should be rate limited
        $response = $this->getJson("/api/menu?tenant={$this->tenant->id}&table={$this->table->code}");

        // Should return 429 Too Many Requests
        $response->assertStatus(429);
    }
}
