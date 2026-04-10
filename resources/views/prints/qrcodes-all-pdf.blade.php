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

        /* Card container */
        .card {
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            max-width: 380px;
            margin: 0 auto;
        }

        /* Top band */
        .card-top {
            background: #1e293b;
            padding: 24px 20px 20px;
            text-align: center;
        }
        .logo {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            object-fit: contain;
            margin: 0 auto 10px;
            display: block;
            background: #fff;
            padding: 3px;
        }
        .logo-placeholder {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            background: #6366f1;
            color: #fff;
            font-size: 24px;
            font-weight: 900;
            line-height: 60px;
            text-align: center;
            margin: 0 auto 10px;
        }
        .tenant-name {
            color: #ffffff;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .tenant-address {
            color: #94a3b8;
            font-size: 11px;
            margin-top: 4px;
        }

        /* QR Section */
        .qr-section {
            padding: 28px 20px 16px;
        }
        .qr-frame {
            display: inline-block;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
        }
        .qr-frame img {
            width: 200px;
            height: 200px;
            display: block;
        }

        /* Table badge */
        .table-badge {
            display: inline-block;
            background: #6366f1;
            color: #ffffff;
            padding: 8px 24px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 700;
            margin-top: 16px;
        }

        /* CTA */
        .cta-section {
            padding: 8px 20px 24px;
        }
        .cta-title {
            font-size: 18px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 4px;
        }
        .cta-sub {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 16px;
        }

        /* Steps */
        .steps-table {
            margin: 0 auto 16px;
        }
        .steps-table td {
            text-align: center;
            padding: 0 16px;
        }
        .step-num {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #f1f5f9;
            color: #6366f1;
            font-weight: 800;
            font-size: 13px;
            line-height: 28px;
            text-align: center;
            margin: 0 auto 4px;
        }
        .step-text {
            font-size: 10px;
            color: #64748b;
            font-weight: 600;
        }

        /* URL */
        .url-bar {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 9px;
            color: #64748b;
            word-break: break-all;
            text-align: center;
        }

        /* Footer */
        .footer {
            margin-top: 20px;
            font-size: 9px;
            color: #94a3b8;
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
            {{-- Top Band --}}
            <div class="card-top">
                @if($tenant->logo_url)
                    <img src="{{ public_path(str_replace('/storage/', 'storage/', $tenant->logo_url)) }}" alt="{{ $tenant->name }}" class="logo">
                @else
                    <div class="logo-placeholder">{{ strtoupper(substr($tenant->name, 0, 2)) }}</div>
                @endif
                <div class="tenant-name">{{ $tenant->name }}</div>
                @if($tenant->address)
                    <div class="tenant-address">{{ $tenant->address }}</div>
                @endif
            </div>

            {{-- QR Code --}}
            <div class="qr-section">
                <div class="qr-frame">
                    <img src="{{ $qrCodes[$table->code] }}" alt="QR Code">
                </div>
                <div>
                    <div class="table-badge">Table {{ $table->label ?? $table->code }}</div>
                </div>
            </div>

            {{-- CTA --}}
            <div class="cta-section">
                <div class="cta-title">Scannez pour commander</div>
                <div class="cta-sub">Accedez au menu digital en un instant</div>

                <table class="steps-table">
                    <tr>
                        <td>
                            <div class="step-num">1</div>
                            <div class="step-text">Scannez</div>
                        </td>
                        <td>
                            <div class="step-num">2</div>
                            <div class="step-text">Choisissez</div>
                        </td>
                        <td>
                            <div class="step-num">3</div>
                            <div class="step-text">Commandez</div>
                        </td>
                    </tr>
                </table>

                <div class="url-bar">{{ $menuUrl }}</div>
            </div>
        </div>

        <div class="footer">{{ $tenant->name }} - QR Code Table {{ $table->label ?? $table->code }}</div>
    </div>
    @endforeach
</body>
</html>
