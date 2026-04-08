<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatisticsResource extends JsonResource
{
    /**
     * The resource wraps an array of statistics data.
     *
     * @var string|null
     */
    public static $wrap = 'statistics';

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'period' => [
                'start' => $this->resource['period']['start'] ?? null,
                'end' => $this->resource['period']['end'] ?? null,
                'type' => $this->resource['period']['type'] ?? 'day',
            ],
            'overview' => [
                'total_orders' => $this->resource['total_orders'] ?? 0,
                'total_revenue' => $this->resource['total_revenue'] ?? 0,
                'total_revenue_formatted' => number_format($this->resource['total_revenue'] ?? 0, 0, ',', ' ') . ' FCFA',
                'average_order_value' => $this->resource['average_order_value'] ?? 0,
                'average_order_value_formatted' => number_format($this->resource['average_order_value'] ?? 0, 0, ',', ' ') . ' FCFA',
                'conversion_rate' => round($this->resource['conversion_rate'] ?? 0, 2),
                'conversion_rate_formatted' => round($this->resource['conversion_rate'] ?? 0, 2) . '%',
            ],
            'orders_by_status' => $this->formatOrdersByStatus(),
            'hourly_peaks' => $this->formatHourlyPeaks(),
            'top_dishes' => $this->formatTopDishes(),
            'daily_trends' => $this->resource['daily_trends'] ?? [],
            'comparisons' => [
                'vs_previous_period' => [
                    'orders_change' => $this->resource['orders_change_percent'] ?? 0,
                    'revenue_change' => $this->resource['revenue_change_percent'] ?? 0,
                ],
            ],
            'generated_at' => now()->toISOString(),
        ];
    }

    /**
     * Format orders by status.
     */
    protected function formatOrdersByStatus(): array
    {
        $ordersByStatus = $this->resource['orders_by_status'] ?? [];

        return collect($ordersByStatus)->map(function ($count, $status) {
            return [
                'status' => $status,
                'count' => $count,
                'label' => $this->getStatusLabel($status),
                'color' => $this->getStatusColor($status),
            ];
        })->values()->toArray();
    }

    /**
     * Format hourly peaks.
     */
    protected function formatHourlyPeaks(): array
    {
        $peaks = $this->resource['hourly_peaks'] ?? [];

        return collect($peaks)->map(function ($data, $hour) {
            return [
                'hour' => (int) $hour,
                'hour_formatted' => sprintf('%02d:00', $hour),
                'orders_count' => $data['count'] ?? 0,
                'revenue' => $data['revenue'] ?? 0,
                'is_peak' => ($data['count'] ?? 0) >= ($this->resource['peak_threshold'] ?? 5),
            ];
        })->sortBy('hour')->values()->toArray();
    }

    /**
     * Format top dishes.
     */
    protected function formatTopDishes(): array
    {
        $topDishes = $this->resource['top_dishes'] ?? [];

        return collect($topDishes)->map(function ($dish, $index) {
            return [
                'rank' => $index + 1,
                'dish_id' => $dish['id'] ?? null,
                'name' => $dish['name'] ?? 'Unknown',
                'orders_count' => $dish['orders_count'] ?? 0,
                'revenue' => $dish['revenue'] ?? 0,
                'revenue_formatted' => number_format($dish['revenue'] ?? 0, 0, ',', ' ') . ' FCFA',
                'percentage_of_total' => round($dish['percentage'] ?? 0, 1),
            ];
        })->toArray();
    }

    /**
     * Get status label.
     */
    protected function getStatusLabel(string $status): string
    {
        return match ($status) {
            'RECU' => 'Reçue',
            'PREP' => 'En préparation',
            'PRET' => 'Prête',
            'SERVI' => 'Servie',
            'ANNULE' => 'Annulée',
            default => $status,
        };
    }

    /**
     * Get status color.
     */
    protected function getStatusColor(string $status): string
    {
        return match ($status) {
            'RECU' => 'blue',
            'PREP' => 'yellow',
            'PRET' => 'green',
            'SERVI' => 'gray',
            'ANNULE' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'version' => '1.0',
                'api' => 'SmartMenu API',
                'cache_ttl' => 300, // 5 minutes
            ],
        ];
    }
}
