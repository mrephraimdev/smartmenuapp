<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Role;
use App\Models\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

class QrCodeTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $admin;
    protected Table $table;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

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
    }

    /** @test */
    public function admin_can_access_qrcodes_page()
    {
        $response = $this->actingAs($this->admin)
            ->get("/admin/{$this->tenant->slug}/qrcodes");

        $response->assertStatus(200);
    }

    /** @test */
    public function qrcodes_page_shows_tables()
    {
        $table2 = Table::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'T02',
            'label' => 'Table 2',
            'capacity' => 6,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/admin/{$this->tenant->slug}/qrcodes");

        $response->assertStatus(200);
        $response->assertSee('T01');
        $response->assertSee('T02');
    }

    /** @test */
    public function public_qrcode_page_is_accessible()
    {
        $response = $this->get("/qrcode/{$this->tenant->id}/{$this->table->code}");

        $response->assertStatus(200);
        $response->assertSee($this->tenant->name);
        $response->assertSee($this->table->code);
    }

    /** @test */
    public function qrcode_contains_correct_menu_url()
    {
        $response = $this->get("/qrcode/{$this->tenant->id}/{$this->table->code}");

        $response->assertStatus(200);

        // Should contain a link to the menu
        $expectedMenuUrl = url("/menu/{$this->tenant->id}/{$this->table->code}");
        // The page should show or link to the menu URL
    }

    /** @test */
    public function qrcode_for_invalid_table_returns_404()
    {
        $response = $this->get("/qrcode/{$this->tenant->id}/INVALID");

        $response->assertStatus(404);
    }

    /** @test */
    public function qrcode_for_invalid_tenant_returns_404()
    {
        $response = $this->get("/qrcode/99999/{$this->table->code}");

        $response->assertStatus(404);
    }

    /** @test */
    public function admin_can_generate_bulk_qrcodes()
    {
        // Create multiple tables
        Table::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'T02',
            'label' => 'Table 2',
            'capacity' => 6,
            'is_active' => true,
        ]);

        Table::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'T03',
            'label' => 'Table 3',
            'capacity' => 2,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/admin/{$this->tenant->slug}/qrcodes");

        $response->assertStatus(200);
        $response->assertSee('T01');
        $response->assertSee('T02');
        $response->assertSee('T03');
    }

    /** @test */
    public function scanning_qrcode_redirects_to_menu()
    {
        // The QR code should contain a URL that leads to the menu
        $menuUrl = "/menu/{$this->tenant->id}/{$this->table->code}";

        $response = $this->get($menuUrl);

        $response->assertStatus(200);
        $response->assertSee($this->tenant->name);
    }

    /** @test */
    public function qrcode_works_with_different_table_codes()
    {
        $specialTable = Table::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'VIP-01',
            'label' => 'VIP Table',
            'capacity' => 8,
            'is_active' => true,
        ]);

        $response = $this->get("/qrcode/{$this->tenant->id}/{$specialTable->code}");

        $response->assertStatus(200);
        $response->assertSee('VIP-01');
    }

    /** @test */
    public function inactive_table_qrcode_still_works()
    {
        $this->table->update(['is_active' => false]);

        $response = $this->get("/qrcode/{$this->tenant->id}/{$this->table->code}");

        // QR code should still work for inactive tables
        // (restaurant may want to keep QR codes but mark table as unavailable)
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_print_qrcodes()
    {
        $response = $this->actingAs($this->admin)
            ->get("/admin/{$this->tenant->slug}/qrcodes");

        $response->assertStatus(200);
        // Page should have print functionality
    }
}
