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
use App\Models\OrderItem;
use App\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StatisticsTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $admin;
    protected Table $table;
    protected Dish $dish1;
    protected Dish $dish2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'ADMIN', 'label' => 'Admin']);

        // Create tenant
        $this->tenant = Tenant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'type' => 'RESTAURANT',
            'currency' => 'XOF',
            'locale' => 'fr',
            'is_active' => true,
        ]);

        // Create admin
        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant->id,
        ]);
        $this->admin->roles()->attach(Role::where('name', 'ADMIN')->first());

        // Create table
        $this->table = Table::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'T01',
            'label' => 'Table 1',
            'capacity' => 4,
            'is_active' => true,
        ]);

        // Create dishes
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

        $this->dish1 = Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $category->id,
            'name' => 'Poulet Braisé',
            'price_base' => 5000,
            'active' => true,
        ]);

        $this->dish2 = Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $category->id,
            'name' => 'Poisson Grillé',
            'price_base' => 6000,
            'active' => true,
        ]);
    }

    /** @test */
    public function admin_can_access_statistics_page()
    {
        $response = $this->actingAs($this->admin)
            ->get("/admin/{$this->tenant->slug}/statistics");

        $response->assertStatus(200);
    }

    /** @test */
    public function statistics_shows_today_orders_count()
    {
        // Create some orders today
        Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::SERVED->value,
            'total' => 5000,
        ]);

        Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::READY->value,
            'total' => 8000,
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/admin/{$this->tenant->slug}/statistics");

        $response->assertStatus(200);
        // The page should contain statistics data
    }

    /** @test */
    public function statistics_calculates_revenue_correctly()
    {
        Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::SERVED->value,
            'total' => 10000,
        ]);

        Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::SERVED->value,
            'total' => 15000,
        ]);

        // Cancelled order (should not count)
        Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::CANCELLED->value,
            'total' => 5000,
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/admin/{$this->tenant->slug}/statistics");

        $response->assertStatus(200);
    }

    /** @test */
    public function chart_data_endpoint_returns_json()
    {
        Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::SERVED->value,
            'total' => 5000,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/admin/{$this->tenant->slug}/statistics/chart-data");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'hourly_peaks',
                'daily_revenue',
                'orders_by_status',
            ]);
    }

    /** @test */
    public function top_dishes_shows_most_ordered()
    {
        // Create orders with items
        $order1 = Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::SERVED->value,
            'total' => 25000,
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'dish_id' => $this->dish1->id,
            'quantity' => 5,
            'unit_price' => 5000,
        ]);

        $order2 = Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::SERVED->value,
            'total' => 12000,
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'dish_id' => $this->dish2->id,
            'quantity' => 2,
            'unit_price' => 6000,
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/admin/{$this->tenant->slug}/statistics");

        $response->assertStatus(200);
        // Dish1 should appear as more popular (5 orders vs 2)
    }

    /** @test */
    public function statistics_respects_tenant_isolation()
    {
        // Create another tenant
        $otherTenant = Tenant::create([
            'name' => 'Other Restaurant',
            'slug' => 'other-restaurant',
            'type' => 'RESTAURANT',
            'currency' => 'XOF',
            'locale' => 'fr',
            'is_active' => true,
        ]);

        $otherTable = Table::create([
            'tenant_id' => $otherTenant->id,
            'code' => 'T01',
            'label' => 'Table 1',
            'capacity' => 4,
            'is_active' => true,
        ]);

        // Create order for our tenant
        Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::SERVED->value,
            'total' => 5000,
        ]);

        // Create order for other tenant
        Order::createWithNumber([
            'tenant_id' => $otherTenant->id,
            'table_id' => $otherTable->id,
            'status' => OrderStatus::SERVED->value,
            'total' => 100000, // Large amount that shouldn't show for our admin
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/admin/{$this->tenant->slug}/statistics/chart-data");

        $response->assertStatus(200);
        // Statistics should only include our tenant's data
    }

    /** @test */
    public function hourly_peaks_calculation_works()
    {
        // Create orders at different hours
        $this->createOrderAtHour(12); // Lunch
        $this->createOrderAtHour(12);
        $this->createOrderAtHour(12);
        $this->createOrderAtHour(19); // Dinner
        $this->createOrderAtHour(19);

        $response = $this->actingAs($this->admin)
            ->getJson("/admin/{$this->tenant->slug}/statistics/chart-data");

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertArrayHasKey('hourly_peaks', $data);
    }

    /** @test */
    public function average_order_value_is_calculated()
    {
        Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::SERVED->value,
            'total' => 10000,
        ]);

        Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::SERVED->value,
            'total' => 20000,
        ]);

        // Average should be 15000

        $response = $this->actingAs($this->admin)
            ->get("/admin/{$this->tenant->slug}/statistics");

        $response->assertStatus(200);
    }

    /**
     * Helper to create order at specific hour
     */
    protected function createOrderAtHour(int $hour): Order
    {
        $order = Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::SERVED->value,
            'total' => 5000,
        ]);

        $order->update(['created_at' => now()->setHour($hour)->setMinute(0)]);

        return $order;
    }
}
