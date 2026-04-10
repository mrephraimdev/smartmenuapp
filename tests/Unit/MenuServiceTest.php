<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Menu;
use App\Models\Category;
use App\Models\Dish;
use App\Models\Variant;
use App\Models\Option;
use App\Services\MenuService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MenuServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MenuService $menuService;
    protected Tenant $tenant;
    protected Menu $menu;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->menuService = new MenuService();

        $this->tenant = Tenant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'type' => 'RESTAURANT',
            'currency' => 'XOF',
            'locale' => 'fr',
            'is_active' => true,
        ]);

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
    public function it_can_get_full_menu_with_categories_and_dishes()
    {
        Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'Poulet Braisé',
            'price_base' => 5000,
            'active' => true,
        ]);

        Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'Poisson Grillé',
            'price_base' => 6000,
            'active' => true,
        ]);

        $menu = $this->menuService->getFullMenu($this->tenant->id);

        $this->assertNotNull($menu);
        $this->assertCount(1, $menu->categories);
        $this->assertCount(2, $menu->categories->first()->dishes);
    }

    /** @test */
    public function it_can_create_a_dish_with_variants_and_options()
    {
        $dishData = [
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'Pizza Margherita',
            'description' => 'Pizza classique tomate et mozzarella',
            'price_base' => 4500,
            'allergens' => ['gluten', 'lactose'],
            'tags' => ['vegetarien', 'populaire'],
            'active' => true,
            'variants' => [
                ['name' => 'Petite', 'price_modifier' => -500],
                ['name' => 'Grande', 'price_modifier' => 1000],
            ],
            'options' => [
                ['name' => 'Extra fromage', 'price' => 500],
                ['name' => 'Sans oignon', 'price' => 0],
            ],
        ];

        $dish = $this->menuService->createDish($dishData);

        $this->assertInstanceOf(Dish::class, $dish);
        $this->assertEquals('Pizza Margherita', $dish->name);
        $this->assertEquals(4500, $dish->price_base);
        $this->assertCount(2, $dish->allergens);
        $this->assertCount(2, $dish->tags);
        $this->assertCount(2, $dish->variants);
        $this->assertCount(2, $dish->options);
    }

    /** @test */
    public function it_can_update_a_dish()
    {
        $dish = Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'Original Name',
            'price_base' => 5000,
            'active' => true,
        ]);

        $updatedDish = $this->menuService->updateDish($dish, [
            'name' => 'Updated Name',
            'price_base' => 6000,
            'description' => 'New description',
        ]);

        $this->assertEquals('Updated Name', $updatedDish->name);
        $this->assertEquals(6000, $updatedDish->price_base);
        $this->assertEquals('New description', $updatedDish->description);
    }

    /** @test */
    public function it_can_delete_a_dish_with_variants_and_options()
    {
        $dish = Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'To Delete',
            'price_base' => 5000,
            'active' => true,
        ]);

        $dish->variants()->create(['name' => 'Variant', 'price_modifier' => 100]);
        $dish->options()->create(['name' => 'Option', 'price' => 200]);

        $dishId = $dish->id;

        $result = $this->menuService->deleteDish($dish);

        $this->assertTrue($result);
        $this->assertNull(Dish::find($dishId));
        $this->assertCount(0, Variant::where('dish_id', $dishId)->get());
        $this->assertCount(0, Option::where('dish_id', $dishId)->get());
    }

    /** @test */
    public function it_can_duplicate_a_dish()
    {
        $dish = Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'Original Dish',
            'description' => 'Original description',
            'price_base' => 5000,
            'allergens' => ['gluten'],
            'tags' => ['populaire'],
            'active' => true,
        ]);

        $dish->variants()->create(['name' => 'Small', 'price_modifier' => -500]);
        $dish->options()->create(['name' => 'Extra cheese', 'price' => 300]);

        $duplicatedDish = $this->menuService->duplicateDish($dish);

        $this->assertNotEquals($dish->id, $duplicatedDish->id);
        $this->assertEquals('Original Dish (copie)', $duplicatedDish->name);
        $this->assertEquals($dish->price_base, $duplicatedDish->price_base);
        $this->assertCount(1, $duplicatedDish->variants);
        $this->assertCount(1, $duplicatedDish->options);
    }

    /** @test */
    public function it_can_update_dish_availability()
    {
        $dish = Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'Test Dish',
            'price_base' => 5000,
            'active' => true,
        ]);

        $updatedDish = $this->menuService->updateAvailability($dish, false);
        $this->assertFalse($updatedDish->active);

        $updatedDish = $this->menuService->updateAvailability($dish, true);
        $this->assertTrue($updatedDish->active);
    }

    /** @test */
    public function it_can_update_and_decrement_stock()
    {
        $dish = Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'Limited Dish',
            'price_base' => 5000,
            'stock_quantity' => 10,
            'active' => true,
        ]);

        // Update stock
        $updatedDish = $this->menuService->updateStock($dish, 20);
        $this->assertEquals(20, $updatedDish->stock_quantity);

        // Decrement stock
        $updatedDish = $this->menuService->decrementStock($dish, 3);
        $this->assertEquals(17, $updatedDish->stock_quantity);

        // Decrement shouldn't go below 0
        $updatedDish = $this->menuService->decrementStock($dish, 100);
        $this->assertEquals(0, $updatedDish->stock_quantity);
    }

    /** @test */
    public function it_can_get_dishes_by_category()
    {
        Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'Active Dish 1',
            'price_base' => 5000,
            'active' => true,
        ]);

        Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'Active Dish 2',
            'price_base' => 6000,
            'active' => true,
        ]);

        Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'Inactive Dish',
            'price_base' => 7000,
            'active' => false,
        ]);

        $dishes = $this->menuService->getDishesByCategory($this->category->id);

        $this->assertCount(2, $dishes);
        $this->assertTrue($dishes->every(fn($d) => $d->active));
    }

    /** @test */
    public function it_can_get_available_dishes()
    {
        Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'Unlimited Stock',
            'price_base' => 5000,
            'stock_quantity' => null,
            'active' => true,
        ]);

        Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'Has Stock',
            'price_base' => 6000,
            'stock_quantity' => 5,
            'active' => true,
        ]);

        Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'No Stock',
            'price_base' => 7000,
            'stock_quantity' => 0,
            'active' => true,
        ]);

        $dishes = $this->menuService->getAvailableDishes($this->tenant->id);

        $this->assertCount(2, $dishes);
        $this->assertFalse($dishes->contains(fn($d) => $d->name === 'No Stock'));
    }

    /** @test */
    public function it_can_search_dishes()
    {
        Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'Poulet Braisé',
            'description' => 'Poulet grillé aux épices africaines',
            'price_base' => 5000,
            'active' => true,
        ]);

        Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'Poisson Grillé',
            'description' => 'Poisson frais du jour',
            'price_base' => 6000,
            'active' => true,
        ]);

        // Search by name
        $results = $this->menuService->searchDishes($this->tenant->id, 'Poulet');
        $this->assertCount(1, $results);
        $this->assertEquals('Poulet Braisé', $results->first()->name);

        // Search by description
        $results = $this->menuService->searchDishes($this->tenant->id, 'africaines');
        $this->assertCount(1, $results);

        // Search with no results
        $results = $this->menuService->searchDishes($this->tenant->id, 'pizza');
        $this->assertCount(0, $results);
    }

    /** @test */
    public function it_can_create_and_sort_categories()
    {
        $category1 = $this->menuService->createCategory([
            'menu_id' => $this->menu->id,
            'name' => 'Entrées',
            'sort_order' => 1,
        ]);

        $category2 = $this->menuService->createCategory([
            'menu_id' => $this->menu->id,
            'name' => 'Desserts',
            'sort_order' => 3,
        ]);

        $category3 = $this->menuService->createCategory([
            'menu_id' => $this->menu->id,
            'name' => 'Plats',
            'sort_order' => 2,
        ]);

        // Reorder categories
        $this->menuService->updateCategorySortOrder([
            $category2->id,  // Desserts first
            $category3->id,  // Plats second
            $category1->id,  // Entrées third
        ]);

        $category1->refresh();
        $category2->refresh();
        $category3->refresh();

        $this->assertEquals(2, $category1->sort_order);
        $this->assertEquals(0, $category2->sort_order);
        $this->assertEquals(1, $category3->sort_order);
    }
}
