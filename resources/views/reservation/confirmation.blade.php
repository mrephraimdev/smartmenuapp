<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation confirmée — {{ $tenant->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;0,9..40,800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --amber:     #f59e0b;
            --amber-l:   #fcd34d;
            --amber-dim: rgba(245,158,11,.13);
            --amber-bd:  rgba(245,158,11,.3);
            --green:     #22c55e;
            --green-dim: rgba(34,197,94,.1);
            --green-bd:  rgba(34,197,94,.25);
            --red:       #ef4444;
            --s0:        #0b0f1a;
            --s2:        #111827;
            --s3:        #1f2937;
            --s4:        #374151;
            --bd:        rgba(255,255,255,.07);
            --bd2:       rgba(255,255,255,.11);
            --text:      #f9fafb;
            --mut:       #6b7280;
            --mut2:      #9ca3af;
        }

        html, body {
            min-height: 100%;
            background: var(--s0);
            color: var(--text);
            font-family: 'DM Sans', ui-sans-serif, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        /* Grid texture */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(245,158,11,.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(245,158,11,.03) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
            z-index: 0;
        }

        /* Ambient glow — green tint for success */
        .glow {
            position: fixed;
            top: -140px;
            left: 50%;
            transform: translateX(-50%);
            width: 560px;
            height: 300px;
            background: radial-gradient(ellipse at center,
                rgba(34,197,94,.1) 0%,
                rgba(245,158,11,.06) 45%,
                transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        /* Layout */
        .page {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 48px 16px 64px;
        }

        /* ── Success header ── */
        .success-header {
            text-align: center;
            margin-bottom: 32px;
            animation: fadeDown .5s ease both;
        }

        /* Animated checkmark ring */
        .check-ring {
            width: 72px; height: 72px;
            border-radius: 50%;
            border: 1.5px solid var(--green-bd);
            background: var(--green-dim);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 18px;
            position: relative;
            animation: popIn .5s cubic-bezier(.34,1.56,.64,1) both .1s;
        }
        .check-ring svg { width: 30px; height: 30px; color: var(--green); }
        /* Pulse ring */
        .check-ring::after {
            content: '';
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            border: 1px solid var(--green-bd);
            animation: pulse 2.5s ease-out infinite 1s;
        }

        .success-header h1 {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -.02em;
            color: var(--text);
            margin-bottom: 6px;
        }
        .success-header p {
            font-size: 13px;
            color: var(--mut2);
        }

        /* ── Card ── */
        .card {
            width: 100%;
            max-width: 460px;
            background: var(--s2);
            border: 1px solid var(--bd);
            border-radius: 24px;
            overflow: hidden;
            animation: fadeUp .45s ease both .15s;
        }

        /* Code banner */
        .code-banner {
            background: var(--amber-dim);
            border-bottom: 1px solid var(--amber-bd);
            padding: 20px 24px;
            text-align: center;
        }
        .code-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .12em;
            color: var(--amber);
            margin-bottom: 8px;
        }
        .code-value {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: .25em;
            color: var(--amber-l);
            font-variant-numeric: tabular-nums;
        }
        .code-hint {
            font-size: 11px;
            color: var(--mut);
            margin-top: 6px;
        }

        /* Details list */
        .details { padding: 8px 0; }

        .detail-row {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 24px;
            border-bottom: 1px solid var(--bd);
            transition: background .15s;
        }
        .detail-row:last-child { border-bottom: none; }
        .detail-row:hover { background: rgba(255,255,255,.02); }

        .detail-icon {
            width: 32px; height: 32px;
            border-radius: 9px;
            background: var(--s3);
            border: 1px solid var(--bd2);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .detail-icon svg { width: 14px; height: 14px; color: var(--mut2); }

        .detail-label {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--mut);
            margin-bottom: 3px;
        }
        .detail-value {
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            line-height: 1.4;
        }

        /* Status badge */
        .status-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 24px;
            border-top: 1px solid var(--bd);
        }
        .status-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--mut);
        }
        .badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 11px; font-weight: 700;
        }
        .badge-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
        .badge-pending  { background: rgba(245,158,11,.12); color: var(--amber); }
        .badge-confirmed{ background: rgba(34,197,94,.12);  color: var(--green); }
        .badge-cancelled{ background: rgba(239,68,68,.12);  color: var(--red); }
        .badge-default  { background: var(--s3);            color: var(--mut2); }

        /* Actions */
        .actions {
            width: 100%;
            max-width: 460px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 16px;
            animation: fadeUp .5s ease both .25s;
        }

        .btn-primary {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            padding: 14px;
            background: var(--amber);
            border: none;
            border-radius: 14px;
            color: #0b0f1a;
            font-size: 13px;
            font-weight: 800;
            font-family: 'DM Sans', sans-serif;
            text-decoration: none;
            cursor: pointer;
            transition: background .15s, transform .1s, box-shadow .15s;
            box-shadow: 0 4px 20px rgba(245,158,11,.2);
        }
        .btn-primary:hover {
            background: var(--amber-l);
            box-shadow: 0 6px 28px rgba(245,158,11,.3);
        }
        .btn-primary:active { transform: scale(.98); }
        .btn-primary svg { width: 15px; height: 15px; flex-shrink: 0; }

        .btn-secondary {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            padding: 13px;
            background: var(--s2);
            border: 1px solid var(--bd2);
            border-radius: 14px;
            color: var(--mut2);
            font-size: 13px;
            font-weight: 600;
            font-family: 'DM Sans', sans-serif;
            text-decoration: none;
            cursor: pointer;
            transition: border-color .15s, color .15s;
        }
        .btn-secondary:hover { border-color: var(--amber-bd); color: var(--amber); }
        .btn-secondary svg { width: 15px; height: 15px; flex-shrink: 0; }

        /* Contact */
        .contact {
            margin-top: 20px;
            text-align: center;
            animation: fadeUp .5s ease both .32s;
        }
        .contact p { font-size: 11px; color: var(--mut); margin-bottom: 4px; }
        .contact a { font-size: 14px; font-weight: 700; color: var(--amber); text-decoration: none; transition: color .15s; }
        .contact a:hover { color: var(--amber-l); }

        /* Animations */
        @keyframes fadeDown {
            from { opacity: 0; transform: translateY(-16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes popIn {
            from { opacity: 0; transform: scale(.5); }
            to   { opacity: 1; transform: scale(1); }
        }
        @keyframes pulse {
            0%   { opacity: .6; transform: scale(1); }
            100% { opacity: 0; transform: scale(1.6); }
        }
    </style>
</head>
<body>
<div class="glow"></div>

<div class="page">

    {{-- ── Success header ── --}}
    <div class="success-header">
        <div class="check-ring">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
            </svg>
        </div>
        <h1>Réservation confirmée !</h1>
        <p>Votre demande a bien été enregistrée</p>
    </div>

    {{-- ── Card ── --}}
    <div class="card">

        {{-- Code banner --}}
        <div class="code-banner">
            <div class="code-label">Code de confirmation</div>
            <div class="code-value">{{ $reservation->confirmation_code }}</div>
            <div class="code-hint">Conservez ce code pour modifier ou annuler</div>
        </div>

        {{-- Details --}}
        <div class="details">

            {{-- Restaurant --}}
            <div class="detail-row">
                <div class="detail-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016 2.993 2.993 0 0 0 2.25-1.016 3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"/>
                    </svg>
                </div>
                <div>
                    <div class="detail-label">Restaurant</div>
                    <div class="detail-value">{{ $tenant->name }}</div>
                </div>
            </div>

            {{-- Date & heure --}}
            <div class="detail-row">
                <div class="detail-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                    </svg>
                </div>
                <div>
                    <div class="detail-label">Date et heure</div>
                    <div class="detail-value">
                        {{ \Carbon\Carbon::parse($reservation->reservation_date)->translatedFormat('l d F Y') }}
                        à {{ \Carbon\Carbon::parse($reservation->reservation_time)->format('H:i') }}
                    </div>
                </div>
            </div>

            {{-- Personnes --}}
            <div class="detail-row">
                <div class="detail-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
                    </svg>
                </div>
                <div>
                    <div class="detail-label">Nombre de personnes</div>
                    <div class="detail-value">{{ $reservation->party_size }} personne{{ $reservation->party_size > 1 ? 's' : '' }}</div>
                </div>
            </div>

            {{-- Nom --}}
            <div class="detail-row">
                <div class="detail-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                    </svg>
                </div>
                <div>
                    <div class="detail-label">Réservé au nom de</div>
                    <div class="detail-value">{{ $reservation->customer_name }}</div>
                </div>
            </div>

            {{-- Demandes spéciales --}}
            @if($reservation->special_requests)
            <div class="detail-row">
                <div class="detail-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/>
                    </svg>
                </div>
                <div>
                    <div class="detail-label">Demandes spéciales</div>
                    <div class="detail-value" style="color:var(--mut2);font-weight:500;">{{ $reservation->special_requests }}</div>
                </div>
            </div>
            @endif

        </div>

        {{-- Status --}}
        <div class="status-row">
            <span class="status-label">Statut</span>
            @php
                $badgeClass = match($reservation->status) {
                    'PENDING'   => 'badge badge-pending',
                    'CONFIRMED' => 'badge badge-confirmed',
                    'CANCELLED' => 'badge badge-cancelled',
                    default     => 'badge badge-default',
                };
            @endphp
            <span class="{{ $badgeClass }}">
                <span class="badge-dot"></span>
                {{ $reservation->status_label }}
            </span>
        </div>

    </div>

    {{-- ── Actions ── --}}
    <div class="actions">
        @if($reservation->table_id)
        <a href="{{ route('menu.client', ['tenantId' => $tenant->id, 'tableId' => $reservation->table_id]) }}"
           class="btn-primary">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2"/>
            </svg>
            Voir le menu
            <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="margin-left:auto;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
            </svg>
        </a>
        @endif

        <a href="{{ route('reservation.form', $tenant->slug) }}" class="btn-secondary">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Nouvelle réservation
        </a>
    </div>

    {{-- ── Contact ── --}}
    @if($tenant->phone)
    <div class="contact">
        <p>Pour modifier ou annuler, contactez-nous</p>
        <a href="tel:{{ $tenant->phone }}">{{ $tenant->phone }}</a>
    </div>
    @endif

</div>
</body>
</html>
