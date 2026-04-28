<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - {{ $tenant->name }} - Table {{ $table->label ?? $table->code }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            background: #0a0f1e;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
            position: relative;
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: fixed;
            top: -200px; left: 50%;
            transform: translateX(-50%);
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(99,102,241,0.15) 0%, transparent 70%);
            pointer-events: none;
        }
        body::after {
            content: '';
            position: fixed;
            bottom: -200px; left: 30%;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(251,191,36,0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .wrapper {
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 1;
        }

        /* ── CARD ── */
        .card {
            background: #fff;
            border-radius: 28px;
            overflow: hidden;
            box-shadow:
                0 0 0 1px rgba(255,255,255,0.06),
                0 32px 80px rgba(0,0,0,0.5),
                0 0 60px rgba(99,102,241,0.08);
        }

        /* ── HEADER ── */
        .card-header {
            background: linear-gradient(145deg, #1e293b 0%, #0f172a 100%);
            padding: 34px 32px 42px;
            position: relative;
            overflow: hidden;
        }
        .card-header::before {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 200px; height: 200px;
            background: radial-gradient(circle, rgba(99,102,241,0.2) 0%, transparent 70%);
            pointer-events: none;
        }
        .card-header::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(99,102,241,0.4), transparent);
        }

        .restaurant-row {
            display: flex;
            align-items: center;
            gap: 16px;
            position: relative;
            z-index: 1;
        }
        .restaurant-logo {
            width: 64px; height: 64px;
            border-radius: 18px;
            overflow: hidden;
            flex-shrink: 0;
            box-shadow: 0 4px 16px rgba(0,0,0,0.3);
        }
        .restaurant-logo img {
            width: 100%; height: 100%;
            object-fit: cover;
        }
        .restaurant-logo-placeholder {
            width: 64px; height: 64px;
            border-radius: 18px;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; font-weight: 900; color: #1e293b;
            flex-shrink: 0;
            box-shadow: 0 4px 16px rgba(251,191,36,0.3);
        }
        .restaurant-info { flex: 1; min-width: 0; }
        .restaurant-name {
            color: #fff;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.3px;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .restaurant-address {
            color: #64748b;
            font-size: 12px;
            margin-top: 4px;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .status-dot {
            width: 10px; height: 10px;
            background: #22c55e;
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(34,197,94,0.7);
            flex-shrink: 0;
        }

        /* ── WAVE ── */
        .wave {
            display: block;
            width: 100%;
            height: 32px;
            background: #0f172a;
            position: relative;
            margin-top: -1px;
        }
        .wave::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 32px;
            background: #fff;
            border-radius: 32px 32px 0 0;
        }

        /* ── QR SECTION ── */
        .qr-section {
            padding: 28px 28px 20px;
            text-align: center;
        }

        /* QR frame: gray border + tri-color corners */
        .qr-frame-wrap {
            display: inline-block;
            position: relative;
            padding: 16px;
            border: 2px solid #e2e8f0;
            border-radius: 20px;
            background: #fff;
        }
        .qr-frame-wrap::before {
            content: '';
            position: absolute;
            top: -2px; left: -2px;
            width: 28px; height: 28px;
            border-top: 3px solid #6366f1;
            border-left: 3px solid #6366f1;
            border-radius: 10px 0 0 0;
        }
        .qr-frame-wrap::after {
            content: '';
            position: absolute;
            top: -2px; right: -2px;
            width: 28px; height: 28px;
            border-top: 3px solid #fbbf24;
            border-right: 3px solid #fbbf24;
            border-radius: 0 10px 0 0;
        }
        .corner-bl {
            position: absolute;
            bottom: -2px; left: -2px;
            width: 28px; height: 28px;
            border-bottom: 3px solid #fbbf24;
            border-left: 3px solid #fbbf24;
            border-radius: 0 0 0 10px;
        }
        .corner-br {
            position: absolute;
            bottom: -2px; right: -2px;
            width: 28px; height: 28px;
            border-bottom: 3px solid #8b5cf6;
            border-right: 3px solid #8b5cf6;
            border-radius: 0 0 10px 0;
        }
        .qr-frame-wrap img {
            width: 200px; height: 200px;
            display: block;
        }

        /* Table badge */
        .table-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 700;
            margin-top: 20px;
            box-shadow: 0 4px 16px rgba(99,102,241,0.35);
        }
        .table-badge svg { width: 15px; height: 15px; }

        /* ── STEPS ── */
        .steps-strip {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 24px 0 0;
            padding: 0 8px;
        }
        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            flex: 1;
        }
        .step-num {
            width: 22px; height: 22px;
            border-radius: 50%;
            background: #6366f1;
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            display: flex; align-items: center; justify-content: center;
        }
        .step-icon {
            width: 42px; height: 42px;
            border-radius: 14px;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            display: flex; align-items: center; justify-content: center;
            font-size: 19px;
        }
        .step-label {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-align: center;
        }
        .step-sep {
            width: 28px;
            height: 2px;
            background: linear-gradient(90deg, #e2e8f0, #c7d2fe, #e2e8f0);
            border-radius: 1px;
            flex-shrink: 0;
            margin-bottom: 28px;
        }

        /* ── ACTIONS ── */
        .actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            padding: 20px;
        }
        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 13px;
            border-radius: 16px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.2s;
            font-family: inherit;
        }
        .btn svg { width: 16px; height: 16px; flex-shrink: 0; }
        .btn-print {
            background: #0f172a;
            color: #fff;
            grid-column: 1 / -1;
            padding: 15px;
            font-size: 14px;
            box-shadow: 0 4px 16px rgba(15,23,42,0.3);
        }
        .btn-print:hover { background: #1e293b; transform: translateY(-1px); }
        .btn-pdf {
            background: #fef2f2;
            color: #dc2626;
            border: 1.5px solid #fecaca;
        }
        .btn-pdf:hover { background: #fee2e2; }
        .btn-test {
            background: rgba(99,102,241,0.08);
            color: #6366f1;
            border: 1.5px solid rgba(99,102,241,0.2);
        }
        .btn-test:hover { background: rgba(99,102,241,0.15); }

        /* ── HORUSPOS FOOTER ── */
        .horus-footer {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px;
            border-top: 1px solid #f1f5f9;
        }
        .horus-footer-icon {
            width: 22px; height: 22px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 7px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .horus-footer-icon svg { width: 11px; height: 11px; color: #fff; }
        .horus-footer-text { font-size: 11px; color: #94a3b8; font-weight: 500; }
        .horus-footer-text strong { color: #6366f1; font-weight: 800; }

        /* ── BACK LINK ── */
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #475569;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s;
        }
        .back-link a:hover { color: #fff; }
        .back-link svg { width: 14px; height: 14px; }

        /* ── PRINT ── */
        @media print {
            body {
                background: #fff !important;
                padding: 0 !important;
                display: block;
            }
            body::before, body::after { display: none; }
            .wrapper { max-width: 100%; }
            .card {
                box-shadow: none;
                border: 1px solid #e2e8f0;
                border-radius: 16px;
            }
            .card-header {
                background: #1e293b !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print { display: none !important; }
            .back-link { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">

            {{-- Header restaurant --}}
            <div class="card-header">
                <div class="restaurant-row">
                    @if($tenant->logo_url)
                        <div class="restaurant-logo">
                            <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}">
                        </div>
                    @else
                        <div class="restaurant-logo-placeholder">
                            {{ strtoupper(substr($tenant->name, 0, 2)) }}
                        </div>
                    @endif
                    <div class="restaurant-info">
                        <div class="restaurant-name">{{ $tenant->name }}</div>
                        @if($tenant->address)
                            <div class="restaurant-address">{{ $tenant->address }}</div>
                        @endif
                    </div>
                    <div class="status-dot"></div>
                </div>
            </div>

            {{-- Wave --}}
            <div class="wave"></div>

            {{-- QR Section --}}
            <div class="qr-section">
                <div class="qr-frame-wrap">
                    <div class="corner-bl"></div>
                    <div class="corner-br"></div>
                    <img src="{{ $qrCodeUrl }}" alt="QR Code Menu">
                </div>

                {{-- Badge table --}}
                <div>
                    <span class="table-badge">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18M12 6v12"/>
                        </svg>
                        Table {{ $table->label ?? $table->code }}
                    </span>
                </div>

                {{-- Étapes --}}
                <div class="steps-strip">
                    <div class="step-item">
                        <div class="step-num">1</div>
                        <div class="step-icon">📷</div>
                        <div class="step-label">Scannez</div>
                    </div>
                    <div class="step-sep"></div>
                    <div class="step-item">
                        <div class="step-num">2</div>
                        <div class="step-icon">🍽️</div>
                        <div class="step-label">Choisissez</div>
                    </div>
                    <div class="step-sep"></div>
                    <div class="step-item">
                        <div class="step-num">3</div>
                        <div class="step-icon">✅</div>
                        <div class="step-label">Commandez</div>
                    </div>
                </div>
            </div>

            {{-- Boutons --}}
            <div class="actions no-print">
                <button onclick="window.print()" class="btn btn-print">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.056 48.056 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/>
                    </svg>
                    Imprimer cette affiche
                </button>
                <a href="{{ route('qrcode.pdf', [$tenant->id, $table->code]) }}" class="btn btn-pdf">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                    Exporter PDF
                </a>
                <a href="{{ $menuUrl }}" target="_blank" class="btn btn-test">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                    </svg>
                    Tester le menu
                </a>
            </div>

            {{-- Footer HorusPOS --}}
            <div class="horus-footer">
                <div class="horus-footer-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                    </svg>
                </div>
                <span class="horus-footer-text">Propulsé par <strong>HorusPOS</strong></span>
            </div>
        </div>

        {{-- Retour --}}
        <div class="back-link no-print">
            <a href="{{ route('admin.dashboard', $tenant->slug) }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                </svg>
                Retour au dashboard
            </a>
        </div>
    </div>
</body>
</html>
