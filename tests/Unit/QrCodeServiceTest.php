<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Table;
use App\Services\QrCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

class QrCodeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected QrCodeService $qrCodeService;
    protected Tenant $tenant;
    protected Table $table;

    protected function setUp(): void
    {
        parent::setUp();

        $this->qrCodeService = new QrCodeService();

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

        Storage::fake('public');
    }

    /** @test */
    public function it_can_generate_qr_code_svg()
    {
        $qrCode = $this->qrCodeService->generateQrCode($this->table);

        $this->assertNotEmpty($qrCode);
        $this->assertStringContainsString('<svg', $qrCode);
    }

    /** @test */
    public function it_can_generate_qr_code_with_custom_size()
    {
        $qrCode = $this->qrCodeService->generateQrCode($this->table, [
            'size' => 500,
        ]);

        $this->assertNotEmpty($qrCode);
        // SVG should contain width/height attributes
        $this->assertStringContainsString('500', $qrCode);
    }

    /** @test */
    public function it_generates_correct_menu_url()
    {
        $url = $this->qrCodeService->getMenuUrl($this->table);

        $this->assertStringContainsString('tenant=' . $this->tenant->id, $url);
        $this->assertStringContainsString('table=' . $this->table->code, $url);
    }

    /** @test */
    public function it_can_get_qr_code_as_base64()
    {
        $base64 = $this->qrCodeService->getQrCodeBase64($this->table);

        $this->assertStringStartsWith('data:image/png;base64,', $base64);
    }

    /** @test */
    public function it_can_save_qr_code_to_storage()
    {
        $url = $this->qrCodeService->saveQrCode($this->table);

        $expectedPath = sprintf(
            'qrcodes/tenant-%d/table-%s.png',
            $this->tenant->id,
            $this->table->code
        );

        Storage::disk('public')->assertExists($expectedPath);
    }

    /** @test */
    public function it_can_generate_bulk_qr_codes()
    {
        $table2 = Table::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'T02',
            'label' => 'Table 2',
            'capacity' => 6,
            'is_active' => true,
        ]);

        $table3 = Table::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'T03',
            'label' => 'Table 3',
            'capacity' => 2,
            'is_active' => true,
        ]);

        $this->tenant->load('tables');
        $results = $this->qrCodeService->generateBulkQrCodes($this->tenant);

        $this->assertCount(3, $results);
        $this->assertArrayHasKey('T01', $results);
        $this->assertArrayHasKey('T02', $results);
        $this->assertArrayHasKey('T03', $results);

        foreach ($results as $code => $data) {
            $this->assertArrayHasKey('table', $data);
            $this->assertArrayHasKey('qr_code', $data);
            $this->assertArrayHasKey('url', $data);
            $this->assertNotEmpty($data['qr_code']);
        }
    }

    /** @test */
    public function it_can_save_bulk_qr_codes()
    {
        $table2 = Table::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'T02',
            'label' => 'Table 2',
            'capacity' => 6,
            'is_active' => true,
        ]);

        $this->tenant->load('tables');
        $results = $this->qrCodeService->saveBulkQrCodes($this->tenant);

        $this->assertCount(2, $results);

        foreach ($results as $code => $data) {
            $this->assertArrayHasKey('file_url', $data);
            $this->assertArrayHasKey('menu_url', $data);
        }

        Storage::disk('public')->assertExists(sprintf(
            'qrcodes/tenant-%d/table-T01.png',
            $this->tenant->id
        ));
        Storage::disk('public')->assertExists(sprintf(
            'qrcodes/tenant-%d/table-T02.png',
            $this->tenant->id
        ));
    }

    /** @test */
    public function it_can_delete_qr_code()
    {
        // First save the QR code
        $this->qrCodeService->saveQrCode($this->table);

        $expectedPath = sprintf(
            'qrcodes/tenant-%d/table-%s.png',
            $this->tenant->id,
            $this->table->code
        );

        Storage::disk('public')->assertExists($expectedPath);

        // Now delete it
        $result = $this->qrCodeService->deleteQrCode($this->table);

        $this->assertTrue($result);
        Storage::disk('public')->assertMissing($expectedPath);
    }

    /** @test */
    public function it_returns_false_when_deleting_nonexistent_qr_code()
    {
        $result = $this->qrCodeService->deleteQrCode($this->table);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_can_regenerate_all_qr_codes()
    {
        $table2 = Table::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'T02',
            'label' => 'Table 2',
            'capacity' => 6,
            'is_active' => true,
        ]);

        $this->tenant->load('tables');
        $count = $this->qrCodeService->regenerateAllQrCodes($this->tenant);

        $this->assertEquals(2, $count);

        Storage::disk('public')->assertExists(sprintf(
            'qrcodes/tenant-%d/table-T01.png',
            $this->tenant->id
        ));
        Storage::disk('public')->assertExists(sprintf(
            'qrcodes/tenant-%d/table-T02.png',
            $this->tenant->id
        ));
    }

    /** @test */
    public function it_can_generate_print_sheet()
    {
        $table2 = Table::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'T02',
            'label' => 'Table 2',
            'capacity' => 6,
            'is_active' => true,
        ]);

        $this->tenant->load('tables');
        $printData = $this->qrCodeService->generatePrintSheet($this->tenant);

        $this->assertArrayHasKey('tenant', $printData);
        $this->assertArrayHasKey('tables', $printData);
        $this->assertArrayHasKey('generated_at', $printData);
        $this->assertCount(2, $printData['tables']);

        foreach ($printData['tables'] as $tableData) {
            $this->assertArrayHasKey('table_code', $tableData);
            $this->assertArrayHasKey('qr_base64', $tableData);
            $this->assertArrayHasKey('menu_url', $tableData);
            $this->assertStringStartsWith('data:image/png;base64,', $tableData['qr_base64']);
        }
    }

    /** @test */
    public function it_can_generate_print_sheet_for_specific_tables()
    {
        $table2 = Table::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'T02',
            'label' => 'Table 2',
            'capacity' => 6,
            'is_active' => true,
        ]);

        $table3 = Table::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'T03',
            'label' => 'Table 3',
            'capacity' => 2,
            'is_active' => true,
        ]);

        $this->tenant->load('tables');
        $printData = $this->qrCodeService->generatePrintSheet($this->tenant, [$this->table->id, $table2->id]);

        $this->assertCount(2, $printData['tables']);

        $tableCodes = array_column($printData['tables'], 'table_code');
        $this->assertContains('T01', $tableCodes);
        $this->assertContains('T02', $tableCodes);
        $this->assertNotContains('T03', $tableCodes);
    }

    /** @test */
    public function it_can_generate_qr_code_with_custom_colors()
    {
        $qrCode = $this->qrCodeService->generateQrCode($this->table, [
            'color' => '#FF0000',
            'backgroundColor' => '#FFFFFF',
        ]);

        $this->assertNotEmpty($qrCode);
        // The QR code should be generated (we can't easily test the color in SVG)
    }
}
