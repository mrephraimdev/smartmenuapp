<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de commande</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .logo {
            max-width: 120px;
            height: auto;
            margin-bottom: 10px;
        }
        .order-number {
            background: linear-gradient(135deg, #4F46E5, #7C3AED);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            display: inline-block;
            font-size: 1.2em;
            font-weight: bold;
        }
        .order-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .item:last-child {
            border-bottom: none;
        }
        .item-name {
            font-weight: 500;
        }
        .item-details {
            font-size: 0.9em;
            color: #666;
        }
        .total {
            font-size: 1.3em;
            font-weight: bold;
            color: #4F46E5;
            text-align: right;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #4F46E5;
        }
        .status-badge {
            background: #10B981;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            display: inline-block;
        }
        .info-box {
            background: #EEF2FF;
            border-left: 4px solid #4F46E5;
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            color: #666;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if($tenant->logo_url)
                <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}" class="logo">
            @endif
            <h1 style="color: #4F46E5; margin: 10px 0;">{{ $tenant->name }}</h1>
            <p style="color: #666;">Confirmation de commande</p>
        </div>

        <div style="text-align: center; margin: 20px 0;">
            <span class="order-number">Commande #{{ $order->order_number }}</span>
        </div>

        <p>Bonjour,</p>
        <p>Merci pour votre commande ! Nous l'avons bien reçue et nous la préparons avec soin.</p>

        <div class="info-box">
            <strong>Table :</strong> {{ $table->label ?? $table->code }}<br>
            <strong>Statut :</strong> <span class="status-badge">{{ $order->getStatusLabel() }}</span><br>
            <strong>Date :</strong> {{ $order->created_at->format('d/m/Y à H:i') }}
        </div>

        <div class="order-details">
            <h3 style="margin-top: 0; color: #4F46E5;">Récapitulatif de votre commande</h3>

            @foreach($items as $item)
            <div class="item">
                <div>
                    <div class="item-name">{{ $item->dish->name }} x{{ $item->quantity }}</div>
                    @if($item->variant)
                        <div class="item-details">Variante: {{ $item->variant->name }}</div>
                    @endif
                    @if($item->notes)
                        <div class="item-details">Note: {{ $item->notes }}</div>
                    @endif
                </div>
                <div style="font-weight: 500;">
                    {{ number_format($item->unit_price * $item->quantity, 0, ',', ' ') }} {{ $tenant->currency ?? 'FCFA' }}
                </div>
            </div>
            @endforeach

            <div class="total">
                Total : {{ number_format($order->total, 0, ',', ' ') }} {{ $tenant->currency ?? 'FCFA' }}
            </div>
        </div>

        <p>Nous vous notifierons dès que votre commande sera prête.</p>

        <div class="footer">
            <p>{{ $tenant->name }}</p>
            @if($tenant->address)
                <p>{{ $tenant->address }}</p>
            @endif
            @if($tenant->phone)
                <p>Tél: {{ $tenant->phone }}</p>
            @endif
            <p style="margin-top: 20px; font-size: 0.8em;">
                Ce message a été envoyé automatiquement. Merci de ne pas y répondre directement.
            </p>
        </div>
    </div>
</body>
</html>
