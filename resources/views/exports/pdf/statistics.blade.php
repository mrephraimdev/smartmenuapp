<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques - {{ $tenant->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #10b981;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        h1 {
            font-size: 22px;
            color: #047857;
            margin-bottom: 5px;
        }
        .tenant-name {
            font-size: 16px;
            color: #666;
            margin-bottom: 3px;
        }
        .period {
            font-size: 12px;
            color: #888;
        }
        .kpis {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .kpi {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            width: 23%;
            margin-bottom: 10px;
        }
        .kpi-label {
            font-size: 10px;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kpi-value {
            font-size: 20px;
            font-weight: bold;
            margin-top: 8px;
        }
        .section {
            margin-bottom: 30px;
        }
        h2 {
            font-size: 16px;
            color: #047857;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #d1fae5;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        thead {
            background: #10b981;
            color: white;
        }
        th {
            padding: 10px 8px;
            text-align: left;
            font-weight: 600;
            font-size: 10px;
        }
        tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }
        tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        td {
            padding: 8px;
            font-size: 10px;
        }
        .rank {
            background: #10b981;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 11px;
        }
        .rank-1 { background: #f59e0b; }
        .rank-2 { background: #94a3b8; }
        .rank-3 { background: #cd7f32; }
        .chart-bar {
            background: #d1fae5;
            height: 20px;
            border-radius: 3px;
            position: relative;
        }
        .chart-bar-fill {
            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
            height: 100%;
            border-radius: 3px;
            min-width: 2%;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 9px;
            color: #999;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }
        .status-breakdown {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
        }
        .status-item {
            text-align: center;
            padding: 10px;
        }
        .status-count {
            font-size: 18px;
            font-weight: bold;
            color: #047857;
        }
        .status-label {
            font-size: 9px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        @if($tenant->logo)
            <img src="{{ public_path('storage/' . $tenant->logo) }}" alt="{{ $tenant->name }}" class="logo">
        @endif
        <h1>Rapport Statistiques</h1>
        <div class="tenant-name">{{ $tenant->name }}</div>
        <div class="period">
            Période : {{ ucfirst($period) }} • {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}
        </div>
    </div>

    <!-- KPIs -->
    <div class="kpis">
        <div class="kpi">
            <div class="kpi-label">Chiffre d'affaires</div>
            <div class="kpi-value">{{ number_format($stats['revenue'], 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Commandes</div>
            <div class="kpi-value">{{ $stats['order_count'] }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Panier Moyen</div>
            <div class="kpi-value">{{ number_format($stats['average_order_value'], 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Articles Vendus</div>
            <div class="kpi-value">{{ $stats['top_dishes']->sum('total_quantity') }}</div>
        </div>
    </div>

    <!-- Orders by Status -->
    @if(!empty($stats['orders_by_status']))
        <div class="section">
            <h2>Répartition des Commandes par Statut</h2>
            <div class="status-breakdown">
                @foreach($stats['orders_by_status'] as $status => $count)
                    <div class="status-item">
                        <div class="status-count">{{ $count }}</div>
                        <div class="status-label">{{ $status }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Top Dishes -->
    @if($stats['top_dishes']->count() > 0)
        <div class="section">
            <h2>Top 10 Plats les Plus Vendus</h2>
            <table>
                <thead>
                    <tr>
                        <th style="width: 10%">#</th>
                        <th style="width: 45%">Plat</th>
                        <th style="width: 15%; text-align: center">Quantité</th>
                        <th style="width: 30%; text-align: right">Chiffre d'Affaires</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats['top_dishes'] as $index => $dish)
                        <tr>
                            <td>
                                <span class="rank rank-{{ min($index + 1, 3) }}">{{ $index + 1 }}</span>
                            </td>
                            <td>{{ $dish->name }}</td>
                            <td style="text-align: center; font-weight: bold">{{ $dish->total_quantity }}</td>
                            <td style="text-align: right; font-weight: bold; color: #047857">
                                {{ number_format($dish->total_revenue, 0, ',', ' ') }} FCFA
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Hourly Distribution -->
    @if(!empty($stats['hourly_distribution']))
        <div class="section">
            <h2>Distribution Horaire des Commandes</h2>
            @php
                $maxCount = max($stats['hourly_distribution']);
            @endphp
            @foreach($stats['hourly_distribution'] as $hour => $count)
                <div style="margin-bottom: 10px;">
                    <div style="display: flex; align-items: center; margin-bottom: 3px;">
                        <div style="width: 60px; font-weight: bold; color: #666;">{{ sprintf('%02d', $hour) }}h</div>
                        <div style="flex: 1;">
                            <div class="chart-bar">
                                <div class="chart-bar-fill" style="width: {{ ($count / $maxCount) * 100 }}%"></div>
                            </div>
                        </div>
                        <div style="width: 50px; text-align: right; font-weight: bold; color: #047857; margin-left: 10px;">
                            {{ $count }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Daily Revenue -->
    @if($stats['daily_revenue']->count() > 0)
        <div class="section">
            <h2>Évolution Quotidienne du Chiffre d'Affaires</h2>
            <table>
                <thead>
                    <tr>
                        <th style="width: 30%">Date</th>
                        <th style="width: 25%; text-align: center">Commandes</th>
                        <th style="width: 45%; text-align: right">Chiffre d'Affaires</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats['daily_revenue'] as $day)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($day->date)->format('d/m/Y') }}</td>
                            <td style="text-align: center">{{ $day->orders }}</td>
                            <td style="text-align: right; font-weight: bold; color: #047857">
                                {{ number_format($day->revenue, 0, ',', ' ') }} FCFA
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="footer">
        Rapport généré le {{ now()->format('d/m/Y à H:i') }} • {{ $tenant->name }}
        <br>
        Ce document est confidentiel et destiné exclusivement à un usage interne
    </div>
</body>
</html>
