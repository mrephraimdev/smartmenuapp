<?php

namespace Tests\Feature;

use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PosSession;
use App\Models\Table;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $admin;
    protected User $cashier;

    protected function setUp(): void
    {
        parent::setUp();

        // Create tenant
        $this->tenant = Tenant::factory()->create();

        // Create admin and cashier users
        $this->admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'ADMIN',
        ]);

        $this->cashier = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'SERVEUR',
        ]);
    }

    /** @test */
    public function admin_can_access_pos_interface()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.pos.index', $this->tenant->slug));

        $response->assertStatus(200);
        $response->assertSee('Point de Vente');
    }

    /** @test */
    public function admin_can_open_pos_session()
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.pos.sessions.open', $this->tenant->slug), [
                'opening_float' => 50000,
                'opening_notes' => 'Session de test',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('pos_sessions', [
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->admin->id,
            'status' => 'OPEN',
            'opening_float' => 50000,
        ]);
    }

    /** @test */
    public function user_cannot_open_multiple_sessions()
    {
        // Open first session
        $this->actingAs($this->admin)
            ->postJson(route('admin.pos.sessions.open', $this->tenant->slug), [
                'opening_float' => 50000,
            ]);

        // Try to open second session
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.pos.sessions.open', $this->tenant->slug), [
                'opening_float' => 30000,
            ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
        ]);
    }

    /** @test */
    public function admin_can_close_pos_session()
    {
        // Create and open a session
        $session = PosSession::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->admin->id,
            'status' => 'OPEN',
            'opening_float' => 50000,
            'opened_at' => now(),
        ]);

        // Create some orders for the session
        $table = Table::factory()->create(['tenant_id' => $this->tenant->id]);
        $dish = Dish::factory()->create(['tenant_id' => $this->tenant->id]);

        $order = Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'table_id' => $table->id,
            'pos_session_id' => $session->id,
            'total' => 15000,
            'status' => 'SERVI',
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'dish_id' => $dish->id,
            'quantity' => 1,
            'unit_price' => 15000,
        ]);

        // Close session
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.pos.sessions.close', [$this->tenant->slug, $session->id]), [
                'actual_cash' => 65000, // 50000 + 15000
                'closing_notes' => 'Clôture test',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $session->refresh();
        $this->assertEquals('CLOSED', $session->status);
        $this->assertEquals(65000, $session->actual_cash);
        $this->assertEquals(65000, $session->expected_cash);
        $this->assertEquals(0, $session->cash_difference);
    }

    /** @test */
    public function closing_session_calculates_cash_difference()
    {
        $session = PosSession::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->admin->id,
            'status' => 'OPEN',
            'opening_float' => 50000,
            'opened_at' => now(),
        ]);

        // Create order with cash payment
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
            'quantity' => 1,
            'unit_price' => 20000,
        ]);

        // Close with incorrect cash (short 2000)
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.pos.sessions.close', [$this->tenant->slug, $session->id]), [
                'actual_cash' => 68000, // Expected: 70000 (50000 + 20000)
            ]);

        $response->assertStatus(200);

        $session->refresh();
        $this->assertEquals(70000, $session->expected_cash);
        $this->assertEquals(68000, $session->actual_cash);
        $this->assertEquals(-2000, $session->cash_difference); // Short 2000
    }

    /** @test */
    public function cannot_close_already_closed_session()
    {
        $session = PosSession::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->admin->id,
            'status' => 'CLOSED',
            'opened_at' => now()->subHours(8),
            'closed_at' => now(),
            'opening_float' => 50000,
            'actual_cash' => 50000,
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.pos.sessions.close', [$this->tenant->slug, $session->id]), [
                'actual_cash' => 60000,
            ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
        ]);
    }

    /** @test */
    public function admin_can_view_session_details()
    {
        $session = PosSession::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->admin->id,
            'status' => 'CLOSED',
            'opened_at' => now()->subHours(8),
            'closed_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.pos.sessions.show', [$this->tenant->slug, $session->id]));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_generate_z_report()
    {
        $session = PosSession::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->admin->id,
            'status' => 'CLOSED',
            'opened_at' => now()->subHours(8),
            'closed_at' => now(),
            'opening_float' => 50000,
            'actual_cash' => 50000,
            'total_sales' => 100000,
            'total_orders' => 10,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.pos.z-report', [$this->tenant->slug, $session->id]));

        $response->assertStatus(200);
        $response->assertSee('RAPPORT Z');
    }

    /** @test */
    public function admin_can_generate_x_report()
    {
        $session = PosSession::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->admin->id,
            'status' => 'OPEN',
            'opened_at' => now()->subHours(2),
            'opening_float' => 50000,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.pos.x-report', [$this->tenant->slug, $session->id]));

        $response->assertStatus(200);
        $response->assertSee('RAPPORT X');
    }

    /** @test */
    public function cannot_generate_z_report_for_open_session()
    {
        $session = PosSession::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->admin->id,
            'status' => 'OPEN',
            'opened_at' => now(),
            'opening_float' => 50000,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.pos.z-report', [$this->tenant->slug, $session->id]));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function admin_can_export_z_report_to_pdf()
    {
        $session = PosSession::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->admin->id,
            'status' => 'CLOSED',
            'opened_at' => now()->subHours(8),
            'closed_at' => now(),
            'opening_float' => 50000,
            'actual_cash' => 50000,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.pos.z-report.export', [$this->tenant->slug, $session->id]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function orders_are_linked_to_pos_session()
    {
        $session = PosSession::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->admin->id,
            'status' => 'OPEN',
        ]);

        $table = Table::factory()->create(['tenant_id' => $this->tenant->id]);
        $dish = Dish::factory()->create(['tenant_id' => $this->tenant->id]);

        $order = Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'table_id' => $table->id,
            'pos_session_id' => $session->id,
            'total' => 10000,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'pos_session_id' => $session->id,
        ]);

        $this->assertEquals($session->id, $order->posSession->id);
    }

    /** @test */
    public function admin_cannot_access_other_tenant_pos_session()
    {
        $otherTenant = Tenant::factory()->create();
        $otherSession = PosSession::factory()->create([
            'tenant_id' => $otherTenant->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.pos.sessions.show', [$this->tenant->slug, $otherSession->id]));

        $response->assertStatus(403);
    }
}
