@extends('layouts.admin')

@section('title', 'Statistiques')
@section('page-title', 'Statistiques')
@section('breadcrumb')
    <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="hover:text-indigo-600">Dashboard</a>
    <span class="mx-2">/</span>
    <span>Statistiques</span>
@endsection

@section('content')
<div x-data="statisticsStore()">

    {{-- ── Filtre par période ──────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-3 mb-6 flex flex-wrap items-center gap-3">
        @foreach(['today' => "Aujourd'hui", 'yesterday' => 'Hier', '7days' => '7 jours', '30days' => '30 jours', 'month' => 'Ce mois'] as $p => $label)
        <a href="{{ route('admin.statistics', array_merge([$tenant->slug], ['period' => $p])) }}"
           class="px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors
                  {{ $period === $p ? 'bg-amber-500 text-white border-amber-500' : 'bg-gray-50 text-gray-600 border-gray-200 hover:border-amber-300' }}">
            {{ $label }}
        </a>
        @endforeach

        <div class="h-5 w-px bg-gray-200 hidden sm:block"></div>

        <form method="GET" action="{{ route('admin.statistics', $tenant->slug) }}" class="flex items-center gap-2">
            <input type="hidden" name="period" value="custom">
            <input type="date" name="date_from" value="{{ $period === 'custom' ? $dateFrom : '' }}"
                   class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-amber-400">
            <span class="text-xs text-gray-400">→</span>
            <input type="date" name="date_to" value="{{ $period === 'custom' ? $dateTo : '' }}"
                   class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-amber-400">
            <button type="submit" class="px-3 py-1.5 text-xs font-semibold bg-slate-800 text-white rounded-lg hover:bg-slate-700 transition-colors">OK</button>
        </form>

        <span class="ml-auto text-xs font-semibold text-amber-700 bg-amber-50 px-3 py-1.5 rounded-lg border border-amber-200">
            📅 {{ $periodLabel }}
        </span>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <x-ui.stat-card
            title="Total Commandes"
            :value="number_format($stats['total_orders'])"
            icon="shopping-cart"
            color="blue"
        />
        <x-ui.stat-card
            title="Revenus Totaux"
            :value="number_format($stats['total_revenue'], 0, ',', ' ') . ' FCFA'"
            icon="banknotes"
            color="green"
        />
        <x-ui.stat-card
            title="Panier Moyen"
            :value="number_format($stats['avg_order_value'], 0, ',', ' ') . ' FCFA'"
            icon="calculator"
            color="purple"
        />
        <x-ui.stat-card
            title="Commandes Aujourd'hui"
            :value="$stats['today_orders']"
            icon="clock"
            color="orange"
        />
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Hourly Peaks -->
        <x-ui.card>
            <x-slot name="header">
                <div class="flex items-center space-x-3">
                    <x-heroicon-o-trophy class="w-6 h-6 text-yellow-500" />
                    <h2 class="text-lg font-semibold text-gray-800">Pics Horaires (30 jours)</h2>
                </div>
            </x-slot>
            <canvas id="hourlyChart" height="250"></canvas>
        </x-ui.card>

        <!-- Top Dishes -->
        <x-ui.card>
            <x-slot name="header">
                <div class="flex items-center space-x-3">
                    <x-heroicon-o-fire class="w-6 h-6 text-orange-500" />
                    <h2 class="text-lg font-semibold text-gray-800">Top 10 Plats</h2>
                </div>
            </x-slot>
            <div class="space-y-3 max-h-80 overflow-y-auto">
                @forelse($topDishes as $index => $dish)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex items-center space-x-3">
                            <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center text-sm font-bold">
                                {{ $index + 1 }}
                            </span>
                            <div>
                                <p class="font-medium text-gray-800">{{ $dish['name'] }}</p>
                                <p class="text-xs text-gray-500">{{ $dish['orders'] }} commandes</p>
                            </div>
                        </div>
                        <x-ui.badge variant="success">{{ $dish['quantity'] }} vendus</x-ui.badge>
                    </div>
                @empty
                    <x-ui.empty-state
                        icon="cake"
                        title="Aucune donnée"
                        description="Les statistiques apparaîtront après vos premières commandes."
                    />
                @endforelse
            </div>
        </x-ui.card>
    </div>

    <!-- Conversion & Revenue Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Conversion Rate -->
        <x-ui.card>
            <x-slot name="header">
                <div class="flex items-center space-x-3">
                    <x-heroicon-o-arrow-trending-up class="w-6 h-6 text-blue-500" />
                    <h2 class="text-lg font-semibold text-gray-800">Taux de Conversion</h2>
                </div>
            </x-slot>
            <div class="text-center py-6">
                <div class="text-6xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600 mb-2">
                    {{ $conversionRate['rate'] }}%
                </div>
                <p class="text-gray-500 mb-6">Taux de conversion estimé</p>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-500 mb-1">Commandes</p>
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($conversionRate['orders']) }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-500 mb-1">Visites Estimées</p>
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($conversionRate['estimated_visits']) }}</p>
                    </div>
                </div>
            </div>
        </x-ui.card>

        <!-- Revenue by Period -->
        <x-ui.card>
            <x-slot name="header">
                <div class="flex items-center space-x-3">
                    <x-heroicon-o-currency-dollar class="w-6 h-6 text-green-500" />
                    <h2 class="text-lg font-semibold text-gray-800">Revenus par Période</h2>
                </div>
            </x-slot>
            <div class="space-y-4">
                @foreach($revenueByPeriod as $period => $data)
                    <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div>
                            <p class="font-medium text-gray-800">
                                {{ $period === '7days' ? '7 derniers jours' : ($period === '30days' ? '30 derniers jours' : '90 derniers jours') }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ number_format($data['avg_daily'], 0, ',', ' ') }} FCFA/jour moyen
                            </p>
                        </div>
                        <span class="text-xl font-bold text-green-600">
                            {{ number_format($data['revenue'], 0, ',', ' ') }} FCFA
                        </span>
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    </div>

    <!-- Trend Chart -->
    <x-ui.card>
        <x-slot name="header">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <x-heroicon-o-chart-bar class="w-6 h-6 text-indigo-500" />
                    <h2 class="text-lg font-semibold text-gray-800">Évolution des Commandes</h2>
                </div>
                <select x-model="selectedPeriod" @change="updateChart()"
                        class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="7days">7 derniers jours</option>
                    <option value="30days">30 derniers jours</option>
                    <option value="hourly">Aujourd'hui par heure</option>
                </select>
            </div>
        </x-slot>
        <canvas id="trendChart" height="100"></canvas>
    </x-ui.card>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('statisticsStore', () => ({
        selectedPeriod: '7days',
        trendChart: null,

        init() {
            this.initHourlyChart();
            this.initTrendChart();
        },

        initHourlyChart() {
            const hourlyData = @json($hourlyPeaks);
            const ctx = document.getElementById('hourlyChart').getContext('2d');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: hourlyData.map(d => d.label),
                    datasets: [{
                        label: 'Commandes',
                        data: hourlyData.map(d => d.count),
                        backgroundColor: 'rgba(99, 102, 241, 0.5)',
                        borderColor: 'rgba(99, 102, 241, 1)',
                        borderWidth: 2,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        },

        initTrendChart() {
            const initialData = @json($trendData ?? []);
            const ctx = document.getElementById('trendChart').getContext('2d');

            this.trendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: initialData.map(d => d.date),
                    datasets: [{
                        label: 'Commandes',
                        data: initialData.map(d => d.orders),
                        borderColor: 'rgba(16, 185, 129, 1)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.4
                    }, {
                        label: 'Revenus (FCFA)',
                        data: initialData.map(d => d.revenue),
                        borderColor: 'rgba(239, 68, 68, 1)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            position: 'left'
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            grid: { drawOnChartArea: false }
                        }
                    }
                }
            });
        },

        async updateChart() {
            try {
                const response = await fetch(`/admin/{{ $tenant->slug }}/statistics/chart-data?period=${this.selectedPeriod}`);
                const data = await response.json();

                if (this.selectedPeriod === 'hourly') {
                    this.trendChart.data.labels = data.map(d => d.hour);
                    this.trendChart.data.datasets[0].data = data.map(d => d.orders);
                    this.trendChart.data.datasets[1].data = new Array(data.length).fill(0);
                } else {
                    this.trendChart.data.labels = data.map(d => d.date);
                    this.trendChart.data.datasets[0].data = data.map(d => d.orders);
                    this.trendChart.data.datasets[1].data = data.map(d => d.revenue);
                }

                this.trendChart.update();
            } catch (error) {
                console.error('Erreur chargement données:', error);
            }
        }
    }));
});
</script>
@endpush
@endsection
