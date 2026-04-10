<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Table;
use App\Models\Menu;
use App\Models\Category;
use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\StatisticsService;
use App\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class StatisticsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StatisticsService $statisticsService;
    protected Tenant $tenant;
    protected Table $table;
    protected Dish $dish;

    protected function setUp(): void
    {
        parent::setUp();

        $this->statisticsService = new StatisticsService();

        $this->tenant = Tenant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'type' => 'RESTAURANT',
            'currency' => 'XOF',
            'locale' => 'fr',
            'is_active' => true,
        ]);

        $this->table = Table::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'T01',
            'label' => 'Table 1',
            'capacity' => 4,
            'is_active' => true,
        ]);

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
    }

    /** @test */
    public function it_can_get_hourly_peaks()
    {
        // Create orders at different hours
        $this->createOrderAtHour(9, OrderStatus::SERVED->value, 3000);
        $this->createOrderAtHour(12, OrderStatus::SERVED->value, 5000);
        $this->createOrderAtHour(12, OrderStatus::SERVED->value, 4000);
        $this->createOrderAtHour(12, OrderStatus::SERVED->value, 6000);
        $this->createOrderAtHour(19, OrderStatus::SERVED->value, 7000);
        $this->createOrderAtHour(19, OrderStatus::SERVED->value, 8000);

        $peaks = $this->statisticsService->getHourlyPeaks($this->tenant->id);

        $this->assertArrayHasKey('data', $peaks);
        $this->assertArrayHasKey('peak_hour', $peaks);
        $this->assertArrayHasKey('peak_count', $peaks);
        $this->assertArrayHasKey('total', $peaks);
        $this->assertEquals(12, $peaks['peak_hour']); // 12h has most orders
        $this->assertEquals(3, $peaks['peak_count']);
        $this->assertEquals(6, $peaks['total']);
    }

    /** @test */
    public function it_returns_all_24_hours_in_peaks_data()
    {
        $peaks = $this->statisticsService->getHourlyPeaks($this->tenant->id);

        $this->assertCount(24, $peaks['data']);

        for ($i = 0; $i < 24; $i++) {
            $this->assertArrayHasKey($i, $peaks['data']);
        }
    }

    /** @test */
    public function it_can_get_top_dishes()
    {
        $dish2 = Dish::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->dish->category_id,
            'name' => 'Poisson Grillé',
            'price_base' => 6000,
            'active' => true,
        ]);

        // Create orders with items
        $this->createOrderWithItems([
            ['dish_id' => $this->dish->id, 'quantity' => 3],
        ]);

        $this->createOrderWithItems([
            ['dish_id' => $this->dish->id, 'quantity' => 2],
            ['dish_id' => $dish2->id, 'quantity' => 1],
        ]);

        $topDishes = $this->statisticsService->getTopDishes($this->tenant->id, 10, 'month');

        $this->assertGreaterThanOrEqual(1, $topDishes->count());

        $topDish = $topDishes->first();
        $this->assertEquals($this->dish->id, $topDish['dish']->id);
        $this->assertEquals(5, $topDish['total_quantity']);
    }

    /** @test */
    public function it_can_get_revenue()
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
            'status' => OrderStatus::READY->value,
            'total' => 5000,
        ]);

        Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::CANCELLED->value, // Not counted
            'total' => 8000,
        ]);

        $revenue = $this->statisticsService->getRevenue($this->tenant->id, 'month');

        $this->assertArrayHasKey('current', $revenue);
        $this->assertArrayHasKey('previous', $revenue);
        $this->assertArrayHasKey('change_percent', $revenue);
        $this->assertArrayHasKey('trend', $revenue);
        $this->assertEquals(15000, $revenue['current']); // 10000 + 5000
    }

    /** @test */
    public function it_can_get_daily_revenue()
    {
        Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::SERVED->value,
            'total' => 10000,
        ]);

        $dailyRevenue = $this->statisticsService->getDailyRevenue($this->tenant->id, 7);

        $this->assertCount(8, $dailyRevenue); // 7 days + today

        $today = now()->format('Y-m-d');
        $todayData = collect($dailyRevenue)->firstWhere('date', $today);

        $this->assertNotNull($todayData);
        $this->assertEquals(10000, $todayData['revenue']);
        $this->assertEquals(1, $todayData['orders']);
    }

    /** @test */
    public function it_can_get_orders_by_status()
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
            'total' => 6000,
        ]);

        Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::SERVED->value,
            'total' => 7000,
        ]);

        Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::SERVED->value,
            'total' => 8000,
        ]);

        $ordersByStatus = $this->statisticsService->getOrdersByStatus($this->tenant->id, 'month');

        $this->assertEquals(1, $ordersByStatus['RECU']);
        $this->assertEquals(1, $ordersByStatus['PREP']);
        $this->assertEquals(2, $ordersByStatus['SERVI']);
    }

    /** @test */
    public function it_can_get_average_order_value()
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

        $averageValue = $this->statisticsService->getAverageOrderValue($this->tenant->id, 'month');

        $this->assertEquals(15000, $averageValue);
    }

    /** @test */
    public function it_can_get_dashboard_stats()
    {
        Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::SERVED->value,
            'total' => 10000,
        ]);

        $stats = $this->statisticsService->getDashboardStats($this->tenant->id);

        $this->assertArrayHasKey('today', $stats);
        $this->assertArrayHasKey('week', $stats);
        $this->assertArrayHasKey('month', $stats);
        $this->assertArrayHasKey('pending_orders', $stats);
        $this->assertArrayHasKey('hourly_peaks', $stats);
        $this->assertArrayHasKey('top_dishes', $stats);

        $this->assertEquals(1, $stats['today']['orders']);
        $this->assertEquals(10000, $stats['today']['revenue']);
    }

    /** @test */
    public function it_can_get_table_stats()
    {
        $table2 = Table::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'T02',
            'label' => 'Table 2',
            'capacity' => 6,
            'is_active' => true,
        ]);

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

        Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $table2->id,
            'status' => OrderStatus::SERVED->value,
            'total' => 8000,
        ]);

        $tableStats = $this->statisticsService->getTableStats($this->tenant->id, 'month');

        $this->assertGreaterThanOrEqual(2, $tableStats->count());

        $table1Stats = $tableStats->firstWhere('table.id', $this->table->id);
        $this->assertEquals(2, $table1Stats['orders_count']);
        $this->assertEquals(25000, $table1Stats['total_revenue']);
        $this->assertEquals(12500, $table1Stats['average_order']);
    }

    /** @test */
    public function it_can_get_comprehensive_stats()
    {
        Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::SERVED->value,
            'total' => 10000,
        ]);

        $stats = $this->statisticsService->getComprehensiveStats($this->tenant->id);

        $this->assertArrayHasKey('dashboard', $stats);
        $this->assertArrayHasKey('revenue', $stats);
        $this->assertArrayHasKey('daily_revenue', $stats);
        $this->assertArrayHasKey('orders_by_status', $stats);
        $this->assertArrayHasKey('top_dishes', $stats);
        $this->assertArrayHasKey('table_stats', $stats);
        $this->assertArrayHasKey('reviews', $stats);
        $this->assertArrayHasKey('reservations', $stats);
    }

    /**
     * Helper to create an order at a specific hour
     */
    protected function createOrderAtHour(int $hour, string $status, float $total): Order
    {
        $order = Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => $status,
            'total' => $total,
        ]);

        // Update created_at to specific hour
        $order->update(['created_at' => now()->setHour($hour)->setMinute(0)]);

        return $order;
    }

    /**
     * Helper to create order with items
     */
    protected function createOrderWithItems(array $items): Order
    {
        $total = 0;
        foreach ($items as $item) {
            $dish = Dish::find($item['dish_id']);
            $total += $dish->price_base * $item['quantity'];
        }

        $order = Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::SERVED->value,
            'total' => $total,
        ]);

        foreach ($items as $item) {
            $dish = Dish::find($item['dish_id']);
            OrderItem::create([
                'order_id' => $order->id,
                'dish_id' => $item['dish_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $dish->price_base,
            ]);
        }

        return $order;
    }
}
