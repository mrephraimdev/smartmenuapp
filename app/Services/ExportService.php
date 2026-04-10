<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Dish;
use App\Models\Menu;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\Tenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    /**
     * Export orders to CSV
     */
    public function exportOrdersToCsv(int $tenantId, ?string $startDate = null, ?string $endDate = null): StreamedResponse
    {
        $orders = $this->getOrdersForExport($tenantId, $startDate, $endDate);

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="commandes_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');

            // BOM for Excel UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header row
            fputcsv($file, [
                'Numéro',
                'Date',
                'Heure',
                'Table',
                'Statut',
                'Nombre d\'articles',
                'Total (FCFA)',
                'Notes'
            ], ';');

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order_number,
                    $order->created_at->format('d/m/Y'),
                    $order->created_at->format('H:i'),
                    $order->table->label ?? $order->table->code ?? 'N/A',
                    $order->getStatusLabel(),
                    $order->items->sum('quantity'),
                    number_format($order->total, 0, ',', ' '),
                    $order->notes ?? ''
                ], ';');
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export orders details to CSV
     */
    public function exportOrderDetailsToCsv(int $tenantId, ?string $startDate = null, ?string $endDate = null): StreamedResponse
    {
        $orders = $this->getOrdersForExport($tenantId, $startDate, $endDate);

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="details_commandes_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'Numéro commande',
                'Date',
                'Table',
                'Plat',
                'Variante',
                'Quantité',
                'Prix unitaire',
                'Sous-total',
                'Notes'
            ], ';');

            foreach ($orders as $order) {
                foreach ($order->items as $item) {
                    fputcsv($file, [
                        $order->order_number,
                        $order->created_at->format('d/m/Y H:i'),
                        $order->table->label ?? $order->table->code ?? 'N/A',
                        $item->dish->name ?? 'N/A',
                        $item->variant->name ?? '-',
                        $item->quantity,
                        number_format($item->unit_price, 0, ',', ' '),
                        number_format($item->unit_price * $item->quantity, 0, ',', ' '),
                        $item->notes ?? ''
                    ], ';');
                }
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export menu/dishes to CSV
     */
    public function exportDishesToCsv(int $tenantId): StreamedResponse
    {
        $dishes = Dish::where('tenant_id', $tenantId)
            ->with(['category', 'variants', 'options'])
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="menu_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($dishes) {
            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'Catégorie',
                'Nom',
                'Description',
                'Prix de base',
                'Actif',
                'Stock',
                'Variantes',
                'Options',
                'Allergènes',
                'Tags'
            ], ';');

            foreach ($dishes as $dish) {
                fputcsv($file, [
                    $dish->category->name ?? 'N/A',
                    $dish->name,
                    $dish->description ?? '',
                    number_format($dish->price_base, 0, ',', ' '),
                    $dish->active ? 'Oui' : 'Non',
                    $dish->stock_quantity ?? 'N/A',
                    $dish->variants->pluck('name')->implode(', '),
                    $dish->options->pluck('name')->implode(', '),
                    is_array($dish->allergens) ? implode(', ', $dish->allergens) : '',
                    is_array($dish->tags) ? implode(', ', $dish->tags) : ''
                ], ';');
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export reservations to CSV
     */
    public function exportReservationsToCsv(int $tenantId, ?string $startDate = null, ?string $endDate = null): StreamedResponse
    {
        $query = Reservation::where('tenant_id', $tenantId)->with('table');

        if ($startDate) {
            $query->whereDate('reservation_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('reservation_date', '<=', $endDate);
        }

        $reservations = $query->orderBy('reservation_date')->orderBy('reservation_time')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="reservations_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($reservations) {
            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'Code confirmation',
                'Date',
                'Heure',
                'Table',
                'Client',
                'Téléphone',
                'Email',
                'Personnes',
                'Statut',
                'Demandes spéciales'
            ], ';');

            foreach ($reservations as $res) {
                fputcsv($file, [
                    $res->confirmation_code,
                    $res->reservation_date->format('d/m/Y'),
                    $res->reservation_time->format('H:i'),
                    $res->table->label ?? $res->table->code ?? 'N/A',
                    $res->customer_name,
                    $res->customer_phone,
                    $res->customer_email ?? '',
                    $res->party_size,
                    $res->status_label,
                    $res->special_requests ?? ''
                ], ';');
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export reviews to CSV
     */
    public function exportReviewsToCsv(int $tenantId): StreamedResponse
    {
        $reviews = Review::where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="avis_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($reviews) {
            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'Date',
                'Client',
                'Note globale',
                'Cuisine',
                'Service',
                'Ambiance',
                'Commentaire',
                'Publié',
                'Réponse'
            ], ';');

            foreach ($reviews as $review) {
                fputcsv($file, [
                    $review->created_at->format('d/m/Y'),
                    $review->display_name,
                    $review->overall_rating,
                    $review->food_rating,
                    $review->service_rating,
                    $review->ambiance_rating,
                    $review->comment ?? '',
                    $review->is_published ? 'Oui' : 'Non',
                    $review->response ?? ''
                ], ';');
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Generate sales report data
     */
    public function getSalesReportData(int $tenantId, string $startDate, string $endDate): array
    {
        $orders = $this->getOrdersForExport($tenantId, $startDate, $endDate);

        $completedOrders = $orders->whereIn('status', ['PRET', 'SERVI']);

        // Daily breakdown
        $dailySales = $completedOrders->groupBy(fn($o) => $o->created_at->format('Y-m-d'))
            ->map(fn($dayOrders) => [
                'date' => $dayOrders->first()->created_at->format('d/m/Y'),
                'orders' => $dayOrders->count(),
                'revenue' => $dayOrders->sum('total')
            ])->values();

        // Top dishes
        $dishSales = [];
        foreach ($completedOrders as $order) {
            foreach ($order->items as $item) {
                $dishName = $item->dish->name ?? 'Unknown';
                if (!isset($dishSales[$dishName])) {
                    $dishSales[$dishName] = ['quantity' => 0, 'revenue' => 0];
                }
                $dishSales[$dishName]['quantity'] += $item->quantity;
                $dishSales[$dishName]['revenue'] += $item->unit_price * $item->quantity;
            }
        }
        arsort($dishSales);

        return [
            'period' => [
                'start' => Carbon::parse($startDate)->format('d/m/Y'),
                'end' => Carbon::parse($endDate)->format('d/m/Y')
            ],
            'summary' => [
                'total_orders' => $completedOrders->count(),
                'total_revenue' => $completedOrders->sum('total'),
                'average_order' => $completedOrders->avg('total') ?? 0,
                'total_items' => $completedOrders->flatMap->items->sum('quantity')
            ],
            'daily_sales' => $dailySales,
            'top_dishes' => collect($dishSales)->take(10)->map(fn($data, $name) => [
                'name' => $name,
                'quantity' => $data['quantity'],
                'revenue' => $data['revenue']
            ])->values()
        ];
    }

    /**
     * Get orders with relations for export
     */
    protected function getOrdersForExport(int $tenantId, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = Order::where('tenant_id', $tenantId)
            ->with(['table', 'items.dish', 'items.variant']);

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Export orders as PDF for a given date range.
     *
     * @param Tenant $tenant
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return HttpResponse
     */
    public function exportOrdersPDF(Tenant $tenant, Carbon $startDate, Carbon $endDate): HttpResponse
    {
        $orders = Order::where('tenant_id', $tenant->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['table', 'items.dish'])
            ->orderBy('created_at', 'desc')
            ->get();

        $total = $orders->sum('total');
        $count = $orders->count();

        $pdf = Pdf::loadView('exports.pdf.orders', [
            'tenant' => $tenant,
            'orders' => $orders,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'total' => $total,
            'count' => $count,
        ]);

        $filename = "commandes_{$tenant->slug}_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Export statistics as PDF for a given period.
     *
     * @param Tenant $tenant
     * @param string $period (day, week, month, year)
     * @return HttpResponse
     */
    public function exportStatisticsPDF(Tenant $tenant, string $period = 'month'): HttpResponse
    {
        $startDate = match ($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $endDate = now();

        // Get statistics
        $stats = $this->getDetailedStatistics($tenant, $startDate, $endDate);

        $pdf = Pdf::loadView('exports.pdf.statistics', [
            'tenant' => $tenant,
            'stats' => $stats,
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);

        $filename = "statistiques_{$tenant->slug}_{$period}_{$endDate->format('Y-m-d')}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Export menu as printable PDF.
     *
     * @param Menu $menu
     * @return HttpResponse
     */
    public function exportMenuPDF(Menu $menu): HttpResponse
    {
        $menu->load(['categories.dishes.variants', 'categories.dishes.options']);

        $pdf = Pdf::loadView('exports.pdf.menu', [
            'menu' => $menu,
            'tenant' => $menu->tenant,
        ]);

        $pdf->setPaper('a4', 'portrait');

        $filename = "menu_{$menu->tenant->slug}_{$menu->id}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Get comprehensive statistics for a tenant.
     *
     * @param Tenant $tenant
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    protected function getDetailedStatistics(Tenant $tenant, Carbon $startDate, Carbon $endDate): array
    {
        // Total revenue
        $revenue = Order::where('tenant_id', $tenant->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotIn('status', ['ANNULE'])
            ->sum('total');

        // Order count
        $orderCount = Order::where('tenant_id', $tenant->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Average order value
        $averageOrderValue = $orderCount > 0 ? $revenue / $orderCount : 0;

        // Orders by status
        $ordersByStatus = Order::where('tenant_id', $tenant->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Top dishes
        $topDishes = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('dishes', 'order_items.dish_id', '=', 'dishes.id')
            ->where('orders.tenant_id', $tenant->id)
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select(
                'dishes.name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.unit_price * order_items.quantity) as total_revenue')
            )
            ->groupBy('dishes.id', 'dishes.name')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();

        // Hourly distribution
        $hourExpression = DB::getDriverName() === 'sqlite'
            ? "CAST(strftime('%H', created_at) AS INTEGER)"
            : "EXTRACT(HOUR FROM created_at)";

        $hourlyDistribution = Order::where('tenant_id', $tenant->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw("$hourExpression as hour"), DB::raw('count(*) as count'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        // Daily revenue
        $dailyRevenue = Order::where('tenant_id', $tenant->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotIn('status', ['ANNULE'])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'revenue' => $revenue,
            'order_count' => $orderCount,
            'average_order_value' => $averageOrderValue,
            'orders_by_status' => $ordersByStatus,
            'top_dishes' => $topDishes,
            'hourly_distribution' => $hourlyDistribution,
            'daily_revenue' => $dailyRevenue,
        ];
    }

    /**
     * Export orders as Excel for a given date range.
     *
     * @param Tenant $tenant
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportOrdersExcel(Tenant $tenant, Carbon $startDate, Carbon $endDate)
    {
        $filename = "commandes_{$tenant->slug}_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}.xlsx";

        return (new \App\Exports\OrdersExport($tenant->id, $startDate, $endDate))->download($filename);
    }

    /**
     * Export statistics as Excel for a given period.
     *
     * @param Tenant $tenant
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportStatisticsExcel(Tenant $tenant, Carbon $startDate, Carbon $endDate)
    {
        $filename = "statistiques_{$tenant->slug}_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}.xlsx";

        return (new \App\Exports\StatisticsExport($tenant, $startDate, $endDate))->download($filename);
    }
}
