<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StatisticsExport implements WithMultipleSheets
{
    use Exportable;

    protected Tenant $tenant;
    protected Carbon $startDate;
    protected Carbon $endDate;

    public function __construct(Tenant $tenant, Carbon $startDate, Carbon $endDate)
    {
        $this->tenant = $tenant;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Return an array of sheets.
     */
    public function sheets(): array
    {
        return [
            new StatisticsOverviewSheet($this->tenant, $this->startDate, $this->endDate),
            new TopDishesSheet($this->tenant, $this->startDate, $this->endDate),
            new DailyRevenueSheet($this->tenant, $this->startDate, $this->endDate),
        ];
    }
}

// Statistics Overview Sheet
class StatisticsOverviewSheet implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithTitle, \Maatwebsite\Excel\Concerns\WithStyles
{
    protected Tenant $tenant;
    protected Carbon $startDate;
    protected Carbon $endDate;

    public function __construct(Tenant $tenant, Carbon $startDate, Carbon $endDate)
    {
        $this->tenant = $tenant;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function array(): array
    {
        $revenue = Order::where('tenant_id', $this->tenant->id)
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['ANNULE'])
            ->sum('total');

        $orderCount = Order::where('tenant_id', $this->tenant->id)
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->count();

        $averageOrderValue = $orderCount > 0 ? $revenue / $orderCount : 0;

        $ordersByStatus = Order::where('tenant_id', $this->tenant->id)
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            ['RAPPORT STATISTIQUES'],
            ['Tenant', $this->tenant->name],
            ['Période', $this->startDate->format('d/m/Y') . ' - ' . $this->endDate->format('d/m/Y')],
            [''],
            ['INDICATEURS CLÉS'],
            ['Chiffre d\'affaires (FCFA)', number_format($revenue, 0, ',', ' ')],
            ['Nombre de commandes', $orderCount],
            ['Panier moyen (FCFA)', number_format($averageOrderValue, 0, ',', ' ')],
            [''],
            ['COMMANDES PAR STATUT'],
            ['Statut', 'Nombre'],
            ...collect($ordersByStatus)->map(fn($count, $status) => [$status, $count])->values()->toArray(),
        ];
    }

    public function title(): string
    {
        return 'Vue d\'ensemble';
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '2563eb']],
        ]);

        $sheet->getStyle('A5')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
        ]);

        $sheet->getStyle('A10')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
        ]);

        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(25);

        return $sheet;
    }
}

// Top Dishes Sheet
class TopDishesSheet implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithTitle, \Maatwebsite\Excel\Concerns\WithStyles
{
    protected Tenant $tenant;
    protected Carbon $startDate;
    protected Carbon $endDate;

    public function __construct(Tenant $tenant, Carbon $startDate, Carbon $endDate)
    {
        $this->tenant = $tenant;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('dishes', 'order_items.dish_id', '=', 'dishes.id')
            ->where('orders.tenant_id', $this->tenant->id)
            ->whereBetween('orders.created_at', [$this->startDate, $this->endDate])
            ->select(
                'dishes.name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.unit_price * order_items.quantity) as total_revenue')
            )
            ->groupBy('dishes.id', 'dishes.name')
            ->orderBy('total_quantity', 'desc')
            ->limit(20)
            ->get();
    }

    public function headings(): array
    {
        return [
            'Plat',
            'Quantité Vendue',
            'Chiffre d\'Affaires (FCFA)',
        ];
    }

    public function title(): string
    {
        return 'Top Plats';
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        $sheet->getStyle('A1:C1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '10b981'],
            ],
        ]);

        foreach (range('A', 'C') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return $sheet;
    }
}

// Daily Revenue Sheet
class DailyRevenueSheet implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithTitle, \Maatwebsite\Excel\Concerns\WithStyles
{
    protected Tenant $tenant;
    protected Carbon $startDate;
    protected Carbon $endDate;

    public function __construct(Tenant $tenant, Carbon $startDate, Carbon $endDate)
    {
        $this->tenant = $tenant;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return Order::where('tenant_id', $this->tenant->id)
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['ANNULE'])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($day) {
                return [
                    'date' => Carbon::parse($day->date)->format('d/m/Y'),
                    'orders' => $day->orders,
                    'revenue' => $day->revenue,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Date',
            'Nombre de Commandes',
            'Chiffre d\'Affaires (FCFA)',
        ];
    }

    public function title(): string
    {
        return 'CA Quotidien';
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        $sheet->getStyle('A1:C1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'f59e0b'],
            ],
        ]);

        foreach (range('A', 'C') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Add a sum formula at the bottom
        $lastRow = $sheet->getHighestRow() + 1;
        $sheet->setCellValue("A{$lastRow}", 'TOTAL');
        $sheet->setCellValue("B{$lastRow}", "=SUM(B2:B" . ($lastRow - 1) . ")");
        $sheet->setCellValue("C{$lastRow}", "=SUM(C2:C" . ($lastRow - 1) . ")");

        $sheet->getStyle("A{$lastRow}:C{$lastRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'fef3c7'],
            ],
        ]);

        return $sheet;
    }
}
