<?php

namespace Tests\Feature\Api;

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

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Table $table;
    protected Dish $dish;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'ADMIN', 'label' => 'Admin']);
        Role::create(['name' => 'CHEF', 'label' => 'Chef']);

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
        $menu = Menu::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Menu Principal',
            'active' => true,
        ]);

        $category = Category::create([
            'menu_id' => $menu->id,
            'name' => 'Plats',
            'sort_order' => 1,
        ]);

        $this->dish = Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $category->id,
            'name' => 'Poulet Braisé',
            'price_base' => 5000,
            'active' => true,
        ]);

        // Create admin
        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant->id,
        ]);
        $this->admin->roles()->attach(Role::where('name', 'ADMIN')->first());
    }

    /** @test */
    public function api_can_create_order()
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

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'order_number',
                    'status',
                    'total',
                    'items',
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'tenant_id' => $this->tenant->id,
            'status' => OrderStatus::RECEIVED->value,
        ]);
    }

    /** @test */
    public function api_order_creation_validates_items()
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
    public function api_order_creation_validates_tenant()
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
    public function api_order_creation_validates_table()
    {
        $response = $this->postJson('/api/orders', [
            'tenant_id' => $this->tenant->id,
            'table_id' => 99999,
            'items' => [
                ['dish_id' => $this->dish->id, 'quantity' => 1, 'unit_price' => 5000],
            ],
            'total' => 5000,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('table_id');
    }

    /** @test */
    public function api_order_creation_validates_dish()
    {
        $response = $this->postJson('/api/orders', [
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'items' => [
                ['dish_id' => 99999, 'quantity' => 1, 'unit_price' => 5000],
            ],
            'total' => 5000,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('items.0.dish_id');
    }

    /** @test */
    public function api_can_update_order_status()
    {
        $order = Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::RECEIVED->value,
            'total' => 5000,
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/orders/{$order->id}/status", [
                'status' => OrderStatus::PREPARING->value,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', OrderStatus::PREPARING->value);

        $order->refresh();
        $this->assertEquals(OrderStatus::PREPARING->value, $order->status);
    }

    /** @test */
    public function api_validates_status_transition()
    {
        $order = Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::RECEIVED->value,
            'total' => 5000,
        ]);

        // Try to skip from RECEIVED directly to SERVED (invalid transition)
        $response = $this->actingAs($this->admin)
            ->patchJson("/api/orders/{$order->id}/status", [
                'status' => OrderStatus::SERVED->value,
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function api_cannot_update_served_order()
    {
        $order = Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::SERVED->value,
            'total' => 5000,
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/orders/{$order->id}/status", [
                'status' => OrderStatus::PREPARING->value,
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function api_can_get_orders_for_kds()
    {
        Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::RECEIVED->value,
            'total' => 5000,
        ]);

        Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::PREPARING->value,
            'total' => 8000,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/orders/kds/{$this->tenant->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'RECU',
                'PREP',
                'PRET',
            ]);
    }

    /** @test */
    public function api_kds_excludes_served_orders()
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

        $response = $this->actingAs($this->admin)
            ->getJson("/api/orders/kds/{$this->tenant->id}");

        $response->assertStatus(200);

        // Should only contain active order
        $allOrders = array_merge(
            $response->json('RECU'),
            $response->json('PREP'),
            $response->json('PRET')
        );

        $orderIds = collect($allOrders)->pluck('id')->toArray();

        $this->assertContains($activeOrder->id, $orderIds);
        $this->assertNotContains($servedOrder->id, $orderIds);
    }

    /** @test */
    public function api_order_includes_items()
    {
        $dish2 = Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->dish->category_id,
            'name' => 'Poisson Grillé',
            'price_base' => 6000,
            'active' => true,
        ]);

        $response = $this->postJson('/api/orders', [
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'items' => [
                ['dish_id' => $this->dish->id, 'quantity' => 2, 'unit_price' => $this->dish->price_base],
                ['dish_id' => $dish2->id, 'quantity' => 1, 'unit_price' => $dish2->price_base],
            ],
            'total' => ($this->dish->price_base * 2) + $dish2->price_base,
        ]);

        $response->assertStatus(201);

        $items = $response->json('data.items');
        $this->assertCount(2, $items);
    }

    /** @test */
    public function api_rate_limits_order_creation()
    {
        // Make many order requests quickly
        for ($i = 0; $i < 60; $i++) {
            $this->postJson('/api/orders', [
                'tenant_id' => $this->tenant->id,
                'table_id' => $this->table->id,
                'items' => [
                    ['dish_id' => $this->dish->id, 'quantity' => 1, 'unit_price' => 5000],
                ],
                'total' => 5000,
            ]);
        }

        // The next request should be rate limited
        $response = $this->postJson('/api/orders', [
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'items' => [
                ['dish_id' => $this->dish->id, 'quantity' => 1, 'unit_price' => 5000],
            ],
            'total' => 5000,
        ]);

        $response->assertStatus(429);
    }

    /** @test */
    public function api_can_get_order_details()
    {
        $order = Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::RECEIVED->value,
            'total' => 5000,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $order->id)
            ->assertJsonPath('data.order_number', $order->order_number);
    }

    /** @test */
    public function api_order_creation_with_notes()
    {
        $response = $this->postJson('/api/orders', [
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'items' => [
                [
                    'dish_id' => $this->dish->id,
                    'quantity' => 1,
                    'unit_price' => $this->dish->price_base,
                    'notes' => 'Sans piment',
                ],
            ],
            'notes' => 'Client allergique aux arachides',
            'total' => $this->dish->price_base,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('orders', [
            'notes' => 'Client allergique aux arachides',
        ]);

        $this->assertDatabaseHas('order_items', [
            'notes' => 'Sans piment',
        ]);
    }
}
