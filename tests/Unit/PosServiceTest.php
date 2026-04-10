<?php

namespace Tests\Unit;

use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PosSession;
use App\Models\Table;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PosService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PosService $posService;
    protected Tenant $tenant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->posService = app(PosService::class);
        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'ADMIN',
        ]);
    }

    /** @test */
    public function it_can_open_a_session()
    {
        $session = $this->posService->openSession($this->tenant, $this->user, 50000, 'Test opening');

        $this->assertInstanceOf(PosSession::class, $session);
        $this->assertEquals('OPEN', $session->status);
        $this->assertEquals(50000, $session->opening_float);
        $this->assertEquals('Test opening', $session->opening_notes);
        $this->assertNotNull($session->session_number);
        $this->assertNotNull($session->opened_at);
    }

    /** @test */
    public function it_generates_unique_session_numbers()
    {
        $session1 = $this->posService->openSession($this->tenant, $this->user, 50000);

        $user2 = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'ADMIN',
        ]);

        $session2 = $this->posService->openSession($this->tenant, $user2, 30000);

        $this->assertNotEquals($session1->session_number, $session2->session_number);
        $this->assertStringContainsString('POS-', $session1->session_number);
        $this->assertStringContainsString('POS-', $session2->session_number);
    }

    /** @test */
    public function it_throws_exception_when_user_has_open_session()
    {
        $this->posService->openSession($this->tenant, $this->user, 50000);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Vous avez déjà une session ouverte');

        $this->posService->openSession($this->tenant, $this->user, 30000);
    }

    /** @test */
    public function it_can_get_current_open_session()
    {
        $createdSession = $this->posService->openSession($this->tenant, $this->user, 50000);

        $currentSession = $this->posService->getCurrentSession($this->tenant, $this->user);

        $this->assertNotNull($currentSession);
        $this->assertEquals($createdSession->id, $currentSession->id);
    }

    /** @test */
    public function it_returns_null_when_no_open_session()
    {
        $currentSession = $this->posService->getCurrentSession($this->tenant, $this->user);

        $this->assertNull($currentSession);
    }

    /** @test */
    public function it_can_close_a_session()
    {
        $session = $this->posService->openSession($this->tenant, $this->user, 50000);

        // Create orders for the session
        $table = Table::factory()->create(['tenant_id' => $this->tenant->id]);
        $dish = Dish::factory()->create(['tenant_id' => $this->tenant->id]);

        $order = Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'table_id' => $table->id,
            'pos_session_id' => $session->id,
            'total' => 20000,
            'status' => 'SERVI',
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'dish_id' => $dish->id,
            'quantity' => 2,
            'unit_price' => 10000,
        ]);

        $closedSession = $this->posService->closeSession($session, 70000, 'Test closing');

        $this->assertEquals('CLOSED', $closedSession->status);
        $this->assertEquals(70000, $closedSession->actual_cash);
        $this->assertEquals(70000, $closedSession->expected_cash); // 50000 + 20000
        $this->assertEquals(0, $closedSession->cash_difference);
        $this->assertEquals(20000, $closedSession->total_sales);
        $this->assertEquals(1, $closedSession->total_orders);
        $this->assertEquals(2, $closedSession->total_items);
        $this->assertNotNull($closedSession->closed_at);
    }

    /** @test */
    public function it_calculates_cash_difference_correctly()
    {
        $session = $this->posService->openSession($this->tenant, $this->user, 50000);

        // Create order
        $table = Table::factory()->create(['tenant_id' => $this->tenant->id]);
        $dish = Dish::factory()->create(['tenant_id' => $this->tenant->id]);

        Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'table_id' => $table->id,
            'pos_session_id' => $session->id,
            'total' => 30000,
            'status' => 'SERVI',
        ]);

        // Close with 5000 short
        $closedSession = $this->posService->closeSession($session, 75000);

        $this->assertEquals(80000, $closedSession->expected_cash); // 50000 + 30000
        $this->assertEquals(75000, $closedSession->actual_cash);
        $this->assertEquals(-5000, $closedSession->cash_difference);
    }

    /** @test */
    public function it_throws_exception_when_closing_already_closed_session()
    {
        $session = PosSession::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'status' => 'CLOSED',
            'opened_at' => now()->subHours(8),
            'closed_at' => now(),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cette session est déjà fermée');

        $this->posService->closeSession($session, 50000);
    }

    /** @test */
    public function it_can_generate_z_report()
    {
        $session = PosSession::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'status' => 'CLOSED',
            'opened_at' => now()->subHours(8),
            'closed_at' => now(),
            'opening_float' => 50000,
            'actual_cash' => 150000,
            'expected_cash' => 150000,
            'total_sales' => 100000,
            'total_orders' => 10,
            'total_items' => 25,
        ]);

        $report = $this->posService->generateZReport($session);

        $this->assertIsArray($report);
        $this->assertArrayHasKey('session', $report);
        $this->assertArrayHasKey('orders', $report);
        $this->assertArrayHasKey('summary', $report);
        $this->assertEquals($session->id, $report['session']->id);
    }

    /** @test */
    public function it_throws_exception_generating_z_report_for_open_session()
    {
        $session = PosSession::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'status' => 'OPEN',
            'opened_at' => now(),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('La session doit être fermée');

        $this->posService->generateZReport($session);
    }

    /** @test */
    public function it_can_generate_x_report()
    {
        $session = PosSession::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'status' => 'OPEN',
            'opened_at' => now()->subHours(2),
            'opening_float' => 50000,
        ]);

        // Create some orders
        $table = Table::factory()->create(['tenant_id' => $this->tenant->id]);
        $dish = Dish::factory()->create(['tenant_id' => $this->tenant->id]);

        Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'table_id' => $table->id,
            'pos_session_id' => $session->id,
            'total' => 15000,
            'status' => 'SERVI',
        ]);

        $report = $this->posService->generateXReport($session);

        $this->assertIsArray($report);
        $this->assertArrayHasKey('session', $report);
        $this->assertArrayHasKey('current_totals', $report);
        $this->assertArrayHasKey('expected_cash', $report);
        $this->assertEquals(65000, $report['expected_cash']); // 50000 + 15000
    }

    /** @test */
    public function it_throws_exception_generating_x_report_for_closed_session()
    {
        $session = PosSession::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'status' => 'CLOSED',
            'opened_at' => now()->subHours(8),
            'closed_at' => now(),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('La session est fermée');

        $this->posService->generateXReport($session);
    }

    /** @test */
    public function it_can_get_session_statistics()
    {
        // Create multiple sessions
        PosSession::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'CLOSED',
            'opened_at' => now()->subDays(5),
            'closed_at' => now()->subDays(5)->addHours(8),
            'total_sales' => 100000,
            'total_orders' => 10,
        ]);

        $statistics = $this->posService->getSessionStatistics(
            $this->tenant,
            now()->subWeek(),
            now()
        );

        $this->assertIsArray($statistics);
        $this->assertArrayHasKey('total_sales', $statistics);
        $this->assertArrayHasKey('total_orders', $statistics);
        $this->assertArrayHasKey('total_sessions', $statistics);
        $this->assertEquals(300000, $statistics['total_sales']);
        $this->assertEquals(30, $statistics['total_orders']);
        $this->assertEquals(3, $statistics['total_sessions']);
    }
}
