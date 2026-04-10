<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport journalier - {{ $date }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', 'Lucida Console', monospace;
            font-size: 14px;
            font-weight: 900;
            width: 80mm;
            padding: 4mm;
            background: #fff;
            color: #000;
            line-height: 1.4;
            -webkit-text-size-adjust: none;
        }

        .c { text-align: center; }

        /* Header */
        .header { text-align: center; margin-bottom: 4px; }
        .rname { font-size: 20px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; }
        .rinfo { font-size: 12px; font-weight: 900; margin-top: 2px; }

        /* Separators */
        .sep { border: none; height: 2px; background: #000; margin: 6px 0; }
        .sep-d { border: none; border-top: 2px dashed #000; margin: 6px 0; }
        .stars { text-align: center; font-size: 12px; letter-spacing: 3px; margin: 4px 0; }

        /* Title */
        .title {
            text-align: center;
            font-size: 16px;
            font-weight: 900;
            letter-spacing: 2px;
            padding: 4px 0;
            text-transform: uppercase;
        }

        /* Section headers */
        .sec {
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            text-align: center;
            padding: 3px 0;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            letter-spacing: 1px;
            margin: 6px 0 4px 0;
        }

        /* Key-value rows */
        .r {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 13px;
            font-weight: 900;
        }
        .r .dots {
            flex: 1;
            border-bottom: 1px dotted #000;
            margin: 0 4px;
            min-width: 8px;
            height: 12px;
        }
        .r span:first-child { flex-shrink: 0; }
        .r span:last-child { flex-shrink: 0; text-align: right; }

        /* Stat box */
        .stat-box {
            border: 2px solid #000;
            padding: 4px 6px;
            margin: 4px 0;
        }
        .stat-big {
            display: flex;
            justify-content: space-between;
            font-size: 18px;
            font-weight: 900;
        }
        .stat-label { font-size: 11px; font-weight: 900; text-transform: uppercase; }

        /* Tables */
        .tbl { width: 100%; border-collapse: collapse; margin: 2px 0 6px 0; }
        .tbl th {
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            padding: 3px 2px;
            border-bottom: 2px solid #000;
            border-top: 2px solid #000;
        }
        .tbl td {
            font-size: 12px;
            font-weight: 900;
            padding: 3px 2px;
            border-bottom: 1px dotted #999;
        }
        .tbl .tr { text-align: right; }
        .tbl .total-row td {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            font-size: 13px;
        }
        .tbl .tc { text-align: center; }

        /* Order detail condensed */
        .od {
            font-size: 12px;
            font-weight: 900;
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
            border-bottom: 1px dotted #ccc;
        }
        .od .left { flex: 1; }
        .od .right { text-align: right; flex-shrink: 0; min-width: 55px; }

        /* Unpaid box */
        .warn-box {
            border: 2px solid #000;
            padding: 4px 6px;
            margin: 4px 0;
            font-size: 12px;
            font-weight: 900;
        }

        /* Footer */
        .foot { text-align: center; margin-top: 8px; }
        .thx { font-size: 13px; font-weight: 900; margin-bottom: 3px; }
        .fs { font-size: 11px; font-weight: 900; }

        /* Print button */
        .pbtn {
            position: fixed; top: 10px; right: 10px;
            padding: 10px 20px; background: #000; color: #fff;
            border: none; cursor: pointer; font-size: 14px;
            font-family: sans-serif; font-weight: bold;
        }

        @media print {
            body { width: 80mm; margin: 0; }
            .pbtn { display: none !important; }
            @page { size: 80mm auto; margin: 0; }
        }
    </style>
</head>
<body>
    <button class="pbtn" onclick="window.print()">IMPRIMER</button>

    {{-- ====== HEADER ====== --}}
    <div class="header">
        @if($tenant->logo_url)
            <img src="{{ $tenant->logo_url }}" alt="" style="width:50px;height:50px;object-fit:contain;margin:0 auto 4px;display:block;">
        @endif
        <div class="rname">{{ $tenant->name }}</div>
        @if($tenant->address || $tenant->phone)
        <div class="rinfo">
            @if($tenant->address){{ $tenant->address }}@endif
            @if($tenant->phone)<br>Tel: {{ $tenant->phone }}@endif
        </div>
        @endif
    </div>

    <div class="sep"></div>
    <div class="title">Rapport journalier</div>
    <div class="r"><span>Date</span><span class="dots"></span><span>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</span></div>
    <div class="r"><span>Imprimé</span><span class="dots"></span><span>{{ $printedAt->format('H:i') }}</span></div>
    <div class="sep"></div>

    {{-- ====== RÉSUMÉ DU JOUR ====== --}}
    <div class="sec">Résumé du jour</div>

    <div class="stat-box">
        <div class="stat-big"><span>ENCAISSÉ</span><span>{{ number_format($stats['total_payments'], 0, ',', ' ') }} F</span></div>
        <div class="stat-label">Total encaissé sur {{ $stats['payment_count'] }} paiement(s)</div>
    </div>

    <div class="r"><span>Commandes totales</span><span class="dots"></span><span>{{ $stats['total_orders'] }}</span></div>
    <div class="r"><span>Commandes servies</span><span class="dots"></span><span>{{ $stats['served_orders'] ?? 0 }}</span></div>
    <div class="r"><span>Commandes annulées</span><span class="dots"></span><span>{{ $stats['cancelled_orders'] ?? 0 }}</span></div>

    @if(($stats['unpaid_orders'] ?? 0) > 0)
    <div class="warn-box">
        IMPAYES: {{ $stats['unpaid_orders'] }} cmd — {{ number_format($stats['unpaid_amount'] ?? 0, 0, ',', ' ') }} F
    </div>
    @endif

    <div class="sep-d"></div>

    {{-- ====== PAIEMENTS PAR MODE ====== --}}
    <div class="sec">Paiements par mode</div>
    <table class="tbl">
        <tr>
            <th style="text-align:left">Mode</th>
            <th class="tr">Nb</th>
            <th class="tr">Montant F</th>
        </tr>
        @foreach($stats['payments_by_method'] as $method => $data)
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

    <div class="sep-d"></div>

    {{-- ====== PLATS POPULAIRES ====== --}}
    @if(!empty($stats['popular_dishes']))
    <div class="sec">Plats populaires</div>
    <table class="tbl">
        <tr>
            <th style="text-align:left">Plat</th>
            <th class="tr">Qté</th>
        </tr>
        @foreach($stats['popular_dishes'] as $dish => $qty)
        <tr>
            <td>{{ $dish }}</td>
            <td class="tr">{{ $qty }}</td>
        </tr>
        @endforeach
    </table>
    <div class="sep-d"></div>
    @endif

    {{-- ====== DÉTAIL COMMANDES ====== --}}
    <div class="sec">Détail commandes</div>
    @foreach($orders as $order)
    <div class="od">
        <div class="left">
            <span>{{ $order->order_number }}</span>
            <span style="font-size:11px"> {{ $order->created_at->format('H:i') }}</span>
            <span style="font-size:11px"> [{{ $order->table->code ?? 'Cprt' }}]</span>
        </div>
        <div class="right">
            {{ number_format($order->total, 0, ',', ' ') }} F
            <br><span style="font-size:10px">
                @if($order->status === 'ANNULE') ANNULE
                @elseif($order->payment_status === 'PAID') PAYE
                @elseif($order->payment_status === 'PARTIAL') PART.
                @else IMPAYE
                @endif
            </span>
        </div>
    </div>
    @endforeach

    {{-- Total commandes --}}
    @php
        $totalCA = $orders->where('status', '!=', 'ANNULE')->sum('total');
    @endphp
    <div style="display:flex;justify-content:space-between;border-top:2px solid #000;margin-top:4px;padding-top:4px;font-size:14px;font-weight:900;">
        <span>{{ $orders->where('status','!=','ANNULE')->count() }} commandes</span>
        <span>{{ number_format($totalCA, 0, ',', ' ') }} F</span>
    </div>

    <div class="sep"></div>

    {{-- ====== FOOTER ====== --}}
    <div class="foot">
        <div class="stars">* * * * * * * * *</div>
        <div class="thx">{{ $tenant->name }}</div>
        <div class="fs">Rapport du {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</div>
        <div class="fs">Généré le {{ $printedAt->format('d/m/Y à H:i') }}</div>
        <div class="stars">* * * * * * * * *</div>
    </div>

    <script>
        window.onload = function() { window.print(); };
        window.onafterprint = function() { window.close(); };
    </script>
</body>
</html>
