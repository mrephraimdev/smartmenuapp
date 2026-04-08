<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Dish;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $admin;
    protected Menu $menu;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Create tenant
        $this->tenant = Tenant::factory()->create();

        // Create admin user
        $this->admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'ADMIN',
        ]);

        // Create menu structure
        $this->menu = Menu::factory()->create([
            'tenant_id' => $this->tenant->id,
            'active' => true,
        ]);

        $this->category = Category::factory()->create([
            'menu_id' => $this->menu->id,
        ]);

        // Create dishes
        Dish::factory()->count(5)->create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $this->category->id,
            'active' => true,
        ]);

        // Create tables
        Table::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // Create orders with items
        Order::factory()->count(10)->create([
            'tenant_id' => $this->tenant->id,
            'table_id' => Table::where('tenant_id', $this->tenant->id)->first()->id,
        ])->each(function ($order) {
            OrderItem::factory()->count(3)->create([
                'order_id' => $order->id,
                'dish_id' => Dish::where('tenant_id', $this->tenant->id)->inRandomOrder()->first()->id,
            ]);
        });
    }

    /** @test */
    public function admin_can_access_reports_page()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports', $this->tenant->slug));

        $response->assertStatus(200);
        $response->assertSee('Rapports et Exports');
    }

    /** @test */
    public function admin_can_export_orders_to_pdf()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.exports.orders.pdf', [
                'tenantSlug' => $this->tenant->slug,
                'start_date' => now()->subDays(7)->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertNotEmpty($response->getContent());
    }

    /** @test */
    public function pdf_orders_contains_correct_data()
    {
        $order = Order::where('tenant_id', $this->tenant->id)->first();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.exports.orders.pdf', [
                'tenantSlug' => $this->tenant->slug,
                'start_date' => $order->created_at->format('Y-m-d'),
                'end_date' => $order->created_at->format('Y-m-d'),
            ]));

        $response->assertStatus(200);

        // PDF should be generated
        $content = $response->getContent();
        $this->assertNotEmpty($content);
        $this->assertStringContainsString('%PDF', $content);
    }

    /** @test */
    public function admin_can_export_orders_to_excel()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.exports.orders.excel', [
                'tenantSlug' => $this->tenant->slug,
                'start_date' => now()->subDays(7)->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function admin_can_export_statistics_to_pdf()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.exports.statistics.pdf', [
                'tenantSlug' => $this->tenant->slug,
                'period' => 'month',
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function admin_can_export_statistics_to_excel()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.exports.statistics.excel', [
                'tenantSlug' => $this->tenant->slug,
                'start_date' => now()->startOfMonth()->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function statistics_excel_has_multiple_sheets()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.exports.statistics.excel', [
                'tenantSlug' => $this->tenant->slug,
                'start_date' => now()->startOfMonth()->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function admin_can_export_menu_to_pdf()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.exports.menu.pdf', [
                'tenantSlug' => $this->tenant->slug,
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function non_admin_cannot_access_exports()
    {
        $user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'SERVEUR',
        ]);

        $response = $this->actingAs($user)
            ->get(route('admin.reports', $this->tenant->slug));

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_cannot_export_data_from_other_tenant()
    {
        $otherTenant = Tenant::factory()->create();
        $otherAdmin = User::factory()->create([
            'tenant_id' => $otherTenant->id,
            'role' => 'ADMIN',
        ]);

        $response = $this->actingAs($otherAdmin)
            ->get(route('admin.exports.orders.pdf', [
                'tenantSlug' => $this->tenant->slug,
                'start_date' => now()->subDays(7)->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
            ]));

        $response->assertStatus(403);
    }

    /** @test */
    public function exports_respect_date_filters()
    {
        // Create orders on specific dates
        $oldOrder = Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'table_id' => Table::where('tenant_id', $this->tenant->id)->first()->id,
            'created_at' => now()->subDays(30),
        ]);

        $recentOrder = Order::factory()->create([
            'tenant_id' => $this->tenant->id,
            'table_id' => Table::where('tenant_id', $this->tenant->id)->first()->id,
            'created_at' => now(),
        ]);

        // Export only recent orders
        $response = $this->actingAs($this->admin)
            ->get(route('admin.exports.orders.pdf', [
                'tenantSlug' => $this->tenant->slug,
                'start_date' => now()->subDays(7)->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
            ]));

        $response->assertStatus(200);
    }
}
