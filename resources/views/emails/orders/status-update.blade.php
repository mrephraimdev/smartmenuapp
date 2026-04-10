<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mise à jour de commande</title>
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
        .status-container {
            text-align: center;
            margin: 30px 0;
        }
        .status-badge {
            padding: 15px 30px;
            border-radius: 30px;
            font-size: 1.3em;
            font-weight: bold;
            display: inline-block;
        }
        .status-received { background: #DBEAFE; color: #1E40AF; }
        .status-preparing { background: #FEF3C7; color: #92400E; }
        .status-ready { background: #D1FAE5; color: #065F46; }
        .status-served { background: #E0E7FF; color: #3730A3; }
        .status-cancelled { background: #FEE2E2; color: #991B1B; }

        .progress-bar {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            position: relative;
        }
        .progress-bar::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 4px;
            background: #E5E7EB;
            z-index: 1;
        }
        .progress-step {
            text-align: center;
            position: relative;
            z-index: 2;
            flex: 1;
        }
        .progress-step .dot {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #E5E7EB;
            margin: 0 auto 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: white;
        }
        .progress-step.active .dot {
            background: #4F46E5;
        }
        .progress-step.completed .dot {
            background: #10B981;
        }
        .progress-step .label {
            font-size: 0.8em;
            color: #666;
        }
        .progress-step.active .label {
            color: #4F46E5;
            font-weight: bold;
        }

        .order-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
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
            <p style="color: #666;">Mise à jour de votre commande</p>
        </div>

        <p>Bonjour,</p>
        <p>Votre commande <strong>#{{ $order->order_number }}</strong> a été mise à jour.</p>

        <div class="status-container">
            @php
                $statusClass = match($order->status) {
                    'RECU' => 'status-received',
                    'PREP' => 'status-preparing',
                    'PRET' => 'status-ready',
                    'SERVI' => 'status-served',
                    'ANNULE' => 'status-cancelled',
                    default => 'status-received'
                };
            @endphp
            <span class="status-badge {{ $statusClass }}">
                {{ $newStatus }}
            </span>
        </div>

        @if($order->status !== 'ANNULE')
        <div class="progress-bar">
            @php
                $steps = ['RECU', 'PREP', 'PRET', 'SERVI'];
                $currentIndex = array_search($order->status, $steps);
            @endphp
            @foreach($steps as $index => $step)
                @php
                    $stepClass = '';
                    if ($index < $currentIndex) $stepClass = 'completed';
                    if ($index == $currentIndex) $stepClass = 'active';
                @endphp
                <div class="progress-step {{ $stepClass }}">
                    <div class="dot">
                        @if($index < $currentIndex)
                            ✓
                        @else
                            {{ $index + 1 }}
                        @endif
                    </div>
                    <div class="label">
                        @switch($step)
                            @case('RECU') Reçue @break
                            @case('PREP') En préparation @break
                            @case('PRET') Prête @break
                            @case('SERVI') Servie @break
                        @endswitch
                    </div>
                </div>
            @endforeach
        </div>
        @endif

        @if($order->status === 'PRET')
        <div style="background: #D1FAE5; border-radius: 8px; padding: 20px; text-align: center; margin: 20px 0;">
            <p style="font-size: 1.2em; color: #065F46; margin: 0;">
                <strong>🎉 Votre commande est prête !</strong>
            </p>
            <p style="color: #065F46; margin: 10px 0 0;">
                Rendez-vous au comptoir ou attendez qu'un serveur vous l'apporte.
            </p>
        </div>
        @endif

        @if($order->status === 'ANNULE')
        <div style="background: #FEE2E2; border-radius: 8px; padding: 20px; text-align: center; margin: 20px 0;">
            <p style="font-size: 1.1em; color: #991B1B; margin: 0;">
                Votre commande a été annulée.
            </p>
            <p style="color: #991B1B; margin: 10px 0 0;">
                Si vous avez des questions, n'hésitez pas à nous contacter.
            </p>
        </div>
        @endif

        <div class="order-info">
            <p><strong>Commande :</strong> #{{ $order->order_number }}</p>
            <p><strong>Table :</strong> {{ $order->table->label ?? $order->table->code }}</p>
            <p><strong>Total :</strong> {{ number_format($order->total, 0, ',', ' ') }} {{ $tenant->currency ?? 'FCFA' }}</p>
        </div>

        <div class="footer">
            <p>{{ $tenant->name }}</p>
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
