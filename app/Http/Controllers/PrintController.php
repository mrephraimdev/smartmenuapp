<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Tenant;
use App\Services\PrintService;
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
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

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
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        if ($order->tenant_id !== $tenant->id) {
            abort(403, 'Accès non autorisé');
        }

        $html = $this->printService->generateReceipt($order);

        return response($html)->header('Content-Type', 'text/html');
    }

    /**
     * Print daily report
     */
    public function dailyReport(Request $request, string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $date = $request->get('date', now()->toDateString());

        $html = $this->printService->generateDailyReport($tenant, $date);

        return response($html)->header('Content-Type', 'text/html');
    }
}
