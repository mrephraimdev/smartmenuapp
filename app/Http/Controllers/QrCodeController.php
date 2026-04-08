<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Table;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    /**
     * Afficher la liste des QR codes pour un tenant (admin)
     */
    public function index($tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $tables = Table::where('tenant_id', $tenant->id)->orderBy('code')->get();

        $qrCodes = [];
        foreach ($tables as $table) {
            $menuUrl = url("/menu/{$tenant->id}/{$table->code}");
            $qrCodes[] = [
                'table' => $table,
                'url' => route('qrcode.show', [$tenant->id, $table->code]),
                'menu_url' => $menuUrl,
                'qr_image' => "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($menuUrl)
            ];
        }

        return view('admin.qrcodes', compact('tenant', 'qrCodes', 'tables'));
    }

    /**
     * Générer un QR code pour une table spécifique
     */
    public function generate($tenantId, $tableCode)
    {
        $tenant = Tenant::findOrFail($tenantId);
        $table = Table::where('tenant_id', $tenantId)
                     ->where('code', $tableCode)
                     ->firstOrFail();

        $menuUrl = url("/menu/{$tenantId}/{$tableCode}");

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
     * Affichage public de la page QR code pour impression
     */
    public function publicShow($tenantId, $tableCode)
    {
        $tenant = Tenant::findOrFail($tenantId);
        $table = Table::where('tenant_id', $tenantId)
                     ->where('code', $tableCode)
                     ->firstOrFail();

        $menuUrl = url("/menu/{$tenantId}/{$tableCode}");
        $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($menuUrl);

        return view('qrcode', compact('tenant', 'table', 'qrCodeUrl', 'menuUrl'));
    }

    /**
     * Générer un QR code en base64 SVG pour l'inclusion dans les PDF
     */
    private function generateQrBase64($url)
    {
        $svg = QrCode::format('svg')
                     ->size(300)
                     ->errorCorrection('H')
                     ->generate($url);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Télécharger le QR code en PDF
     */
    public function downloadPdf($tenantId, $tableCode)
    {
        $tenant = Tenant::findOrFail($tenantId);
        $table = Table::where('tenant_id', $tenantId)
                     ->where('code', $tableCode)
                     ->firstOrFail();

        $menuUrl = url("/menu/{$tenantId}/{$tableCode}");
        $qrCodeBase64 = $this->generateQrBase64($menuUrl);

        $pdf = Pdf::loadView('prints.qrcode-pdf', compact('tenant', 'table', 'qrCodeBase64', 'menuUrl'));
        $pdf->setPaper('A4', 'portrait');

        $filename = "QRCode-{$tenant->name}-Table-" . ($table->label ?? $table->code) . ".pdf";

        return $pdf->download($filename);
    }

    /**
     * Télécharger tous les QR codes d'un tenant en un seul PDF
     */
    public function downloadAllPdf($tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $tables = Table::where('tenant_id', $tenant->id)->orderBy('code')->get();

        // Pré-générer les QR codes en base64 pour chaque table
        $qrCodes = [];
        foreach ($tables as $table) {
            $menuUrl = url("/menu/{$tenant->id}/{$table->code}");
            $qrCodes[$table->code] = $this->generateQrBase64($menuUrl);
        }

        $pdf = Pdf::loadView('prints.qrcodes-all-pdf', compact('tenant', 'tables', 'qrCodes'));
        $pdf->setPaper('A4', 'portrait');

        $filename = "QRCodes-{$tenant->name}.pdf";

        return $pdf->download($filename);
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
