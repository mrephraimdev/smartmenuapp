<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport Z - {{ $session->session_number }}</title>
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
            background: #000;
            color: #fff;
            padding: 8px;
            margin: 15px 0;
            text-align: center;
        }
        .session-info {
            background: #f3f4f6;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #d1d5db;
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
            border-left: 4px solid #000;
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
            border-top: 2px solid #000;
        }
        .grand-total {
            font-size: 16px;
            font-weight: 700;
            background: #000;
            color: #fff;
            padding: 10px;
            text-align: right;
            margin: 15px 0;
        }
        .cash-box {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
        }
        .cash-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 12px;
        }
        .cash-difference {
            font-size: 16px;
            font-weight: 700;
            padding-top: 10px;
            border-top: 2px solid #f59e0b;
        }
        .warning {
            background: #fee2e2;
            color: #991b1b;
            padding: 10px;
            border-left: 4px solid #dc2626;
            margin: 10px 0;
        }
        .success {
            background: #d1fae5;
            color: #065f46;
            padding: 10px;
            border-left: 4px solid #10b981;
            margin: 10px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #000;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
        }
        .signature-box {
            margin-top: 40px;
            padding: 15px;
            border: 1px dashed #9ca3af;
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

    <div class="report-type">RAPPORT Z - CLÔTURE DE CAISSE</div>

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
            <span class="info-label">Fermeture:</span>
            <span>{{ $session->closed_at->format('d/m/Y à H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Durée:</span>
            <span>{{ $summary['duration_minutes'] }} minutes</span>
        </div>
    </div>

    <!-- KPIs -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-label">Chiffre d'Affaires</div>
            <div class="kpi-value">{{ number_format($session->total_sales, 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Commandes</div>
            <div class="kpi-value">{{ $session->total_orders }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Panier Moyen</div>
            <div class="kpi-value">{{ number_format($summary['average_order_value'], 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Articles Vendus</div>
            <div class="kpi-value">{{ $session->total_items }}</div>
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
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Espèces</td>
                    <td class="text-right">{{ number_format($session->cash_sales, 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td>Carte Bancaire</td>
                    <td class="text-right">{{ number_format($session->card_sales, 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td>Mobile Money</td>
                    <td class="text-right">{{ number_format($session->mobile_sales, 0, ',', ' ') }}</td>
                </tr>
                <tr class="total-row">
                    <td>TOTAL</td>
                    <td class="text-right">{{ number_format($session->total_sales, 0, ',', ' ') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Cash Balance -->
    <div class="cash-box">
        <div style="font-size: 14px; font-weight: 700; margin-bottom: 10px;">BILAN DE CAISSE</div>
        <div class="cash-row">
            <span>Fond de caisse ouverture:</span>
            <span style="font-weight: 600;">{{ number_format($session->opening_float, 0, ',', ' ') }} FCFA</span>
        </div>
        <div class="cash-row">
            <span>+ Ventes en espèces:</span>
            <span style="font-weight: 600;">{{ number_format($session->cash_sales, 0, ',', ' ') }} FCFA</span>
        </div>
        <div class="cash-row">
            <span>- Remboursements:</span>
            <span style="font-weight: 600;">{{ number_format($session->refunds_total, 0, ',', ' ') }} FCFA</span>
        </div>
        <div class="cash-row" style="font-size: 14px; font-weight: 700; padding-top: 8px; border-top: 1px solid #f59e0b;">
            <span>= Espèces attendues:</span>
            <span>{{ number_format($session->expected_cash, 0, ',', ' ') }} FCFA</span>
        </div>
        <div class="cash-row" style="font-size: 14px; font-weight: 700;">
            <span>Espèces comptées:</span>
            <span>{{ number_format($session->actual_cash, 0, ',', ' ') }} FCFA</span>
        </div>
        <div class="cash-difference">
            <div style="display: flex; justify-content: space-between;">
                <span>ÉCART:</span>
                <span style="color: {{ $session->cash_difference >= 0 ? '#10b981' : '#dc2626' }}">
                    {{ $session->cash_difference >= 0 ? '+' : '' }}{{ number_format($session->cash_difference, 0, ',', ' ') }} FCFA
                </span>
            </div>
        </div>
    </div>

    @if($session->hasCashDiscrepancy())
        <div class="warning">
            ⚠ ATTENTION: Un écart de caisse de {{ number_format(abs($session->cash_difference), 0, ',', ' ') }} FCFA a été détecté
            ({{ $session->cash_difference > 0 ? 'excédent' : 'manque' }}).
        </div>
    @else
        <div class="success">
            ✓ Caisse conforme - Aucun écart détecté
        </div>
    @endif

    <!-- Cancellations -->
    @if($session->cancelled_orders > 0)
        <div class="section">
            <div class="section-title">ANNULATIONS ET REMBOURSEMENTS</div>
            <table>
                <tr>
                    <td>Commandes annulées:</td>
                    <td class="text-right">{{ $session->cancelled_orders }}</td>
                </tr>
                <tr>
                    <td>Montant total remboursé:</td>
                    <td class="text-right font-weight: 700;">{{ number_format($session->refunds_total, 0, ',', ' ') }} FCFA</td>
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

    <!-- Notes -->
    @if($session->opening_notes || $session->closing_notes)
        <div class="section">
            <div class="section-title">NOTES</div>
            @if($session->opening_notes)
                <div style="margin-bottom: 10px;">
                    <strong>Ouverture:</strong> {{ $session->opening_notes }}
                </div>
            @endif
            @if($session->closing_notes)
                <div>
                    <strong>Fermeture:</strong> {{ $session->closing_notes }}
                </div>
            @endif
        </div>
    @endif

    <!-- Signature -->
    <div class="signature-box">
        <div style="margin-bottom: 20px;">
            <strong>Caissier:</strong> {{ $session->user->name }}
        </div>
        <div style="display: flex; justify-content: space-between;">
            <div style="width: 45%;">
                <div style="margin-bottom: 40px;">Signature du caissier:</div>
                <div style="border-top: 1px solid #000; padding-top: 5px; text-align: center; font-size: 9px;">
                    Signature
                </div>
            </div>
            <div style="width: 45%;">
                <div style="margin-bottom: 40px;">Signature du responsable:</div>
                <div style="border-top: 1px solid #000; padding-top: 5px; text-align: center; font-size: 9px;">
                    Signature
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <div>Rapport généré le {{ now()->format('d/m/Y à H:i:s') }}</div>
        <div style="margin-top: 5px;">{{ $tenant->name }} - Document Confidentiel</div>
        <div style="margin-top: 5px; font-weight: 600;">RAPPORT Z - ARCHIVE OBLIGATOIRE</div>
    </div>
</body>
</html>
