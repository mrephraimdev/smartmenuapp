<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Table;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    /**
     * Générer un QR code pour une table spécifique
     */
    public function generate($tenantId, $tableCode)
    {
        $tenant = Tenant::findOrFail($tenantId);
        $table = Table::where('tenant_id', $tenantId)
                     ->where('code', $tableCode)
                     ->firstOrFail();

        $menuUrl = url("/menu?tenant={$tenantId}&table={$tableCode}");

        // Générer le QR code en SVG (plus fiable que PNG)
        $qrCode = QrCode::size(300)
                        ->format('svg')
                        ->errorCorrection('H')
                        ->generate($menuUrl);

        return response($qrCode)->header('Content-Type', 'image/svg+xml');
    }

    /**
     * Afficher la page avec le QR code pour impression
     */
    public function show($tenantId, $tableCode)
    {
        $tenant = Tenant::findOrFail($tenantId);
        $table = Table::where('tenant_id', $tenantId)
                     ->where('code', $tableCode)
                     ->firstOrFail();

        $qrCodeUrl = url("/qrcode/generate/{$tenantId}/{$tableCode}");
        $menuUrl = url("/menu?tenant={$tenantId}&table={$tableCode}");

        return view('qrcode', compact('tenant', 'table', 'qrCodeUrl', 'menuUrl'));
    }

    /**
     * Générer tous les QR codes d'un tenant
     */
    public function generateAll($tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $tables = Table::where('tenant_id', $tenant->id)->get();

        $qrCodes = [];
        foreach ($tables as $table) {
            $qrCodes[] = [
                'table' => $table,
                'url' => url("/qrcode/{$tenant->id}/{$table->code}"),
                'menu_url' => url("/menu?tenant={$tenant->id}&table={$table->code}")
            ];
        }

        return view('admin.qrcodes', compact('tenant', 'qrCodes'));
    }
}
