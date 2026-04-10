<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Tenant;
use App\Services\ExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ExportController extends Controller
{
    public function __construct(
        private ExportService $exportService
    ) {}

    /**
     * Validate that the authenticated user has access to the tenant.
     */
    protected function validateTenantAccess(Tenant $tenant): void
    {
        $user = auth()->user();

        // Super admins can access all tenants
        if ($user->hasRole('SUPER_ADMIN')) {
            return;
        }

        // Regular admins can only access their own tenant
        if ($user->tenant_id !== $tenant->id) {
            abort(403, 'Vous n\'avez pas accès aux données de ce tenant.');
        }
    }

    /**
     * Display reports and exports page
     */
    public function index(string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $this->validateTenantAccess($tenant);

        $menus = $tenant->menus;

        return view('admin.reports.index', [
            'tenantSlug' => $tenantSlug,
            'tenant' => $tenant,
            'menus' => $menus
        ]);
    }

    /**
     * Export orders to CSV
     */
    public function exportOrders(Request $request, string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $this->validateTenantAccess($tenant);

        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        return $this->exportService->exportOrdersToCsv($tenant->id, $startDate, $endDate);
    }

    /**
     * Export order details to CSV
     */
    public function exportOrderDetails(Request $request, string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $this->validateTenantAccess($tenant);

        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        return $this->exportService->exportOrderDetailsToCsv($tenant->id, $startDate, $endDate);
    }

    /**
     * Export menu to CSV
     */
    public function exportMenu(string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $this->validateTenantAccess($tenant);

        return $this->exportService->exportDishesToCsv($tenant->id);
    }

    /**
     * Export reservations to CSV
     */
    public function exportReservations(Request $request, string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $this->validateTenantAccess($tenant);

        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        return $this->exportService->exportReservationsToCsv($tenant->id, $startDate, $endDate);
    }

    /**
     * Export reviews to CSV
     */
    public function exportReviews(string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $this->validateTenantAccess($tenant);

        return $this->exportService->exportReviewsToCsv($tenant->id);
    }

    /**
     * Get sales report data (for PDF generation)
     */
    public function salesReport(Request $request, string $tenantSlug): JsonResponse
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $this->validateTenantAccess($tenant);

        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $data = $this->exportService->getSalesReportData($tenant->id, $startDate, $endDate);
        $data['tenant'] = [
            'name' => $tenant->name,
            'address' => $tenant->address,
            'phone' => $tenant->phone
        ];

        return response()->json([
            'success' => true,
            'report' => $data
        ]);
    }

    /**
     * Export orders to PDF
     */
    public function exportOrdersPDF(Request $request, string $tenantSlug): Response
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $this->validateTenantAccess($tenant);

        $startDate = Carbon::parse($request->get('start_date', now()->startOfMonth()));
        $endDate = Carbon::parse($request->get('end_date', now()));

        return $this->exportService->exportOrdersPDF($tenant, $startDate, $endDate);
    }

    /**
     * Export orders to Excel
     */
    public function exportOrdersExcel(Request $request, string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $this->validateTenantAccess($tenant);

        $startDate = Carbon::parse($request->get('start_date', now()->startOfMonth()));
        $endDate = Carbon::parse($request->get('end_date', now()));

        return $this->exportService->exportOrdersExcel($tenant, $startDate, $endDate);
    }

    /**
     * Export statistics to PDF
     */
    public function exportStatisticsPDF(Request $request, string $tenantSlug): Response
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $this->validateTenantAccess($tenant);

        $period = $request->get('period', 'month');

        return $this->exportService->exportStatisticsPDF($tenant, $period);
    }

    /**
     * Export statistics to Excel
     */
    public function exportStatisticsExcel(Request $request, string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $this->validateTenantAccess($tenant);

        $startDate = Carbon::parse($request->get('start_date', now()->startOfMonth()));
        $endDate = Carbon::parse($request->get('end_date', now()));

        return $this->exportService->exportStatisticsExcel($tenant, $startDate, $endDate);
    }

    /**
     * Export menu to PDF
     */
    public function exportMenuPDF(Request $request, string $tenantSlug): Response
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $this->validateTenantAccess($tenant);

        $menuId = $request->get('menu_id');

        if (!$menuId) {
            // Get the first active menu
            $menu = $tenant->menus()->where('active', true)->first();
        } else {
            $menu = Menu::where('tenant_id', $tenant->id)->findOrFail($menuId);
        }

        if (!$menu) {
            abort(404, 'Aucun menu actif trouvé');
        }

        return $this->exportService->exportMenuPDF($menu);
    }
}
