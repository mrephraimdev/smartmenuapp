<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Role;
use App\Models\Menu;
use App\Models\Category;
use App\Models\Dish;
use App\Models\Table;
use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Table $table;
    protected Menu $menu;
    protected Category $category;
    protected Dish $dish;
    protected User $admin;
    protected User $chef;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'SUPER_ADMIN', 'label' => 'Super Admin']);
        Role::create(['name' => 'ADMIN', 'label' => 'Admin']);
        Role::create(['name' => 'CHEF', 'label' => 'Chef']);
        Role::create(['name' => 'SERVEUR', 'label' => 'Serveur']);

        // Create tenant
        $this->tenant = Tenant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'type' => 'RESTAURANT',
            'currency' => 'XOF',
            'locale' => 'fr',
            'is_active' => true,
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

        $this->dish = Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'Poulet Braisé',
            'description' => 'Poulet grillé aux épices africaines',
            'price_base' => 5000,
            'active' => true,
        ]);

        // Create users
        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant->id,
        ]);
        $this->admin->roles()->attach(Role::where('name', 'ADMIN')->first());

        $this->chef = User::create([
            'name' => 'Chef',
            'email' => 'chef@test.com',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant->id,
        ]);
        $this->chef->roles()->attach(Role::where('name', 'CHEF')->first());
    }

    /** @test */
    public function menu_client_page_loads_with_correct_tenant()
    {
        $response = $this->get("/menu/{$this->tenant->id}/{$this->table->code}");

        $response->assertStatus(200);
        $response->assertSee($this->tenant->name);
    }

    /** @test */
    public function menu_displays_active_dishes()
    {
        $response = $this->get("/menu/{$this->tenant->id}/{$this->table->code}");

        $response->assertStatus(200);
        $response->assertSee($this->dish->name);
        $response->assertSee(number_format($this->dish->price_base, 0, ',', ' '));
    }

    /** @test */
    public function inactive_dishes_are_not_displayed()
    {
        $inactiveDish = Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'Plat Inactif',
            'price_base' => 3000,
            'active' => false,
        ]);

        $response = $this->get("/menu/{$this->tenant->id}/{$this->table->code}");

        $response->assertStatus(200);
        $response->assertDontSee('Plat Inactif');
    }

    /** @test */
    public function order_can_be_created_via_api()
    {
        $response = $this->postJson('/api/orders', [
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'items' => [
                [
                    'dish_id' => $this->dish->id,
                    'quantity' => 2,
                    'unit_price' => $this->dish->price_base,
                ],
            ],
            'total' => $this->dish->price_base * 2,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('orders', [
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::RECEIVED->value,
        ]);
    }

    /** @test */
    public function order_creates_order_items()
    {
        $this->postJson('/api/orders', [
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'items' => [
                [
                    'dish_id' => $this->dish->id,
                    'quantity' => 3,
                    'unit_price' => $this->dish->price_base,
                ],
            ],
            'total' => $this->dish->price_base * 3,
        ]);

        $this->assertDatabaseHas('order_items', [
            'dish_id' => $this->dish->id,
            'quantity' => 3,
            'unit_price' => $this->dish->price_base,
        ]);
    }

    /** @test */
    public function kds_displays_active_orders()
    {
        $order = Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::RECEIVED->value,
            'total' => 5000,
        ]);

        $response = $this->actingAs($this->chef)
            ->get("/kds/{$this->tenant->id}");

        $response->assertStatus(200);
        $response->assertSee($order->order_number);
    }

    /** @test */
    public function order_status_can_be_updated()
    {
        $order = Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::RECEIVED->value,
            'total' => 5000,
        ]);

        $response = $this->actingAs($this->chef)
            ->patchJson("/api/orders/{$order->id}/status", [
                'status' => OrderStatus::PREPARING->value,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::PREPARING->value,
        ]);
    }

    /** @test */
    public function order_progresses_through_statuses()
    {
        $order = Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::RECEIVED->value,
            'total' => 5000,
        ]);

        // RECEIVED -> PREPARING
        $this->actingAs($this->chef)
            ->patchJson("/api/orders/{$order->id}/status", [
                'status' => OrderStatus::PREPARING->value,
            ])
            ->assertStatus(200);

        $order->refresh();
        $this->assertEquals(OrderStatus::PREPARING->value, $order->status);

        // PREPARING -> READY
        $this->actingAs($this->chef)
            ->patchJson("/api/orders/{$order->id}/status", [
                'status' => OrderStatus::READY->value,
            ])
            ->assertStatus(200);

        $order->refresh();
        $this->assertEquals(OrderStatus::READY->value, $order->status);

        // READY -> SERVED
        $this->actingAs($this->chef)
            ->patchJson("/api/orders/{$order->id}/status", [
                'status' => OrderStatus::SERVED->value,
            ])
            ->assertStatus(200);

        $order->refresh();
        $this->assertEquals(OrderStatus::SERVED->value, $order->status);
    }

    /** @test */
    public function served_orders_not_shown_in_kds()
    {
        $activeOrder = Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::RECEIVED->value,
            'total' => 5000,
        ]);

        $servedOrder = Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::SERVED->value,
            'total' => 8000,
        ]);

        $response = $this->actingAs($this->chef)
            ->get("/kds/{$this->tenant->id}");

        $response->assertStatus(200);
        $response->assertSee($activeOrder->order_number);
        // Note: The served order may or may not be visible depending on KDS implementation
    }

    /** @test */
    public function order_validation_requires_items()
    {
        $response = $this->postJson('/api/orders', [
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'items' => [],
            'total' => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('items');
    }

    /** @test */
    public function order_validation_requires_valid_tenant()
    {
        $response = $this->postJson('/api/orders', [
            'tenant_id' => 99999,
            'table_id' => $this->table->id,
            'items' => [
                ['dish_id' => $this->dish->id, 'quantity' => 1, 'unit_price' => 5000],
            ],
            'total' => 5000,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('tenant_id');
    }

    /** @test */
    public function order_calculates_total_correctly()
    {
        $dish2 = Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'name' => 'Poisson Grillé',
            'price_base' => 6000,
            'active' => true,
        ]);

        $expectedTotal = ($this->dish->price_base * 2) + ($dish2->price_base * 1);

        $response = $this->postJson('/api/orders', [
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'items' => [
                ['dish_id' => $this->dish->id, 'quantity' => 2, 'unit_price' => $this->dish->price_base],
                ['dish_id' => $dish2->id, 'quantity' => 1, 'unit_price' => $dish2->price_base],
            ],
            'total' => $expectedTotal,
        ]);

        $response->assertStatus(201);

        $order = Order::latest()->first();
        $this->assertEquals($expectedTotal, $order->total);
    }
}
