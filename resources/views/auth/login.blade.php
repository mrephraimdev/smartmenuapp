<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - HorusPOS</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            background: #0B0B10;
        }

        /* ─── Layout ─────────────────────────────── */
        .split {
            display: flex;
            min-height: 100vh;
        }

        /* ─── Left: Brand Panel ───────────────────── */
        .brand {
            width: 45%;
            background: #0B0B10;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            padding: 2.75rem 3rem;
        }

        /* Thin violet edge on the right */
        .brand::after {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 1px;
            height: 100%;
            background: linear-gradient(to bottom, transparent 0%, rgba(124,58,237,0.5) 30%, rgba(124,58,237,0.5) 70%, transparent 100%);
        }

        /* Diagonal grid pattern */
        .brand-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(124,58,237,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(124,58,237,0.04) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        /* Radial glow top-left */
        .brand-glow {
            position: absolute;
            top: -120px;
            left: -120px;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(124,58,237,0.18) 0%, transparent 65%);
            pointer-events: none;
        }

        /* Radial glow bottom-right */
        .brand-glow-2 {
            position: absolute;
            bottom: -80px;
            right: 40px;
            width: 360px;
            height: 360px;
            background: radial-gradient(circle, rgba(168,85,247,0.10) 0%, transparent 65%);
            pointer-events: none;
        }

        .brand-logo {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-icon {
            width: 34px;
            height: 34px;
            background: #7c3aed;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .brand-name {
            color: #fff;
            font-size: 1.0625rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .brand-body {
            position: relative;
            z-index: 2;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 3rem 0 2rem;
        }

        .brand-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 1.5rem;
        }

        .brand-eyebrow-dot {
            width: 6px;
            height: 6px;
            background: #7c3aed;
            border-radius: 50%;
        }

        .brand-eyebrow-text {
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.35);
        }

        .brand-headline {
            font-size: clamp(2rem, 3vw, 2.875rem);
            font-weight: 700;
            line-height: 1.12;
            letter-spacing: -0.04em;
            color: #fff;
            margin-bottom: 1.5rem;
        }

        .brand-headline em {
            font-style: normal;
            color: rgba(255,255,255,0.28);
        }

        .brand-sub {
            font-size: 0.9375rem;
            line-height: 1.75;
            color: rgba(255,255,255,0.38);
            max-width: 280px;
            margin-bottom: 2.75rem;
        }

        .brand-pills {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .brand-pill {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: rgba(255,255,255,0.45);
            font-size: 0.875rem;
        }

        .brand-pill-check {
            width: 20px;
            height: 20px;
            border-radius: 6px;
            background: rgba(124,58,237,0.18);
            border: 1px solid rgba(124,58,237,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .brand-footer {
            position: relative;
            z-index: 2;
            color: rgba(255,255,255,0.15);
            font-size: 0.8125rem;
        }

        /* QR watermark */
        .qr-mark {
            position: absolute;
            bottom: -30px;
            right: -20px;
            width: 320px;
            height: 320px;
            opacity: 0.055;
            pointer-events: none;
            z-index: 1;
        }

        /* ─── Right: Form Panel ───────────────────── */
        .form-panel {
            width: 55%;
            background: #FAFAF8;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 4.5rem;
        }

        .form-inner {
            width: 100%;
            max-width: 380px;
        }

        .form-heading {
            margin-bottom: 2.5rem;
        }

        .form-heading h1 {
            font-size: 1.875rem;
            font-weight: 700;
            color: #0D0D12;
            letter-spacing: -0.035em;
            line-height: 1.2;
            margin-bottom: 0.4rem;
        }

        .form-heading p {
            font-size: 0.9375rem;
            color: #9a9a9a;
        }

        /* ─── Fields ──────────────────────────────── */
        .field {
            margin-bottom: 1.125rem;
        }

        .field label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #3d3d3d;
            margin-bottom: 0.4375rem;
            letter-spacing: 0.005em;
        }

        .field-wrap {
            position: relative;
        }

        .field-icon {
            position: absolute;
            top: 50%;
            left: 0.875rem;
            transform: translateY(-50%);
            pointer-events: none;
            color: #c5c5c5;
            display: flex;
        }

        .field input[type="text"],
        .field input[type="password"],
        .field input[type="email"] {
            width: 100%;
            padding: 0.8125rem 0.9375rem 0.8125rem 2.625rem;
            border: 1.5px solid #e8e7e3;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9375rem;
            color: #1a1a1a;
            background: #fff;
            outline: none;
            transition: border-color 0.18s, box-shadow 0.18s;
        }

        .field input:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124,58,237,0.09);
        }

        .field input.has-error {
            border-color: #f87171;
            box-shadow: 0 0 0 3px rgba(248,113,113,0.09);
        }

        .field-error {
            margin-top: 0.375rem;
            font-size: 0.8125rem;
            color: #ef4444;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .toggle-btn {
            position: absolute;
            top: 50%;
            right: 0.875rem;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            color: #c5c5c5;
            display: flex;
            align-items: center;
            transition: color 0.15s;
        }

        .toggle-btn:hover { color: #6b6b6b; }

        /* ─── Row: remember + forgot ──────────────── */
        .meta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.375rem;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 7px;
            cursor: pointer;
        }

        .remember-label input[type="checkbox"] {
            width: 15px;
            height: 15px;
            accent-color: #7c3aed;
            cursor: pointer;
        }

        .remember-label span {
            font-size: 0.875rem;
            color: #6b6b6b;
        }

        .forgot-link {
            font-size: 0.875rem;
            font-weight: 600;
            color: #7c3aed;
            text-decoration: none;
            transition: color 0.15s;
        }

        .forgot-link:hover { color: #6d28d9; }

        /* ─── Submit ──────────────────────────────── */
        .btn-submit {
            width: 100%;
            padding: 0.9375rem;
            background: #7c3aed;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
            letter-spacing: 0.005em;
            transition: background 0.18s, transform 0.15s, box-shadow 0.18s;
            box-shadow: 0 4px 16px rgba(124,58,237,0.28);
        }

        .btn-submit:hover {
            background: #6d28d9;
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(124,58,237,0.38);
        }

        .btn-submit:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(124,58,237,0.22);
        }

        /* ─── Register footer ─────────────────────── */
        .register-row {
            text-align: center;
            margin-top: 1.25rem;
            font-size: 0.875rem;
            color: #9a9a9a;
        }

        .register-row a {
            font-weight: 600;
            color: #7c3aed;
            text-decoration: none;
            transition: color 0.15s;
        }

        .register-row a:hover { color: #6d28d9; }

        /* ─── Mobile ──────────────────────────────── */
        @media (max-width: 768px) {
            .split { flex-direction: column; }

            .brand {
                width: 100%;
                padding: 2rem 1.75rem;
                min-height: auto;
            }

            .brand::after { display: none; }

            .brand-body {
                padding: 1.5rem 0;
                flex: none;
            }

            .brand-headline { font-size: 1.875rem; }
            .brand-pills { flex-direction: row; flex-wrap: wrap; }
            .brand-pill { font-size: 0.8125rem; }
            .qr-mark { width: 180px; height: 180px; opacity: 0.04; }

            .form-panel {
                width: 100%;
                padding: 2.5rem 1.5rem 3rem;
            }
        }
    </style>
</head>
<body>
<div class="split">

    {{-- ══ LEFT: BRAND ══════════════════════════════════════════════════════ --}}
    <div class="brand">
        <div class="brand-grid"></div>
        <div class="brand-glow"></div>
        <div class="brand-glow-2"></div>

        {{-- Logo --}}
        <div class="brand-logo">
            <div class="brand-icon">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/>
                </svg>
            </div>
            <span class="brand-name">HorusPOS</span>
        </div>

        {{-- Body copy --}}
        <div class="brand-body">
            <div class="brand-eyebrow">
                <span class="brand-eyebrow-dot"></span>
                <span class="brand-eyebrow-text">Plateforme de Gestion Restaurant</span>
            </div>

            <h2 class="brand-headline">
                Gérez votre<br>
                restaurant<br>
                <em>autrement.</em>
            </h2>

            <p class="brand-sub">
                Menus digitaux, commandes en temps réel et analyses avancées — tout en un seul endroit.
            </p>

            <div class="brand-pills">
                <div class="brand-pill">
                    <div class="brand-pill-check">
                        <svg width="10" height="10" viewBox="0 0 12 12" fill="none">
                            <path d="M2 6l3 3 5-5" stroke="#a78bfa" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    Menus QR personnalisés
                </div>
                <div class="brand-pill">
                    <div class="brand-pill-check">
                        <svg width="10" height="10" viewBox="0 0 12 12" fill="none">
                            <path d="M2 6l3 3 5-5" stroke="#a78bfa" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    Commandes & caisse en live
                </div>
                <div class="brand-pill">
                    <div class="brand-pill-check">
                        <svg width="10" height="10" viewBox="0 0 12 12" fill="none">
                            <path d="M2 6l3 3 5-5" stroke="#a78bfa" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    Multi-établissement
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="brand-footer">HorusPOS &copy; {{ date('Y') }}</div>

        {{-- Decorative QR watermark --}}
        <svg class="qr-mark" viewBox="0 0 200 200" fill="white" xmlns="http://www.w3.org/2000/svg">
            {{-- Corner anchors --}}
            <rect x="0"   y="0"   width="50" height="50" rx="5"/>
            <rect x="7"   y="7"   width="36" height="36" rx="3" fill="#0B0B10"/>
            <rect x="14"  y="14"  width="22" height="22" rx="2"/>

            <rect x="150" y="0"   width="50" height="50" rx="5"/>
            <rect x="157" y="7"   width="36" height="36" rx="3" fill="#0B0B10"/>
            <rect x="164" y="14"  width="22" height="22" rx="2"/>

            <rect x="0"   y="150" width="50" height="50" rx="5"/>
            <rect x="7"   y="157" width="36" height="36" rx="3" fill="#0B0B10"/>
            <rect x="14"  y="164" width="22" height="22" rx="2"/>

            {{-- Data module cells --}}
            <rect x="60"  y="0"   width="12" height="12" rx="2"/>
            <rect x="78"  y="0"   width="20" height="12" rx="2"/>
            <rect x="108" y="0"   width="12" height="12" rx="2"/>
            <rect x="126" y="0"   width="18" height="12" rx="2"/>

            <rect x="60"  y="18"  width="18" height="12" rx="2"/>
            <rect x="86"  y="18"  width="12" height="12" rx="2"/>
            <rect x="106" y="18"  width="16" height="12" rx="2"/>
            <rect x="130" y="18"  width="12" height="12" rx="2"/>
            <rect x="148" y="18"  width="10" height="12" rx="2"/>

            <rect x="60"  y="36"  width="12" height="12" rx="2"/>
            <rect x="78"  y="36"  width="28" height="12" rx="2"/>
            <rect x="116" y="36"  width="12" height="12" rx="2"/>
            <rect x="134" y="36"  width="16" height="12" rx="2"/>

            <rect x="0"   y="60"  width="12" height="12" rx="2"/>
            <rect x="20"  y="60"  width="12" height="12" rx="2"/>
            <rect x="60"  y="60"  width="12" height="22" rx="2"/>
            <rect x="78"  y="60"  width="12" height="12" rx="2"/>
            <rect x="98"  y="60"  width="26" height="12" rx="2"/>
            <rect x="134" y="60"  width="12" height="12" rx="2"/>
            <rect x="152" y="60"  width="12" height="12" rx="2"/>
            <rect x="172" y="60"  width="12" height="12" rx="2"/>
            <rect x="190" y="60"  width="10" height="12" rx="2"/>

            <rect x="0"   y="78"  width="18" height="12" rx="2"/>
            <rect x="26"  y="78"  width="12" height="12" rx="2"/>
            <rect x="62"  y="78"  width="12" height="12" rx="2"/>
            <rect x="82"  y="78"  width="18" height="12" rx="2"/>
            <rect x="108" y="78"  width="12" height="12" rx="2"/>
            <rect x="130" y="78"  width="26" height="12" rx="2"/>
            <rect x="168" y="78"  width="18" height="12" rx="2"/>

            <rect x="60"  y="96"  width="28" height="12" rx="2"/>
            <rect x="96"  y="96"  width="12" height="12" rx="2"/>
            <rect x="116" y="96"  width="12" height="12" rx="2"/>
            <rect x="136" y="96"  width="12" height="12" rx="2"/>
            <rect x="156" y="96"  width="28" height="12" rx="2"/>

            <rect x="0"   y="114" width="12" height="18" rx="2"/>
            <rect x="20"  y="114" width="18" height="12" rx="2"/>
            <rect x="58"  y="114" width="12" height="12" rx="2"/>
            <rect x="78"  y="114" width="12" height="20" rx="2"/>
            <rect x="98"  y="114" width="18" height="12" rx="2"/>
            <rect x="124" y="114" width="12" height="12" rx="2"/>
            <rect x="142" y="114" width="12" height="12" rx="2"/>
            <rect x="162" y="114" width="12" height="12" rx="2"/>
            <rect x="180" y="114" width="12" height="12" rx="2"/>

            <rect x="60"  y="132" width="18" height="12" rx="2"/>
            <rect x="86"  y="132" width="12" height="12" rx="2"/>
            <rect x="104" y="132" width="12" height="20" rx="2"/>
            <rect x="122" y="132" width="26" height="12" rx="2"/>
            <rect x="158" y="132" width="28" height="12" rx="2"/>

            <rect x="60"  y="150" width="12" height="12" rx="2"/>
            <rect x="80"  y="150" width="12" height="12" rx="2"/>
            <rect x="96"  y="150" width="12" height="22" rx="2"/>
            <rect x="116" y="150" width="12" height="22" rx="2"/>
            <rect x="136" y="150" width="18" height="12" rx="2"/>
            <rect x="162" y="150" width="12" height="12" rx="2"/>
            <rect x="180" y="150" width="12" height="12" rx="2"/>

            <rect x="60"  y="168" width="28" height="12" rx="2"/>
            <rect x="96"  y="168" width="12" height="12" rx="2"/>
            <rect x="114" y="168" width="28" height="12" rx="2"/>
            <rect x="150" y="168" width="12" height="12" rx="2"/>
            <rect x="170" y="168" width="18" height="12" rx="2"/>

            <rect x="60"  y="186" width="12" height="14" rx="2"/>
            <rect x="80"  y="186" width="22" height="14" rx="2"/>
            <rect x="112" y="186" width="12" height="14" rx="2"/>
            <rect x="134" y="186" width="18" height="14" rx="2"/>
            <rect x="160" y="186" width="12" height="14" rx="2"/>
            <rect x="180" y="186" width="20" height="14" rx="2"/>
        </svg>
    </div>

    {{-- ══ RIGHT: FORM ═══════════════════════════════════════════════════════ --}}
    <div class="form-panel">
        <div class="form-inner">

            {{-- Heading --}}
            <div class="form-heading">
                <h1>Connexion</h1>
                <p>Accédez à votre espace de gestion</p>
            </div>

            {{-- Form --}}
            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email / Identifiant --}}
                <div class="field">
                    <label for="login">Email ou identifiant</label>
                    <div class="field-wrap">
                        <span class="field-icon">
                            <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                            </svg>
                        </span>
                        <input id="login" name="login" type="text" autocomplete="username" required
                               class="{{ $errors->has('login') ? 'has-error' : '' }}"
                               placeholder="votre@email.com ou identifiant"
                               value="{{ old('login') }}">
                    </div>
                    @error('login')
                        <div class="field-error">
                            <svg width="13" height="13" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Mot de passe --}}
                <div class="field">
                    <label for="password">Mot de passe</label>
                    <div class="field-wrap" x-data="{ showPassword: false }">
                        <span class="field-icon">
                            <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </span>
                        <input id="password" name="password" :type="showPassword ? 'text' : 'password'"
                               autocomplete="current-password" required
                               class="{{ $errors->has('password') ? 'has-error' : '' }}"
                               style="padding-right: 2.75rem;"
                               placeholder="Votre mot de passe">
                        <button type="button" class="toggle-btn" @click="showPassword = !showPassword" tabindex="-1">
                            <svg x-show="!showPassword" width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPassword" x-cloak width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <div class="field-error">
                            <svg width="13" height="13" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Se souvenir / Mot de passe oublié --}}
                <div class="meta-row">
                    <label class="remember-label">
                        <input id="remember" name="remember" type="checkbox" value="1">
                        <span>Se souvenir de moi</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">
                            Mot de passe oublié ?
                        </a>
                    @endif
                </div>

                {{-- Soumettre --}}
                <button type="submit" class="btn-submit">
                    Se connecter
                </button>

                {{-- Créer un compte --}}
                @if (Route::has('register'))
                    <p class="register-row">
                        Pas encore de compte ?
                        <a href="{{ route('register') }}">Créer un compte</a>
                    </p>
                @endif

            </form>
        </div>
    </div>

</div>
</body>
</html>
