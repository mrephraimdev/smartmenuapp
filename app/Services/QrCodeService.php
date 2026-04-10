<?php

namespace App\Services;

use App\Models\Table;
use App\Models\Tenant;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    /**
     * Generate QR code for a table
     */
    public function generateQrCode(Table $table, array $options = []): string
    {
        $url = $this->getMenuUrl($table);

        $size = $options['size'] ?? 300;
        $format = $options['format'] ?? 'svg';
        $errorCorrection = $options['errorCorrection'] ?? 'H';

        $qrCode = QrCode::format($format)
            ->size($size)
            ->errorCorrection($errorCorrection)
            ->margin(1);

        if (isset($options['color'])) {
            $rgb = $this->hexToRgb($options['color']);
            $qrCode->color($rgb[0], $rgb[1], $rgb[2]);
        }

        if (isset($options['backgroundColor'])) {
            $rgb = $this->hexToRgb($options['backgroundColor']);
            $qrCode->backgroundColor($rgb[0], $rgb[1], $rgb[2]);
        }

        return $qrCode->generate($url);
    }

    /**
     * Generate QR code and save to file
     */
    public function saveQrCode(Table $table, array $options = []): string
    {
        $qrCode = $this->generateQrCode($table, array_merge($options, ['format' => 'png']));

        $filename = sprintf(
            'qrcodes/tenant-%d/table-%s.png',
            $table->tenant_id,
            $table->code
        );

        Storage::disk('public')->put($filename, $qrCode);

        return Storage::disk('public')->url($filename);
    }

    /**
     * Generate bulk QR codes for all tables of a tenant
     */
    public function generateBulkQrCodes(Tenant $tenant, array $options = []): array
    {
        $results = [];

        foreach ($tenant->tables as $table) {
            $results[$table->code] = [
                'table' => $table,
                'qr_code' => $this->generateQrCode($table, $options),
                'url' => $this->getMenuUrl($table),
            ];
        }

        return $results;
    }

    /**
     * Save bulk QR codes and return file paths
     */
    public function saveBulkQrCodes(Tenant $tenant, array $options = []): array
    {
        $results = [];

        foreach ($tenant->tables as $table) {
            $results[$table->code] = [
                'table' => $table,
                'file_url' => $this->saveQrCode($table, $options),
                'menu_url' => $this->getMenuUrl($table),
            ];
        }

        return $results;
    }

    /**
     * Generate QR code as base64 for embedding
     */
    public function getQrCodeBase64(Table $table, array $options = []): string
    {
        $qrCode = $this->generateQrCode($table, array_merge($options, ['format' => 'png']));
        return 'data:image/png;base64,' . base64_encode($qrCode);
    }

    /**
     * Get menu URL for a table
     */
    public function getMenuUrl(Table $table): string
    {
        $tenant = $table->tenant;

        return url("/menu-client?tenant={$tenant->id}&table={$table->code}");
    }

    /**
     * Generate printable QR code sheet (multiple tables)
     */
    public function generatePrintSheet(Tenant $tenant, array $tableIds = [], array $options = []): array
    {
        $tables = $tenant->tables();

        if (!empty($tableIds)) {
            $tables->whereIn('id', $tableIds);
        }

        $tables = $tables->get();

        $printData = [];
        foreach ($tables as $table) {
            $printData[] = [
                'table_name' => $table->name,
                'table_code' => $table->code,
                'qr_base64' => $this->getQrCodeBase64($table, $options),
                'menu_url' => $this->getMenuUrl($table),
            ];
        }

        return [
            'tenant' => $tenant,
            'tables' => $printData,
            'generated_at' => now(),
        ];
    }

    /**
     * Delete stored QR code file
     */
    public function deleteQrCode(Table $table): bool
    {
        $filename = sprintf(
            'qrcodes/tenant-%d/table-%s.png',
            $table->tenant_id,
            $table->code
        );

        if (Storage::disk('public')->exists($filename)) {
            return Storage::disk('public')->delete($filename);
        }

        return false;
    }

    /**
     * Regenerate all QR codes for a tenant (e.g., after domain change)
     */
    public function regenerateAllQrCodes(Tenant $tenant, array $options = []): int
    {
        $count = 0;

        foreach ($tenant->tables as $table) {
            $this->saveQrCode($table, $options);
            $count++;
        }

        return $count;
    }

    /**
     * Convert hex color to RGB array
     */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }
}
