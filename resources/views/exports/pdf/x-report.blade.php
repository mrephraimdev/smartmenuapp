<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport X - {{ $session->session_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1f2937;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px double #000;
        }
        .logo {
            max-width: 100px;
            margin-bottom: 10px;
        }
        h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .report-type {
            font-size: 18px;
            font-weight: 700;
            background: #3b82f6;
            color: #fff;
            padding: 8px;
            margin: 15px 0;
            text-align: center;
        }
        .session-info {
            background: #dbeafe;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #93c5fd;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: 600;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 14px;
            font-weight: 700;
            background: #e5e7eb;
            padding: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #3b82f6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            padding: 6px 8px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background: #f9fafb;
            font-weight: 600;
            font-size: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            font-weight: 700;
            background: #f3f4f6;
            border-top: 2px solid #3b82f6;
        }
        .info-box {
            background: #dbeafe;
            border: 2px solid #3b82f6;
            padding: 12px;
            margin: 15px 0;
            text-align: center;
        }
        .info-box-title {
            font-size: 10px;
            color: #1e40af;
            text-transform: uppercase;
            font-weight: 600;
        }
        .info-box-value {
            font-size: 20px;
            font-weight: 700;
            color: #1e3a8a;
            margin-top: 5px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #3b82f6;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
        }
        .kpi-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
        }
        .kpi-card {
            background: #f9fafb;
            padding: 12px;
            border-left: 3px solid #3b82f6;
        }
        .kpi-label {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
        }
        .kpi-value {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin-top: 5px;
        }
        .notice {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 12px;
            margin: 15px 0;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        @if($tenant->logo)
            <img src="{{ public_path('storage/' . $tenant->logo) }}" alt="{{ $tenant->name }}" class="logo">
        @endif
        <h1>{{ $tenant->name }}</h1>
        @if($tenant->address)
            <div>{{ $tenant->address }}</div>
        @endif
        @if($tenant->phone)
            <div>Tél : {{ $tenant->phone }}</div>
        @endif
    </div>

    <div class="report-type">RAPPORT X - SITUATION INTERMÉDIAIRE</div>

    <div class="notice">
        ℹ Ce rapport est un état intermédiaire de la session en cours. Il n'entraîne pas la fermeture de la caisse.
        Les chiffres présentés sont cumulés depuis l'ouverture de la session.
    </div>

    <!-- Session Info -->
    <div class="session-info">
        <div class="info-row">
            <span class="info-label">N° Session:</span>
            <span>{{ $session->session_number }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Caissier:</span>
            <span>{{ $session->user->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Ouverture:</span>
            <span>{{ $session->opened_at->format('d/m/Y à H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Durée actuelle:</span>
            <span>{{ $summary['duration_minutes'] }} minutes</span>
        </div>
        <div class="info-row">
            <span class="info-label">Rapport généré le:</span>
            <span>{{ now()->format('d/m/Y à H:i') }}</span>
        </div>
    </div>

    <!-- KPIs -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-label">Chiffre d'Affaires Actuel</div>
            <div class="kpi-value">{{ number_format($current_totals['total_sales'], 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Commandes</div>
            <div class="kpi-value">{{ $current_totals['total_orders'] }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Panier Moyen</div>
            <div class="kpi-value">{{ number_format($summary['average_order_value'], 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Articles Vendus</div>
            <div class="kpi-value">{{ $current_totals['total_items'] }}</div>
        </div>
    </div>

    <!-- Payment Methods -->
    <div class="section">
        <div class="section-title">RÉPARTITION PAR MOYEN DE PAIEMENT</div>
        <table>
            <thead>
                <tr>
                    <th>Moyen de Paiement</th>
                    <th class="text-right">Montant (FCFA)</th>
                    <th class="text-right">%</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Espèces</td>
                    <td class="text-right">{{ number_format($current_totals['cash_sales'], 0, ',', ' ') }}</td>
                    <td class="text-right">
                        {{ $current_totals['total_sales'] > 0 ? number_format(($current_totals['cash_sales'] / $current_totals['total_sales']) * 100, 1) : 0 }}%
                    </td>
                </tr>
                <tr>
                    <td>Carte Bancaire</td>
                    <td class="text-right">{{ number_format($current_totals['card_sales'], 0, ',', ' ') }}</td>
                    <td class="text-right">
                        {{ $current_totals['total_sales'] > 0 ? number_format(($current_totals['card_sales'] / $current_totals['total_sales']) * 100, 1) : 0 }}%
                    </td>
                </tr>
                <tr>
                    <td>Mobile Money</td>
                    <td class="text-right">{{ number_format($current_totals['mobile_sales'], 0, ',', ' ') }}</td>
                    <td class="text-right">
                        {{ $current_totals['total_sales'] > 0 ? number_format(($current_totals['mobile_sales'] / $current_totals['total_sales']) * 100, 1) : 0 }}%
                    </td>
                </tr>
                <tr class="total-row">
                    <td>TOTAL</td>
                    <td class="text-right">{{ number_format($current_totals['total_sales'], 0, ',', ' ') }}</td>
                    <td class="text-right">100%</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Expected Cash -->
    <div class="info-box">
        <div class="info-box-title">Espèces Attendues en Caisse</div>
        <div class="info-box-value">{{ number_format($expected_cash, 0, ',', ' ') }} FCFA</div>
        <div style="font-size: 10px; color: #6b7280; margin-top: 8px;">
            Fond de caisse ({{ number_format($session->opening_float, 0, ',', ' ') }})
            + Ventes espèces ({{ number_format($current_totals['cash_sales'], 0, ',', ' ') }})
            - Remboursements ({{ number_format($current_totals['refunds_total'], 0, ',', ' ') }})
        </div>
    </div>

    <!-- Orders by Status -->
    @if(!empty($orders_by_status))
        <div class="section">
            <div class="section-title">COMMANDES PAR STATUT</div>
            <table>
                <thead>
                    <tr>
                        <th>Statut</th>
                        <th class="text-right">Nombre</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders_by_status as $status => $count)
                        <tr>
                            <td>{{ $status }}</td>
                            <td class="text-right">{{ $count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Cancellations -->
    @if($current_totals['cancelled_orders'] > 0)
        <div class="section">
            <div class="section-title">ANNULATIONS ET REMBOURSEMENTS</div>
            <table>
                <tr>
                    <td>Commandes annulées:</td>
                    <td class="text-right">{{ $current_totals['cancelled_orders'] }}</td>
                </tr>
                <tr>
                    <td>Montant total remboursé:</td>
                    <td class="text-right" style="font-weight: 700;">{{ number_format($current_totals['refunds_total'], 0, ',', ' ') }} FCFA</td>
                </tr>
            </table>
        </div>
    @endif

    <!-- Top Dishes -->
    @if($top_dishes && $top_dishes->count() > 0)
        <div class="section">
            <div class="section-title">TOP 10 PLATS VENDUS</div>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Plat</th>
                        <th class="text-center">Qté</th>
                        <th class="text-right">CA (FCFA)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($top_dishes as $index => $dish)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $dish->name }}</td>
                            <td class="text-center">{{ $dish->total_quantity }}</td>
                            <td class="text-right">{{ number_format($dish->total_revenue, 0, ',', ' ') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="footer">
        <div style="font-weight: 600; margin-bottom: 8px;">SESSION EN COURS - NON CLÔTURÉE</div>
        <div>Rapport X généré le {{ now()->format('d/m/Y à H:i:s') }}</div>
        <div style="margin-top: 5px;">{{ $tenant->name }}</div>
        <div style="margin-top: 8px; font-size: 8px;">
            Ce rapport est fourni à titre informatif. Pour une clôture officielle, veuillez générer un Rapport Z.
        </div>
    </div>
</body>
</html>
