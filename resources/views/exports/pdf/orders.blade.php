<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Commandes - {{ $tenant->name }}</title>
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
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #2563eb;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        h1 {
            font-size: 20px;
            color: #1e40af;
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
        .summary {
            background: #f3f4f6;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
        }
        .summary-item {
            text-align: center;
        }
        .summary-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
        }
        .summary-value {
            font-size: 16px;
            font-weight: bold;
            color: #1e40af;
            margin-top: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        thead {
            background: #2563eb;
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
        .status {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 600;
            display: inline-block;
        }
        .status-RECU { background: #dbeafe; color: #1e40af; }
        .status-PREP { background: #fef3c7; color: #92400e; }
        .status-PRET { background: #d1fae5; color: #065f46; }
        .status-SERVI { background: #e0e7ff; color: #3730a3; }
        .status-ANNULE { background: #fee2e2; color: #991b1b; }
        .total {
            text-align: right;
            font-weight: bold;
            color: #1e40af;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #999;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        @if($tenant->logo)
            <img src="{{ public_path('storage/' . $tenant->logo) }}" alt="{{ $tenant->name }}" class="logo">
        @endif
        <h1>Export des Commandes</h1>
        <div class="tenant-name">{{ $tenant->name }}</div>
        <div class="period">
            Période : {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}
        </div>
    </div>

    <div class="summary">
        <div class="summary-item">
            <div class="summary-label">Nombre de commandes</div>
            <div class="summary-value">{{ $count }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Chiffre d'affaires</div>
            <div class="summary-value">{{ number_format($total, 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Panier moyen</div>
            <div class="summary-value">{{ $count > 0 ? number_format($total / $count, 0, ',', ' ') : 0 }} FCFA</div>
        </div>
    </div>

    @if($orders->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 12%">N° Commande</th>
                    <th style="width: 14%">Date/Heure</th>
                    <th style="width: 12%">Table</th>
                    <th style="width: 15%">Statut</th>
                    <th style="width: 10%">Articles</th>
                    <th style="width: 15%">Total</th>
                    <th style="width: 22%">Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $order->table->label ?? $order->table->code ?? 'N/A' }}</td>
                        <td>
                            <span class="status status-{{ $order->status }}">
                                {{ $order->getStatusLabel() }}
                            </span>
                        </td>
                        <td>{{ $order->items->sum('quantity') }}</td>
                        <td class="total">{{ number_format($order->total, 0, ',', ' ') }} FCFA</td>
                        <td>{{ Str::limit($order->notes, 30) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            Aucune commande trouvée pour cette période
        </div>
    @endif

    <div class="footer">
        Document généré le {{ now()->format('d/m/Y à H:i') }} • {{ $tenant->name }}
    </div>
</body>
</html>
