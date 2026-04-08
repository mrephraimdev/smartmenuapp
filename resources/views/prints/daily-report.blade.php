<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport journalier - {{ $date }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            max-width: 210mm;
            margin: 0 auto;
            padding: 20mm;
            background: white;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header .subtitle {
            font-size: 14px;
            color: #666;
        }
        .section {
            margin-bottom: 25px;
        }
        .section h2 {
            font-size: 16px;
            background: #f5f5f5;
            padding: 8px 12px;
            margin-bottom: 15px;
            border-left: 4px solid #333;
        }
        .section h2.green {
            border-left-color: #22c55e;
            background: #f0fdf4;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        .stat-card {
            border: 1px solid #ddd;
            padding: 15px;
            text-align: center;
            border-radius: 5px;
        }
        .stat-card.green {
            border-color: #22c55e;
            background: #f0fdf4;
        }
        .stat-card.red {
            border-color: #ef4444;
            background: #fef2f2;
        }
        .stat-card .value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .stat-card.green .value {
            color: #16a34a;
        }
        .stat-card.red .value {
            color: #dc2626;
        }
        .stat-card .label {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #f5f5f5;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background: #fafafa;
        }
        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .text-right {
            text-align: right;
        }
        .font-bold {
            font-weight: bold;
        }
        .total-row {
            background: #f5f5f5 !important;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .payment-status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
        }
        .payment-status.paid {
            background: #dcfce7;
            color: #166534;
        }
        .payment-status.unpaid {
            background: #fee2e2;
            color: #991b1b;
        }
        @media print {
            body {
                padding: 10mm;
            }
            @page {
                size: A4;
                margin: 10mm;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h1>{{ $tenant->name }}</h1>
        <div class="subtitle">Rapport journalier du {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</div>
    </div>

    <!-- Statistiques principales -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="value">{{ $stats['total_orders'] }}</div>
            <div class="label">Commandes</div>
        </div>
        <div class="stat-card green">
            <div class="value">{{ number_format($stats['total_payments'], 0, ',', ' ') }}</div>
            <div class="label">Encaissé (FCFA)</div>
        </div>
        <div class="stat-card">
            <div class="value">{{ $stats['served_orders'] ?? 0 }}</div>
            <div class="label">Commandes servies</div>
        </div>
        <div class="stat-card red">
            <div class="value">{{ $stats['cancelled_orders'] ?? 0 }}</div>
            <div class="label">Annulations</div>
        </div>
    </div>

    <div class="two-columns">
        <!-- Section Paiements -->
        <div class="section">
            <h2 class="green">Paiements par mode</h2>
            <table>
                <tr>
                    <th>Mode de paiement</th>
                    <th class="text-right">Nb</th>
                    <th class="text-right">Montant (FCFA)</th>
                </tr>
                @foreach($stats['payments_by_method'] as $method => $data)
                    @if($data['count'] > 0)
                    <tr>
                        <td>{{ $data['label'] }}</td>
                        <td class="text-right">{{ $data['count'] }}</td>
                        <td class="text-right">{{ number_format($data['total'], 0, ',', ' ') }}</td>
                    </tr>
                    @endif
                @endforeach
                <tr class="total-row">
                    <td>TOTAL</td>
                    <td class="text-right">{{ $stats['payment_count'] }}</td>
                    <td class="text-right">{{ number_format($stats['total_payments'], 0, ',', ' ') }}</td>
                </tr>
            </table>

            @if($stats['unpaid_orders'] > 0)
            <div style="margin-top: 10px; padding: 10px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 5px;">
                <strong style="color: #991b1b;">Impayés:</strong>
                <span>{{ $stats['unpaid_orders'] }} commande(s) - {{ number_format($stats['unpaid_amount'], 0, ',', ' ') }} FCFA</span>
            </div>
            @endif
        </div>

        <!-- Plats populaires -->
        <div class="section">
            <h2>Plats populaires</h2>
            <table>
                <tr>
                    <th>Plat</th>
                    <th class="text-right">Quantité</th>
                </tr>
                @foreach($stats['popular_dishes'] as $dish => $qty)
                    <tr>
                        <td>{{ $dish }}</td>
                        <td class="text-right">{{ $qty }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>

    <!-- Détail des commandes -->
    <div class="section">
        <h2>Détail des commandes</h2>
        <table>
            <tr>
                <th>N°</th>
                <th>Heure</th>
                <th>Table</th>
                <th class="text-right">Articles</th>
                <th class="text-right">Total</th>
                <th>Paiement</th>
            </tr>
            @foreach($orders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->created_at->format('H:i') }}</td>
                    <td>{{ $order->table->code ?? 'N/A' }}</td>
                    <td class="text-right">{{ $order->items->sum('quantity') }}</td>
                    <td class="text-right">{{ number_format($order->total, 0, ',', ' ') }}</td>
                    <td>
                        @if($order->status === 'ANNULE')
                            <span class="payment-status" style="background: #f3f4f6; color: #6b7280;">Annulé</span>
                        @elseif($order->payment_status === 'PAID')
                            <span class="payment-status paid">Payé</span>
                        @elseif($order->payment_status === 'PARTIAL')
                            <span class="payment-status" style="background: #fef3c7; color: #92400e;">Partiel</span>
                        @else
                            <span class="payment-status unpaid">Non payé</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="footer">
        <p>Rapport généré le {{ $printedAt->format('d/m/Y à H:i:s') }}</p>
        <p>{{ $tenant->name }} - Tous droits réservés</p>
    </div>
</body>
</html>
