<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques - {{ $tenant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="container mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">📊 Statistiques - {{ $tenant->name }}</h1>
                    <div class="flex space-x-4">
                        <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            <i class="fas fa-arrow-left mr-2"></i>Retour Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="container mx-auto px-4 py-8">
            <!-- Statistiques générales -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <i class="fas fa-shopping-cart text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Commandes</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_orders']) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-euro-sign text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Revenus Totaux</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_revenue'], 0, ',', ' ') }} FCFA</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                            <i class="fas fa-calculator text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Panier Moyen</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['avg_order_value'], 0, ',', ' ') }} FCFA</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                            <i class="fas fa-clock text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Commandes Aujourd'hui</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['today_orders'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Graphiques -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Pics horaires -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6">🏆 Pics Horaires (30 derniers jours)</h2>
                    <canvas id="hourlyChart" width="400" height="300"></canvas>
                </div>

                <!-- Top plats -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6">🍕 Top 10 Plats</h2>
                    <div class="space-y-4">
                        @foreach($topDishes as $index => $dish)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center text-sm font-bold mr-3">
                                    {{ $index + 1 }}
                                </span>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $dish['name'] }}</p>
                                    <p class="text-sm text-gray-500">{{ $dish['orders'] }} commandes</p>
                                </div>
                            </div>
                            <span class="font-bold text-green-600">{{ $dish['quantity'] }} vendus</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Taux de conversion et revenus -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Taux de conversion -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6">📈 Taux de Conversion</h2>
                    <div class="text-center">
                        <div class="text-6xl font-bold text-blue-600 mb-2">{{ $conversionRate['rate'] }}%</div>
                        <p class="text-gray-600 mb-4">Taux de conversion estimé</p>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500">Commandes</p>
                                <p class="font-bold">{{ $conversionRate['orders'] }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Visites Estimées</p>
                                <p class="font-bold">{{ $conversionRate['estimated_visits'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revenus par période -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6">💰 Revenus par Période</h2>
                    <div class="space-y-4">
                        @foreach($revenueByPeriod as $period => $data)
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                            <div>
                                <p class="font-medium">{{ $period === '7days' ? '7 jours' : ($period === '30days' ? '30 jours' : '90 jours') }}</p>
                                <p class="text-sm text-gray-600">{{ number_format($data['avg_daily'], 0, ',', ' ') }} FCFA/jour moyen</p>
                            </div>
                            <span class="font-bold text-green-600">{{ number_format($data['revenue'], 0, ',', ' ') }} FCFA</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Graphique des commandes récentes -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">📊 Évolution des Commandes</h2>
                <div class="mb-4">
                    <select id="periodSelect" class="border border-gray-300 rounded px-3 py-2">
                        <option value="7days">7 derniers jours</option>
                        <option value="30days">30 derniers jours</option>
                        <option value="hourly">Aujourd'hui par heure</option>
                    </select>
                </div>
                <canvas id="trendChart" width="800" height="300"></canvas>
            </div>
        </main>
    </div>

    <script>
        // Données pour les graphiques
        const hourlyData = @json($hourlyPeaks);
        const initialTrendData = @json($this->getLast7DaysData($tenant));

        // Graphique des pics horaires
        const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
        new Chart(hourlyCtx, {
            type: 'bar',
            data: {
                labels: hourlyData.map(d => d.label),
                datasets: [{
                    label: 'Commandes',
                    data: hourlyData.map(d => d.count),
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Graphique d'évolution
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        let trendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: initialTrendData.map(d => d.date),
                datasets: [{
                    label: 'Commandes',
                    data: initialTrendData.map(d => d.orders),
                    borderColor: 'rgba(16, 185, 129, 1)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Revenus (FCFA)',
                    data: initialTrendData.map(d => d.revenue),
                    borderColor: 'rgba(245, 101, 101, 1)',
                    backgroundColor: 'rgba(245, 101, 101, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        position: 'left'
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });

        // Changement de période
        document.getElementById('periodSelect').addEventListener('change', function() {
            const period = this.value;

            fetch(`/admin/${tenantSlug}/statistics/chart-data?period=${period}`)
                .then(response => response.json())
                .then(data => {
                    if (period === 'hourly') {
                        trendChart.data.labels = data.map(d => d.hour);
                        trendChart.data.datasets[0].data = data.map(d => d.orders);
                        trendChart.data.datasets[1].data = new Array(data.length).fill(0); // Pas de revenus horaires
                    } else {
                        trendChart.data.labels = data.map(d => d.date);
                        trendChart.data.datasets[0].data = data.map(d => d.orders);
                        trendChart.data.datasets[1].data = data.map(d => d.revenue);
                    }
                    trendChart.update();
                });
        });
    </script>
</body>
</html>
