<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>QR Codes - {{ $tenant->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            background: #fff;
            color: #1e293b;
        }

        .page {
            page-break-after: always;
            width: 100%;
            text-align: center;
            padding: 30px 40px;
        }
        .page:last-child {
            page-break-after: avoid;
        }

        /* Card */
        .card {
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            max-width: 380px;
            margin: 0 auto;
        }

        /* Header */
        .card-header {
            background: #1e293b;
            padding: 22px 20px 26px;
        }
        .header-table {
            width: 100%;
        }
        .header-table td {
            vertical-align: middle;
        }
        .logo-cell {
            width: 64px;
            padding-right: 12px;
        }
        .logo-img {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            object-fit: cover;
            display: block;
        }
        .logo-placeholder {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            background: #f59e0b;
            color: #1e293b;
            font-size: 22px;
            font-weight: 900;
            line-height: 56px;
            text-align: center;
            display: block;
        }
        .info-cell {
            text-align: left;
        }
        .tenant-name {
            color: #ffffff;
            font-size: 18px;
            font-weight: 800;
        }
        .tenant-address {
            color: #94a3b8;
            font-size: 10px;
            margin-top: 3px;
        }
        .status-cell {
            width: 14px;
            text-align: right;
        }
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #22c55e;
            display: inline-block;
        }

        /* Wave separator */
        .wave-top {
            background: #1e293b;
            height: 20px;
        }
        .wave-bottom {
            background: #fff;
            border-radius: 20px 20px 0 0;
            height: 20px;
            margin-top: -20px;
        }

        /* QR section */
        .qr-section {
            padding: 20px 20px 12px;
            text-align: center;
        }

        /* QR frame */
        .qr-frame-outer {
            display: inline-block;
            position: relative;
            padding: 14px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            background: #fff;
        }
        .qr-frame-outer img {
            width: 190px;
            height: 190px;
            display: block;
        }
        .corner {
            position: absolute;
            width: 24px;
            height: 24px;
        }
        .corner-tl { top: -2px; left: -2px; border-top: 3px solid #6366f1; border-left: 3px solid #6366f1; border-radius: 8px 0 0 0; }
        .corner-tr { top: -2px; right: -2px; border-top: 3px solid #f59e0b; border-right: 3px solid #f59e0b; border-radius: 0 8px 0 0; }
        .corner-bl { bottom: -2px; left: -2px; border-bottom: 3px solid #f59e0b; border-left: 3px solid #f59e0b; border-radius: 0 0 0 8px; }
        .corner-br { bottom: -2px; right: -2px; border-bottom: 3px solid #8b5cf6; border-right: 3px solid #8b5cf6; border-radius: 0 0 8px 0; }

        /* Table badge */
        .table-badge {
            display: inline-block;
            background: #6366f1;
            color: #ffffff;
            padding: 7px 20px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 700;
            margin-top: 14px;
        }

        /* Steps */
        .steps-section {
            padding: 16px 20px 12px;
        }
        .steps-table {
            width: 100%;
            margin: 0 auto;
        }
        .steps-table td {
            text-align: center;
            padding: 0 10px;
            width: 33%;
        }
        .step-num {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #6366f1;
            color: #fff;
            font-weight: 800;
            font-size: 11px;
            line-height: 22px;
            text-align: center;
            margin: 0 auto 5px;
        }
        .step-icon {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            font-size: 17px;
            line-height: 38px;
            text-align: center;
            margin: 0 auto 5px;
        }
        .step-label {
            font-size: 10px;
            color: #64748b;
            font-weight: 600;
        }

        /* HorusPOS footer */
        .horus-footer {
            border-top: 1px solid #f1f5f9;
            padding: 10px;
            text-align: center;
        }
        .horus-footer-icon {
            display: inline-block;
            width: 18px;
            height: 18px;
            border-radius: 6px;
            background: #6366f1;
            vertical-align: middle;
            margin-right: 5px;
        }
        .horus-footer-text {
            font-size: 10px;
            color: #94a3b8;
            vertical-align: middle;
        }
        .horus-footer-name {
            color: #6366f1;
            font-weight: 800;
        }

        /* Footer */
        .footer {
            margin-top: 16px;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>
    @foreach($tables as $table)
    @php
        $menuUrl = url("/menu/{$tenant->id}/{$table->code}");
    @endphp
    <div class="page">
        <div class="card">

            {{-- Header --}}
            <div class="card-header">
                <table class="header-table">
                    <tr>
                        <td class="logo-cell">
                            @if($tenant->logo_url)
                                <img src="{{ public_path(str_replace('/storage/', 'storage/', $tenant->logo_url)) }}"
                                     alt="{{ $tenant->name }}" class="logo-img">
                            @else
                                <div class="logo-placeholder">{{ strtoupper(substr($tenant->name, 0, 2)) }}</div>
                            @endif
                        </td>
                        <td class="info-cell">
                            <div class="tenant-name">{{ $tenant->name }}</div>
                            @if($tenant->address)
                                <div class="tenant-address">{{ $tenant->address }}</div>
                            @endif
                        </td>
                        <td class="status-cell">
                            <span class="status-dot"></span>
                        </td>
                    </tr>
                </table>
            </div>

            {{-- Wave --}}
            <div class="wave-top"><div class="wave-bottom"></div></div>

            {{-- QR Code --}}
            <div class="qr-section">
                <div class="qr-frame-outer">
                    <div class="corner corner-tl"></div>
                    <div class="corner corner-tr"></div>
                    <div class="corner corner-bl"></div>
                    <div class="corner corner-br"></div>
                    <img src="{{ $qrCodes[$table->code] }}" alt="QR Code">
                </div>
                <div>
                    <span class="table-badge">Table {{ $table->label ?? $table->code }}</span>
                </div>
            </div>

            {{-- Steps --}}
            <div class="steps-section">
                <table class="steps-table">
                    <tr>
                        <td>
                            <div class="step-num">1</div>
                            <div class="step-icon">📷</div>
                            <div class="step-label">Scannez</div>
                        </td>
                        <td>
                            <div class="step-num">2</div>
                            <div class="step-icon">🍽</div>
                            <div class="step-label">Choisissez</div>
                        </td>
                        <td>
                            <div class="step-num">3</div>
                            <div class="step-icon">✅</div>
                            <div class="step-label">Commandez</div>
                        </td>
                    </tr>
                </table>
            </div>

            {{-- HorusPOS footer --}}
            <div class="horus-footer">
                <span class="horus-footer-icon"></span>
                <span class="horus-footer-text">Propulsé par <span class="horus-footer-name">HorusPOS</span></span>
            </div>
        </div>

        <div class="footer">{{ $tenant->name }} — QR Code Table {{ $table->label ?? $table->code }}</div>
    </div>
    @endforeach
</body>
</html>
