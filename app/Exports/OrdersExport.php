<?php

namespace App\Exports;

use App\Models\Order;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    use Exportable;

    protected int $tenantId;
    protected Carbon $startDate;
    protected Carbon $endDate;

    public function __construct(int $tenantId, Carbon $startDate, Carbon $endDate)
    {
        $this->tenantId = $tenantId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Get the collection of orders to export.
     */
    public function collection()
    {
        return Order::where('tenant_id', $this->tenantId)
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->with(['table', 'items.dish'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Define the headings for the Excel file.
     */
    public function headings(): array
    {
        return [
            'N° Commande',
            'Date',
            'Heure',
            'Table',
            'Statut',
            'Nombre d\'articles',
            'Total (FCFA)',
            'Notes',
        ];
    }

    /**
     * Map the data for each row.
     */
    public function map($order): array
    {
        return [
            $order->order_number,
            $order->created_at->format('d/m/Y'),
            $order->created_at->format('H:i'),
            $order->table->label ?? $order->table->code ?? 'N/A',
            $order->getStatusLabel(),
            $order->items->sum('quantity'),
            $order->total,
            $order->notes ?? '',
        ];
    }

    /**
     * Apply styles to the worksheet.
     */
    public function styles(Worksheet $sheet)
    {
        // Style header row
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2563eb'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Auto-size columns
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Set row height for header
        $sheet->getRowDimension(1)->setRowHeight(25);

        return $sheet;
    }

    /**
     * Set the title of the worksheet.
     */
    public function title(): string
    {
        return 'Commandes';
    }
}
