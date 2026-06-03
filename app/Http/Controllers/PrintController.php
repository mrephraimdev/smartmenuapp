<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Tenant;
use App\Services\PrintService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    public function __construct(
        private PrintService $printService
    ) {}

    /**
     * Print kitchen ticket for an order
     */
    public function kitchenTicket(string $tenantSlug, Order $order)
    {
        $tenant = Tenant::findBySlug($tenantSlug);

        if ($order->tenant_id !== $tenant->id) {
            abort(403, 'Accès non autorisé');
        }

        $html = $this->printService->generateKitchenTicket($order);

        return response($html)->header('Content-Type', 'text/html');
    }

    /**
     * Print customer receipt
     */
    public function receipt(string $tenantSlug, Order $order)
    {
        $tenant = Tenant::findBySlug($tenantSlug);

        if ($order->tenant_id !== $tenant->id) {
            abort(403, 'Accès non autorisé');
        }

        $html = $this->printService->generateReceipt($order);

        return response($html)->header('Content-Type', 'text/html');
    }

    /**
     * Print daily report (opens print dialog on desktop, shows PDF button on mobile)
     */
    public function dailyReport(Request $request, string $tenantSlug)
    {
        $tenant = Tenant::findBySlug($tenantSlug);
        $date = $request->get('date', now()->toDateString());

        $pdfUrl = route('admin.print.daily-report.pdf', $tenantSlug) . '?date=' . $date;
        $html = $this->printService->generateDailyReport($tenant, $date, $pdfUrl);

        return response($html)->header('Content-Type', 'text/html');
    }

    /**
     * Export daily report as PDF (for mobile or archiving)
     */
    public function dailyReportPdf(Request $request, string $tenantSlug)
    {
        $tenant = Tenant::findBySlug($tenantSlug);
        $date = $request->get('date', now()->toDateString());

        $html = $this->printService->generateDailyReportPdf($tenant, $date);

        $pdf = Pdf::loadHTML($html)
            ->setPaper([0, 0, 226.77, 841.89]) // 80mm wide
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'courier');

        $filename = 'rapport-' . $tenant->slug . '-' . $date . '.pdf';

        return $pdf->download($filename);
    }
}
