<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Table;
use App\Models\Menu;
use App\Models\Category;
use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderService;
use App\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OrderService $orderService;
    protected Tenant $tenant;
    protected Table $table;
    protected Dish $dish;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderService = new OrderService();

        // Create test data
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
            'description' => 'Poulet grillé avec épices',
            'price_base' => 5000,
            'active' => true,
        ]);
    }

    /** @test */
    public function it_can_create_an_order_with_items()
    {
        $orderData = [
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'items' => [
                [
                    'dish_id' => $this->dish->id,
                    'quantity' => 2,
                    'variant_id' => null,
                    'options' => [],
                    'notes' => 'Sans piment',
                ],
            ],
            'notes' => 'Commande test',
        ];

        $order = $this->orderService->createOrder($orderData);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals($this->tenant->id, $order->tenant_id);
        $this->assertEquals($this->table->id, $order->table_id);
        $this->assertEquals(OrderStatus::RECEIVED->value, $order->status);
        $this->assertEquals(10000, $order->total); // 2 * 5000
        $this->assertCount(1, $order->items);
    }

    /** @test */
    public function it_generates_unique_order_number()
    {
        $orderData = [
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'items' => [
                ['dish_id' => $this->dish->id, 'quantity' => 1, 'variant_id' => null, 'options' => []],
            ],
        ];

        $order1 = $this->orderService->createOrder($orderData);
        $order2 = $this->orderService->createOrder($orderData);

        $this->assertNotEquals($order1->order_number, $order2->order_number);
        $this->assertStringContainsString(now()->format('Ymd'), $order1->order_number);
    }

    /** @test */
    public function it_can_update_order_status()
    {
        $order = Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::RECEIVED->value,
            'total' => 5000,
        ]);

        $updatedOrder = $this->orderService->updateStatus($order, OrderStatus::PREPARING);

        $this->assertEquals(OrderStatus::PREPARING->value, $updatedOrder->status);
    }

    /** @test */
    public function it_can_progress_order_status()
    {
        $order = Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::RECEIVED->value,
            'total' => 5000,
        ]);

        // RECEIVED -> PREPARING
        $order = $this->orderService->progressStatus($order);
        $this->assertEquals(OrderStatus::PREPARING->value, $order->status);

        // PREPARING -> READY
        $order = $this->orderService->progressStatus($order);
        $this->assertEquals(OrderStatus::READY->value, $order->status);

        // READY -> SERVED
        $order = $this->orderService->progressStatus($order);
        $this->assertEquals(OrderStatus::SERVED->value, $order->status);

        // SERVED has no next status
        $result = $this->orderService->progressStatus($order);
        $this->assertNull($result);
    }

    /** @test */
    public function it_can_cancel_an_order()
    {
        $order = Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::RECEIVED->value,
            'total' => 5000,
            'notes' => '',
        ]);

        $cancelledOrder = $this->orderService->cancelOrder($order, 'Client parti');

        $this->assertEquals(OrderStatus::CANCELLED->value, $cancelledOrder->status);
        $this->assertStringContainsString('Client parti', $cancelledOrder->notes);
    }

    /** @test */
    public function it_can_get_orders_by_tenant()
    {
        // Create orders for our tenant
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

        // Create order for different tenant
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

        Order::createWithNumber([
            'tenant_id' => $otherTenant->id,
            'table_id' => $otherTable->id,
            'status' => OrderStatus::RECEIVED->value,
            'total' => 3000,
        ]);

        $orders = $this->orderService->getOrdersByTenant($this->tenant->id);

        $this->assertCount(2, $orders);
        $this->assertTrue($orders->every(fn($o) => $o->tenant_id === $this->tenant->id));
    }

    /** @test */
    public function it_can_get_active_orders()
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
            'status' => OrderStatus::SERVED->value,
            'total' => 8000,
        ]);

        Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::CANCELLED->value,
            'total' => 3000,
        ]);

        $activeOrders = $this->orderService->getActiveOrders($this->tenant->id);

        $this->assertCount(1, $activeOrders);
        $this->assertEquals(OrderStatus::RECEIVED->value, $activeOrders->first()->status);
    }

    /** @test */
    public function it_can_get_orders_for_kds()
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

        Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::READY->value,
            'total' => 3000,
        ]);

        $kdsOrders = $this->orderService->getOrdersForKDS($this->tenant->id);

        $this->assertArrayHasKey('RECU', $kdsOrders);
        $this->assertArrayHasKey('PREP', $kdsOrders);
        $this->assertArrayHasKey('PRET', $kdsOrders);
        $this->assertCount(1, $kdsOrders['RECU']);
        $this->assertCount(1, $kdsOrders['PREP']);
        $this->assertCount(1, $kdsOrders['PRET']);
    }

    /** @test */
    public function it_can_get_today_orders_count()
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
            'status' => OrderStatus::SERVED->value,
            'total' => 8000,
        ]);

        $count = $this->orderService->getTodayOrdersCount($this->tenant->id);

        $this->assertEquals(2, $count);
    }

    /** @test */
    public function it_can_get_today_revenue()
    {
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

        Order::createWithNumber([
            'tenant_id' => $this->tenant->id,
            'table_id' => $this->table->id,
            'status' => OrderStatus::RECEIVED->value, // Not counted (not ready/served)
            'total' => 3000,
        ]);

        $revenue = $this->orderService->getTodayRevenue($this->tenant->id);

        $this->assertEquals(13000, $revenue); // 5000 + 8000
    }
}
