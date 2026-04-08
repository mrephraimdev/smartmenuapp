<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - {{ $tenant->name }} - Table {{ $table->code }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            min-height: 100vh;
        }

        /* Card */
        .qr-card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 420px;
            margin: 0 auto;
        }

        /* Top band */
        .card-top {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            padding: 28px 24px 24px;
            text-align: center;
            position: relative;
        }
        .card-top::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 40px;
            background: #fff;
            border-radius: 50%;
            border: 4px solid #e2e8f0;
        }

        .logo-wrap {
            width: 72px;
            height: 72px;
            border-radius: 16px;
            overflow: hidden;
            margin: 0 auto 12px;
            background: #fff;
            padding: 4px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .logo-wrap img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 12px;
        }
        .logo-placeholder {
            width: 100%;
            height: 100%;
            border-radius: 12px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 28px;
            font-weight: 900;
        }

        .tenant-name {
            color: #fff;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .tenant-info {
            color: #94a3b8;
            font-size: 13px;
        }

        /* QR section */
        .qr-section {
            padding: 36px 24px 20px;
            text-align: center;
        }
        .qr-frame {
            display: inline-block;
            padding: 16px;
            border: 3px solid #e2e8f0;
            border-radius: 20px;
            background: #fff;
            position: relative;
        }
        .qr-frame img {
            width: 220px;
            height: 220px;
            display: block;
        }

        /* Corner decorations */
        .qr-frame::before, .qr-frame::after {
            content: '';
            position: absolute;
            width: 24px;
            height: 24px;
            border: 3px solid #6366f1;
        }
        .qr-frame::before {
            top: -2px; left: -2px;
            border-right: none; border-bottom: none;
            border-radius: 8px 0 0 0;
        }
        .qr-frame::after {
            top: -2px; right: -2px;
            border-left: none; border-bottom: none;
            border-radius: 0 8px 0 0;
        }
        .corners-bottom::before, .corners-bottom::after {
            content: '';
            position: absolute;
            width: 24px;
            height: 24px;
            border: 3px solid #6366f1;
        }
        .corners-bottom::before {
            bottom: -2px; left: -2px;
            border-right: none; border-top: none;
            border-radius: 0 0 0 8px;
        }
        .corners-bottom::after {
            bottom: -2px; right: -2px;
            border-left: none; border-top: none;
            border-radius: 0 0 8px 0;
        }

        /* Table badge */
        .table-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 15px;
            font-weight: 700;
            margin-top: 20px;
            box-shadow: 0 4px 12px rgba(99,102,241,0.3);
        }

        /* CTA */
        .cta-section {
            padding: 0 24px 24px;
            text-align: center;
        }
        .cta-title {
            font-size: 20px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 4px;
        }
        .cta-sub {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 16px;
        }

        /* Steps */
        .steps {
            display: flex;
            justify-content: center;
            gap: 24px;
            margin-bottom: 20px;
        }
        .step {
            text-align: center;
        }
        .step-num {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #f1f5f9;
            color: #6366f1;
            font-weight: 800;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 4px;
        }
        .step-text {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
        }

        /* URL bar */
        .url-bar {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 11px;
            color: #64748b;
            word-break: break-all;
            text-align: left;
        }

        /* Buttons */
        .actions {
            display: flex;
            gap: 10px;
            padding: 0 24px 28px;
        }
        .btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }
        .btn-print {
            background: #1e293b;
            color: #fff;
        }
        .btn-print:hover { background: #0f172a; }
        .btn-pdf {
            background: #dc2626;
            color: #fff;
        }
        .btn-pdf:hover { background: #b91c1c; }
        .btn-test {
            background: #6366f1;
            color: #fff;
        }
        .btn-test:hover { background: #4f46e5; }
        .btn svg { width: 18px; height: 18px; }

        /* Back */
        .back-link {
            text-align: center;
            margin-top: 24px;
        }
        .back-link a {
            color: #94a3b8;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: color 0.2s;
        }
        .back-link a:hover { color: #fff; }

        /* Print */
        @media print {
            body { background: #fff !important; padding: 0 !important; }
            .no-print { display: none !important; }
            .qr-card { box-shadow: none; border: 2px solid #000; }
            .card-top { background: #fff !important; -webkit-print-color-adjust: exact; }
            .tenant-name { color: #000 !important; }
            .tenant-info { color: #333 !important; }
            .actions, .back-link, .url-bar { display: none !important; }
        }
    </style>
</head>
<body class="p-6">
    <div style="max-width: 420px; margin: 0 auto;">
        {{-- QR Card --}}
        <div class="qr-card">
            {{-- Top Band with Logo --}}
            <div class="card-top">
                <div class="logo-wrap">
                    @if($tenant->logo_url)
                        <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}">
                    @else
                        <div class="logo-placeholder">{{ strtoupper(substr($tenant->name, 0, 2)) }}</div>
                    @endif
                </div>
                <div class="tenant-name">{{ $tenant->name }}</div>
                @if($tenant->address)
                    <div class="tenant-info">{{ $tenant->address }}</div>
                @endif
            </div>

            {{-- QR Code --}}
            <div class="qr-section">
                <div class="qr-frame">
                    <div class="corners-bottom" style="position:absolute;inset:0;pointer-events:none;"></div>
                    <img src="{{ $qrCodeUrl }}" alt="QR Code Menu">
                </div>
                <div>
                    <span class="table-badge">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M12 6v12"/></svg>
                        Table {{ $table->label ?? $table->code }}
                    </span>
                </div>
            </div>

            {{-- CTA --}}
            <div class="cta-section">
                <div class="cta-title">Scannez pour commander</div>
                <div class="cta-sub">Accedez au menu digital en un instant</div>

                <div class="steps">
                    <div class="step">
                        <div class="step-num">1</div>
                        <div class="step-text">Scannez</div>
                    </div>
                    <div class="step">
                        <div class="step-num">2</div>
                        <div class="step-text">Choisissez</div>
                    </div>
                    <div class="step">
                        <div class="step-num">3</div>
                        <div class="step-text">Commandez</div>
                    </div>
                </div>

                <div class="url-bar no-print">{{ $menuUrl }}</div>
            </div>

            {{-- Actions --}}
            <div class="actions no-print">
                <button onclick="window.print()" class="btn btn-print">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Imprimer
                </button>
                <a href="{{ route('qrcode.pdf', [$tenant->id, $table->code]) }}" class="btn btn-pdf">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    PDF
                </a>
                <a href="{{ $menuUrl }}" target="_blank" class="btn btn-test">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Tester
                </a>
            </div>
        </div>

        {{-- Back --}}
        <div class="back-link no-print">
            <a href="{{ route('admin.dashboard', $tenant->slug) }}">
                ← Retour au dashboard
            </a>
        </div>
    </div>
</body>
</html>
