<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport journalier - {{ $date }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            font-weight: bold;
            color: #000;
            line-height: 1.4;
            width: 80mm;
        }

        /* Header */
        .header { text-align: center; margin-bottom: 4px; }
        .rname { font-size: 18px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .rinfo { font-size: 11px; margin-top: 2px; }

        /* Separators */
        .sep { border: none; border-top: 2px solid #000; margin: 5px 0; }
        .sep-d { border: none; border-top: 2px dashed #000; margin: 5px 0; }
        .stars { text-align: center; font-size: 11px; letter-spacing: 3px; margin: 3px 0; }

        /* Title */
        .title { text-align: center; font-size: 15px; font-weight: bold; letter-spacing: 2px; padding: 4px 0; text-transform: uppercase; }

        /* Section headers */
        .sec {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            padding: 3px 0;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            letter-spacing: 1px;
            margin: 5px 0 4px 0;
        }

        /* Key-value rows using table */
        .r { width: 100%; border-collapse: collapse; margin: 2px 0; font-size: 12px; font-weight: bold; }
        .r td { padding: 1px 0; }
        .r .label { text-align: left; white-space: nowrap; }
        .r .dots { width: 100%; border-bottom: 1px dotted #000; }
        .r .val { text-align: right; white-space: nowrap; padding-left: 4px; }

        /* Stat box */
        .stat-box { border: 2px solid #000; padding: 4px 5px; margin: 4px 0; }
        .stat-big { width: 100%; border-collapse: collapse; font-size: 17px; font-weight: bold; }
        .stat-big td { padding: 0; }
        .stat-label { font-size: 10px; font-weight: bold; text-transform: uppercase; }

        /* Tables */
        .tbl { width: 100%; border-collapse: collapse; margin: 2px 0 5px 0; }
        .tbl th { font-size: 10px; font-weight: bold; text-transform: uppercase; padding: 3px 2px; border-bottom: 2px solid #000; border-top: 2px solid #000; }
        .tbl td { font-size: 11px; font-weight: bold; padding: 3px 2px; border-bottom: 1px dotted #999; }
        .tbl .tr { text-align: right; }
        .tbl .total-row td { border-top: 2px solid #000; border-bottom: 2px solid #000; font-size: 12px; }

        /* Unpaid box */
        .warn-box { border: 2px solid #000; padding: 4px 5px; margin: 4px 0; font-size: 11px; font-weight: bold; }

        /* Footer */
        .foot { text-align: center; margin-top: 6px; }
        .thx { font-size: 12px; font-weight: bold; margin-bottom: 2px; }
        .fs { font-size: 10px; font-weight: bold; }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <div class="header">
        <div class="rname">{{ $tenant->name }}</div>
        @if($tenant->address || $tenant->phone)
        <div class="rinfo">
            @if($tenant->address){{ $tenant->address }}@endif
            @if($tenant->phone) — Tel: {{ $tenant->phone }}@endif
        </div>
        @endif
    </div>

    <div class="sep"></div>
    <div class="title">Rapport journalier</div>

    <table class="r"><tr>
        <td class="label">Date</td>
        <td class="dots"></td>
        <td class="val">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</td>
    </tr></table>
    <table class="r"><tr>
        <td class="label">Généré</td>
        <td class="dots"></td>
        <td class="val">{{ $printedAt->format('H:i') }}</td>
    </tr></table>

    <div class="sep"></div>

    {{-- RÉSUMÉ --}}
    <div class="sec">Résumé du jour</div>

    <div class="stat-box">
        <table class="stat-big"><tr>
            <td>ENCAISSÉ</td>
            <td style="text-align:right">{{ number_format($stats['total_payments'], 0, ',', ' ') }} F</td>
        </tr></table>
        <div class="stat-label">{{ $stats['payment_count'] }} paiement(s) enregistré(s)</div>
    </div>

    <table class="r"><tr><td class="label">Commandes totales</td><td class="dots"></td><td class="val">{{ $stats['total_orders'] }}</td></tr></table>
    <table class="r"><tr><td class="label">Commandes servies</td><td class="dots"></td><td class="val">{{ $stats['served_orders'] ?? 0 }}</td></tr></table>
    <table class="r"><tr><td class="label">Commandes annulées</td><td class="dots"></td><td class="val">{{ $stats['cancelled_orders'] ?? 0 }}</td></tr></table>

    @if(($stats['unpaid_orders'] ?? 0) > 0)
    <div class="warn-box">
        IMPAYES : {{ $stats['unpaid_orders'] }} commandes — {{ number_format($stats['unpaid_amount'] ?? 0, 0, ',', ' ') }} F
    </div>
    @endif

    <div class="sep-d"></div>

    {{-- PAIEMENTS PAR MODE --}}
    <div class="sec">Paiements par mode</div>
    <table class="tbl">
        <tr>
            <th style="text-align:left">Mode</th>
            <th class="tr">Nb</th>
            <th class="tr">Montant F</th>
        </tr>
        @foreach($stats['payments_by_method'] as $data)
            @if($data['count'] > 0)
            <tr>
                <td>{{ $data['label'] }}</td>
                <td class="tr">{{ $data['count'] }}</td>
                <td class="tr">{{ number_format($data['total'], 0, ',', ' ') }}</td>
            </tr>
            @endif
        @endforeach
        <tr class="total-row">
            <td>TOTAL</td>
            <td class="tr">{{ $stats['payment_count'] }}</td>
            <td class="tr">{{ number_format($stats['total_payments'], 0, ',', ' ') }}</td>
        </tr>
    </table>

    <div class="sep"></div>

    {{-- FOOTER --}}
    <div class="foot">
        <div class="stars">* * * * * * * * *</div>
        <div class="thx">{{ $tenant->name }}</div>
        <div class="fs">Rapport du {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</div>
        <div class="fs">Généré le {{ $printedAt->format('d/m/Y à H:i') }}</div>
        <div class="stars">* * * * * * * * *</div>
    </div>

</body>
</html>
