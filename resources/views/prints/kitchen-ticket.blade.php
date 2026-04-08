<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ticket Cuisine - {{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            width: 80mm;
            padding: 5mm;
            background: white;
        }
        .header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .header .subtitle {
            font-size: 14px;
            font-weight: bold;
        }
        .order-info {
            margin-bottom: 15px;
        }
        .order-info .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .order-info .table-number {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            padding: 10px;
            border: 2px solid #000;
            margin: 10px 0;
        }
        .items {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 10px 0;
        }
        .item {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dotted #ccc;
        }
        .item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .item-header {
            display: flex;
            align-items: flex-start;
        }
        .item-qty {
            font-size: 18px;
            font-weight: bold;
            min-width: 30px;
            padding: 2px 8px;
            border: 1px solid #000;
            text-align: center;
            margin-right: 10px;
        }
        .item-name {
            font-size: 14px;
            font-weight: bold;
            flex: 1;
        }
        .item-variant {
            margin-left: 40px;
            font-size: 11px;
            color: #333;
        }
        .item-notes {
            margin-left: 40px;
            margin-top: 5px;
            font-size: 11px;
            padding: 3px 5px;
            background: #f0f0f0;
            border-left: 3px solid #000;
        }
        .footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px dashed #000;
        }
        .footer .time {
            font-size: 14px;
            font-weight: bold;
        }
        @media print {
            body {
                width: 80mm;
            }
            @page {
                size: 80mm auto;
                margin: 0;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h1>CUISINE</h1>
        <div class="subtitle">{{ $tenant->name }}</div>
    </div>

    <div class="order-info">
        <div class="row">
            <span>Commande:</span>
            <span><strong>{{ $order->order_number }}</strong></span>
        </div>
        <div class="row">
            <span>Heure:</span>
            <span>{{ $order->created_at->format('H:i') }}</span>
        </div>
        <div class="table-number">
            TABLE {{ $table->code ?? $table->label ?? 'N/A' }}
        </div>
    </div>

    <div class="items">
        @foreach($items as $item)
            <div class="item">
                <div class="item-header">
                    <span class="item-qty">{{ $item->quantity }}</span>
                    <span class="item-name">{{ $item->dish->name ?? 'Plat inconnu' }}</span>
                </div>
                @if($item->variant)
                    <div class="item-variant">
                        &rarr; {{ $item->variant->name }}
                    </div>
                @endif
                @if($item->notes)
                    <div class="item-notes">
                        NOTE: {{ $item->notes }}
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="footer">
        <div class="time">Imprimé à {{ $printedAt->format('H:i:s') }}</div>
    </div>
</body>
</html>
