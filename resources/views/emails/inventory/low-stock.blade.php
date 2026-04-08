<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerte Stock Bas</title>
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
            border-bottom: 2px solid #EF4444;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .alert-icon {
            font-size: 3em;
            margin-bottom: 10px;
        }
        .alert-badge {
            background: #FEE2E2;
            color: #991B1B;
            padding: 10px 20px;
            border-radius: 8px;
            display: inline-block;
            font-weight: bold;
        }
        .dishes-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .dishes-table th {
            background: #FEE2E2;
            color: #991B1B;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #EF4444;
        }
        .dishes-table td {
            padding: 12px;
            border-bottom: 1px solid #E5E7EB;
        }
        .stock-critical {
            background: #FEE2E2;
            color: #991B1B;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
        }
        .stock-low {
            background: #FEF3C7;
            color: #92400E;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
        }
        .action-box {
            background: #FEF3C7;
            border-left: 4px solid #F59E0B;
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
            <div class="alert-icon">⚠️</div>
            <h1 style="color: #EF4444; margin: 10px 0;">Alerte Stock Bas</h1>
            <p style="color: #666;">{{ $tenant->name }}</p>
        </div>

        <p>Bonjour,</p>
        <p>Certains plats de votre restaurant ont un stock bas ou sont en rupture. Voici le détail :</p>

        <table class="dishes-table">
            <thead>
                <tr>
                    <th>Plat</th>
                    <th>Catégorie</th>
                    <th>Stock</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dishes as $dish)
                <tr>
                    <td>
                        <strong>{{ $dish->name }}</strong>
                        @if(!$dish->active)
                            <span style="color: #999; font-size: 0.8em;">(Désactivé)</span>
                        @endif
                    </td>
                    <td>{{ $dish->category->name ?? 'N/A' }}</td>
                    <td>
                        @if($dish->stock_quantity <= 0)
                            <span class="stock-critical">Rupture</span>
                        @elseif($dish->stock_quantity <= 5)
                            <span class="stock-critical">{{ $dish->stock_quantity }}</span>
                        @else
                            <span class="stock-low">{{ $dish->stock_quantity }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="action-box">
            <strong>Action recommandée :</strong>
            <p style="margin: 10px 0 0;">
                Réapprovisionnez ces articles dès que possible pour éviter toute interruption de service.
            </p>
        </div>

        <p>Accédez à votre tableau de bord pour gérer vos stocks :</p>
        <p style="text-align: center;">
            <a href="{{ url('/admin/' . $tenant->slug . '/dashboard') }}"
               style="display: inline-block; background: #4F46E5; color: white; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: bold;">
                Accéder au Dashboard
            </a>
        </p>

        <div class="footer">
            <p>{{ $tenant->name }} - Système de gestion</p>
            <p style="margin-top: 20px; font-size: 0.8em;">
                Ce message a été envoyé automatiquement par le système de gestion des stocks.
            </p>
        </div>
    </div>
</body>
</html>
