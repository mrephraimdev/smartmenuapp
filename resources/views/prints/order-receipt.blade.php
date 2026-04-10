<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commande #{{ $order->order_number }}</title>
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
        .header-logo {
            width: 56px; height: 56px;
            object-fit: contain;
            margin: 0 auto 4px;
            display: block;
        }
        .rname {
            font-size: 22px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .rinfo {
            font-size: 12px;
            font-weight: 900;
            margin-top: 2px;
        }

        /* Separators */
        .sep { border: none; height: 2px; background: #000; margin: 6px 0; }
        .sep-d { border: none; border-top: 2px dashed #000; margin: 6px 0; }
        .stars { text-align: center; font-size: 12px; letter-spacing: 3px; margin: 4px 0; }

        /* Title */
        .title {
            text-align: center;
            font-size: 18px;
            font-weight: 900;
            letter-spacing: 2px;
            padding: 4px 0;
        }

        /* Rows */
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
            min-width: 10px;
            height: 12px;
        }
        .r span:first-child { flex-shrink: 0; }
        .r span:last-child { flex-shrink: 0; }

        /* Items */
        .ih {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            padding-bottom: 3px;
            border-bottom: 2px solid #000;
            margin-bottom: 5px;
        }
        .it { margin-bottom: 6px; }
        .il {
            display: flex;
            font-size: 14px;
            font-weight: 900;
        }
        .iq { width: 30px; flex-shrink: 0; }
        .iname { flex: 1; }
        .ip { text-align: right; min-width: 70px; }
        .isub {
            font-size: 11px;
            font-weight: 900;
            padding-left: 30px;
        }

        /* Totals */
        .trow {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 14px;
            font-weight: 900;
        }
        .gt-box {
            border: 2px solid #000;
            padding: 5px 6px;
            margin-top: 6px;
        }
        .gt {
            display: flex;
            justify-content: space-between;
            font-size: 20px;
            font-weight: 900;
        }

        /* Status badge */
        .status-badge {
            text-align: center;
            font-size: 14px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 4px 8px;
            border: 2px solid #000;
            margin-top: 6px;
        }

        /* Footer */
        .foot { text-align: center; margin-top: 10px; }
        .thx { font-size: 15px; font-weight: 900; margin-bottom: 4px; }
        .fs { font-size: 11px; font-weight: 900; }

        /* Print */
        @media print {
            body { width: 80mm; margin: 0; }
            .no-print { display: none !important; }
            @page { size: 80mm auto; margin: 0; }
        }
        .pbtn {
            position: fixed; top: 10px; right: 10px;
            padding: 10px 20px; background: #000; color: #fff;
            border: none; cursor: pointer; font-size: 14px;
            font-family: sans-serif; font-weight: bold;
        }
    </style>
</head>
<body>
    <button class="pbtn no-print" onclick="window.print()">IMPRIMER</button>

    {{-- ====== HEADER ====== --}}
    <div class="header">
        @if($tenant->logo_url)
            <img src="{{ $tenant->logo_url }}" alt="" class="header-logo">
        @endif
        <div class="rname">{{ $tenant->name }}</div>
        @if($tenant->address || $tenant->phone)
            <div class="rinfo">
                @if($tenant->address){{ $tenant->address }}@endif
                @if($tenant->address && $tenant->phone)<br>@endif
                @if($tenant->phone)Tel: {{ $tenant->phone }}@endif
            </div>
        @endif
    </div>

    <div class="sep"></div>
    <div class="title">TICKET DE COMMANDE</div>
    <div class="sep"></div>

    {{-- ====== INFO ====== --}}
    <div class="r"><span>Commande</span><span class="dots"></span><span>{{ $order->order_number }}</span></div>
    <div class="r"><span>Table</span><span class="dots"></span><span>{{ $order->table->label ?? 'Comptoir / Emporter' }}</span></div>

    @php
        $customerName = null;
        if ($order->notes && preg_match('/Client\s*:\s*(.+)/i', $order->notes, $matches)) {
            $customerName = trim($matches[1]);
            // Stop at newline if notes continue
            if (($pos = strpos($customerName, "\n")) !== false) {
                $customerName = trim(substr($customerName, 0, $pos));
            }
        }
    @endphp
    @if($customerName)
    <div class="r"><span>Client</span><span class="dots"></span><span>{{ $customerName }}</span></div>
    @endif

    <div class="r"><span>Date</span><span class="dots"></span><span>{{ $order->created_at->format('d/m/Y') }}</span></div>
    <div class="r"><span>Heure</span><span class="dots"></span><span>{{ $order->created_at->format('H:i') }}</span></div>

    <div class="sep-d"></div>

    {{-- ====== ARTICLES ====== --}}
    <div class="ih"><span>ARTICLE</span><span>MONTANT</span></div>

    @php $nb = 0; @endphp
    @foreach($order->items as $item)
        @php
            $nb += $item->quantity;
            $lineTotal = $item->quantity * $item->unit_price;
        @endphp
        <div class="it">
            <div class="il">
                <span class="iq">{{ $item->quantity }}x</span>
                <span class="iname">{{ $item->dish->name ?? 'Article' }}</span>
                <span class="ip">{{ number_format($lineTotal, 0, ',', ' ') }}</span>
            </div>
            @if($item->quantity > 1)
                <div class="isub">@ {{ number_format($item->unit_price, 0, ',', ' ') }} F/u</div>
            @endif
            @if($item->variant)
                <div class="isub">{{ $item->variant->name }}</div>
            @endif
            @if($item->notes)
                <div class="isub">* {{ $item->notes }}</div>
            @endif
        </div>
    @endforeach

    <div class="sep-d"></div>

    {{-- ====== TOTAUX ====== --}}
    <div class="trow"><span>{{ $nb }} article{{ $nb > 1 ? 's' : '' }}</span><span>{{ number_format($order->total, 0, ',', ' ') }} F</span></div>

    <div class="gt-box">
        <div class="gt"><span>TOTAL</span><span>{{ number_format($order->total, 0, ',', ' ') }} F</span></div>
    </div>

    <div class="sep"></div>

    {{-- ====== STATUS ====== --}}
    <div class="status-badge">{{ $order->getStatusLabel() }}</div>

    <div class="sep"></div>

    {{-- ====== FOOTER ====== --}}
    <div class="foot">
        <div class="stars">* * * * * * * * *</div>
        <div class="thx">Merci de votre visite !</div>
        <div class="fs">{{ $tenant->name }}</div>
        <div class="fs">{{ now()->format('d/m/Y H:i') }}</div>
        <div class="stars">* * * * * * * * *</div>
    </div>

    <script>
        window.onload = function() { window.print(); };
        window.onafterprint = function() { window.close(); };
    </script>
</body>
</html>
