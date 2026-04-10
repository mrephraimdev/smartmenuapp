<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administration') — {{ $tenant->name ?? 'SmartMenu' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'DM Sans', sans-serif; font-size: 15px; }

        /* Active nav link — amber left border */
        .nav-active {
            background: rgba(251,191,36,0.12);
            color: #fbbf24;
            border-left: 3px solid #fbbf24;
        }
        .nav-active svg { color: #fbbf24; }

        /* Sidebar scrollbar */
        .sidebar-scroll { scrollbar-width: thin; scrollbar-color: #334155 transparent; }
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }

        /* Page transition */
        .page-content { animation: fadeUp 0.25s ease-out; }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
    @stack('head')
</head>
<body class="bg-gray-50 min-h-screen text-gray-800">

<div x-data="{ sidebarOpen: false }" class="flex min-h-screen">

    {{-- ── Mobile backdrop ─────────────────────────────── --}}
    <div x-show="sidebarOpen"
         x-cloak
         x-transition:enter="transition duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="fixed inset-0 bg-black/60 z-40 lg:hidden backdrop-blur-sm"></div>

    {{-- ── SIDEBAR ──────────────────────────────────────── --}}
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed inset-y-0 left-0 w-72 bg-slate-900 z-50 transform transition-transform duration-300 ease-in-out lg:relative lg:translate-x-0 flex flex-col shadow-2xl"
    >
        {{-- Logo --}}
        <div class="h-16 flex items-center justify-between px-5 border-b border-slate-800 flex-shrink-0">
            <a href="{{ isset($tenant) ? route('admin.dashboard', $tenant->slug) : '/' }}"
               class="flex items-center gap-3 group">
                <div class="w-9 h-9 rounded-xl bg-amber-400 flex items-center justify-center shadow-lg shadow-amber-400/30 group-hover:shadow-amber-400/50 transition-shadow">
                    <svg class="w-5 h-5 text-slate-900" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                    </svg>
                </div>
                <span class="font-bold text-white text-base tracking-tight">SmartMenu</span>
            </a>
            <button @click="sidebarOpen = false"
                    class="lg:hidden text-slate-400 hover:text-white transition-colors p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Tenant info --}}
        @isset($tenant)
        <div class="px-5 py-3 border-b border-slate-800 flex-shrink-0">
            <div class="flex items-center gap-3 bg-slate-800/60 rounded-xl px-3 py-2.5">
                @if($tenant->logo_url)
                    <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}"
                         class="w-8 h-8 rounded-lg object-cover flex-shrink-0">
                @else
                    <div class="w-8 h-8 bg-amber-400/20 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"/>
                        </svg>
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate">{{ $tenant->name }}</p>
                    <p class="text-xs text-slate-400 truncate">Mon restaurant</p>
                </div>
            </div>
        </div>
        @endisset

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto sidebar-scroll px-3 py-4 space-y-0.5">
            @isset($tenant)
            @php
                $slug   = $tenant->slug;
                $user   = auth()->user();
                $isAdmin    = $user->hasRole(\App\Enums\UserRole::ADMIN) || $user->hasRole(\App\Enums\UserRole::SUPER_ADMIN);
                $isCaissier = $user->hasRole(\App\Enums\UserRole::CAISSIER);
                $isChef     = $user->hasRole(\App\Enums\UserRole::CHEF);
                $isServeur  = $user->hasRole(\App\Enums\UserRole::SERVEUR);

                $navLink = fn($active) => $active
                    ? 'nav-active flex items-center gap-3 px-3 py-2.5 rounded-r-xl rounded-l-none -ml-3 pl-[calc(0.75rem+3px)] text-sm font-medium transition-all'
                    : 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800 transition-all';
            @endphp

            {{-- ─ Tableau de bord ─ --}}
            @if($isAdmin)
            <a href="{{ route('admin.dashboard', $slug) }}"
               class="{{ $navLink(request()->routeIs('admin.dashboard')) }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Tableau de bord
            </a>
            @endif

            {{-- ─ MON MENU ─ --}}
            @if($isAdmin || $isChef)
            <div class="pt-4 pb-1">
                <p class="px-3 text-[10px] font-semibold text-slate-500 uppercase tracking-widest">Mon Menu</p>
            </div>

            <a href="{{ route('admin.menus', $slug) }}"
               class="{{ $navLink(request()->routeIs('admin.menus*') || request()->routeIs('admin.categories*') || request()->routeIs('admin.dishes*')) }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/>
                </svg>
                Menus &amp; Plats
                @if($isChef)
                    <span class="ml-auto text-[10px] text-slate-500 bg-slate-800 px-1.5 py-0.5 rounded">lecture</span>
                @endif
            </a>

            @if($isAdmin)
            <a href="{{ route('admin.menu.import', $slug) }}"
               class="{{ $navLink(request()->routeIs('admin.menu.import*')) }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
                </svg>
                Import Excel
            </a>
            @endif

            @endif

            {{-- ─ COMMANDES ─ --}}
            <div class="pt-4 pb-1">
                <p class="px-3 text-[10px] font-semibold text-slate-500 uppercase tracking-widest">Commandes</p>
            </div>

            {{-- Prise de commande au comptoir --}}
            <a href="{{ route('admin.comptoir.index', $slug) }}"
               class="{{ $navLink(request()->routeIs('admin.comptoir*')) }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z"/>
                </svg>
                Prise de commande
            </a>

            {{-- Suivi des commandes --}}
            <a href="{{ route('admin.suivi.index', $slug) }}"
               class="{{ $navLink(request()->routeIs('admin.suivi*')) }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5"/>
                </svg>
                Suivi commandes
            </a>

            {{-- Historique commandes --}}
            <a href="{{ route('admin.orders.index', $slug) }}"
               class="{{ $navLink(request()->routeIs('admin.orders*')) }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                </svg>
                Historique
            </a>

            @if($isAdmin || $isChef || $isServeur || $isCaissier)
            <a href="{{ url('/kds/' . $slug) }}"
               class="{{ $navLink(false) }} group">
                <svg class="w-5 h-5 flex-shrink-0 text-orange-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z"/>
                </svg>
                <span class="text-orange-400">KDS Cuisine</span>
                <x-ui.help-tooltip text="Écran cuisine pour voir les commandes en temps réel" position="right" />
                <span class="ml-auto text-[10px] font-bold bg-orange-500 text-white px-1.5 py-0.5 rounded-full animate-pulse">LIVE</span>
            </a>
            @endif

            {{-- ─ PAIEMENTS ─ --}}
            @if($isAdmin || $isCaissier)
            <div class="pt-4 pb-1">
                <p class="px-3 text-[10px] font-semibold text-slate-500 uppercase tracking-widest">Paiements</p>
            </div>

            <a href="{{ route('admin.payments.index', $slug) }}"
               class="{{ $navLink(request()->routeIs('admin.payments*')) }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
                Paiements
            </a>
            @endif

            {{-- ─ RESTAURANT ─ --}}
            @if($isAdmin || $isCaissier || $isServeur)
            <div class="pt-4 pb-1">
                <p class="px-3 text-[10px] font-semibold text-slate-500 uppercase tracking-widest">Restaurant</p>
            </div>

            <a href="{{ route('admin.tables.index', $slug) }}"
               class="{{ $navLink(request()->routeIs('admin.tables*')) }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h.008v.008h-.008V8.25Zm-17.25 0h.008v.008H3.375V8.25Z"/>
                </svg>
                Gestion Tables
            </a>

            @if($isAdmin)
            <a href="{{ route('admin.qrcodes.index', $slug) }}"
               class="{{ $navLink(request()->routeIs('admin.qrcodes*')) }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z"/>
                    <path d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z"/>
                </svg>
                QR Codes
                <x-ui.help-tooltip text="Codes scannable par les clients pour accéder à votre menu" position="right" />
            </a>
            @endif
            @endif

            {{-- ─ SERVICES ─ --}}
            @if($isAdmin)
            <div class="pt-4 pb-1">
                <p class="px-3 text-[10px] font-semibold text-slate-500 uppercase tracking-widest">Services</p>
            </div>

            <a href="{{ route('admin.reservations.index', $slug) }}"
               class="{{ $navLink(request()->routeIs('admin.reservations*')) }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                </svg>
                Réservations
            </a>

            <a href="{{ route('admin.reviews.index', $slug) }}"
               class="{{ $navLink(request()->routeIs('admin.reviews*')) }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/>
                </svg>
                Avis Clients
            </a>
            @endif

            {{-- ─ ANALYSE ─ --}}
            @if($isAdmin || $isCaissier)
            <div class="pt-4 pb-1">
                <p class="px-3 text-[10px] font-semibold text-slate-500 uppercase tracking-widest">Analyse</p>
            </div>

            @if($isAdmin)
            <a href="{{ route('admin.statistics', $slug) }}"
               class="{{ $navLink(request()->routeIs('admin.statistics*')) }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>
                </svg>
                Statistiques
            </a>
            @endif

            <a href="{{ route('admin.print.daily-report', $slug) }}" target="_blank"
               class="{{ $navLink(false) }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/>
                </svg>
                Rapport du jour
            </a>
            @endif

            {{-- ─ PARAMÈTRES ─ --}}
            @if($isAdmin)
            <div class="pt-4 pb-1">
                <p class="px-3 text-[10px] font-semibold text-slate-500 uppercase tracking-widest">Paramètres</p>
            </div>

            <a href="{{ route('admin.staff.index', $slug) }}"
               class="{{ $navLink(request()->routeIs('admin.staff*')) }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
                </svg>
                Personnel
            </a>

            @endif

            {{-- ─ LIENS PUBLICS ─ --}}
            @if($isAdmin)
            <div class="pt-4 pb-1">
                <p class="px-3 text-[10px] font-semibold text-slate-500 uppercase tracking-widest">Liens publics</p>
            </div>

            <a href="{{ url('/menu/' . $tenant->id . '/A1') }}" target="_blank"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-emerald-400 hover:bg-emerald-400/10 transition-all">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                </svg>
                Voir mon menu
                <svg class="w-3.5 h-3.5 ml-auto" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                </svg>
            </a>
            @endif
            @endisset
        </nav>

        {{-- User footer --}}
        <div class="px-3 py-3 border-t border-slate-800 flex-shrink-0 space-y-0.5">
            @if(auth()->user()?->hasRole(\App\Enums\UserRole::SUPER_ADMIN))
            <a href="{{ route('superadmin.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-violet-400 hover:bg-violet-400/10 transition-all">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0H3"/>
                </svg>
                <x-ui.help-tooltip text="Espace d'administration globale de toute la plateforme" position="right" />
                Super Admin
            </a>
            @endif

            <div class="flex items-center gap-3 px-3 py-2 rounded-xl bg-slate-800/50">
                <div class="w-8 h-8 bg-amber-400/20 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] text-slate-500 truncate">{{ Auth::user()->role }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            title="Déconnexion"
                            class="text-slate-500 hover:text-red-400 transition-colors p-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ── MAIN CONTENT ─────────────────────────────────── --}}
    <div class="flex-1 flex flex-col min-h-screen overflow-hidden">

        {{-- Top bar --}}
        <header class="h-16 bg-white border-b border-gray-100 flex items-center justify-between px-6 sticky top-0 z-30 flex-shrink-0 shadow-sm">
            <div class="flex items-center gap-4">
                {{-- Hamburger --}}
                <button @click="sidebarOpen = true"
                        class="lg:hidden text-gray-500 hover:text-gray-800 transition-colors p-1">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                    </svg>
                </button>

                {{-- Page title & breadcrumb --}}
                <div>
                    <h1 class="text-lg font-bold text-gray-900 leading-tight">@yield('page-title', 'Dashboard')</h1>
                    @hasSection('breadcrumb')
                    <nav class="text-xs text-gray-400 mt-0.5">@yield('breadcrumb')</nav>
                    @endif
                </div>
            </div>

            {{-- Right zone --}}
            <div class="flex items-center gap-3">
                @isset($tenant)
                {{-- ── Alerte sonore nouvelles commandes ── --}}
                <div x-data="orderNotif('{{ $tenant->slug }}', {{ auth()->id() }})" x-init="init()" class="flex items-center gap-1.5">

                    {{-- Bouton son --}}
                    <button
                        @click="toggle()"
                        :class="enabled ? 'text-amber-600 bg-amber-50 border-amber-300' : 'text-gray-400 bg-gray-50 border-gray-200'"
                        class="relative flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1.5 rounded-lg border transition-all"
                        :title="enabled ? 'Alarme activée — cliquer pour désactiver' : 'Alarme désactivée — cliquer pour activer'"
                    >
                        {{-- Bell ON --}}
                        <svg x-show="enabled" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
                        </svg>
                        {{-- Bell OFF --}}
                        <svg x-show="!enabled" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.143 17.082a24.248 24.248 0 0 0 3.844.148m-3.844-.148a23.856 23.856 0 0 1-5.455-1.31 8.964 8.964 0 0 0 2.3-5.542m3.155 6.852a3 3 0 0 0 5.667 1.97m1.965-2.277L21 21m-4.225-4.225a23.81 23.81 0 0 0 .56-2.700 8.956 8.956 0 0 0 2.167-5.755V9a6 6 0 0 0-9.134-5.11"/>
                        </svg>
                        <span class="hidden sm:inline" x-text="enabled ? 'Son ON' : 'Son OFF'"></span>
                        {{-- Badge commandes en attente --}}
                        <span x-show="pendingCount > 0"
                              x-text="pendingCount > 9 ? '9+' : pendingCount"
                              class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-[9px] font-bold rounded-full min-w-[16px] h-4 flex items-center justify-center px-0.5 animate-pulse"></span>
                    </button>

                    {{-- Bouton notification push (message) --}}
                    <button
                        @click="toggleNotif()"
                        :class="notifEnabled ? 'text-violet-600 bg-violet-50 border-violet-300' : 'text-gray-400 bg-gray-50 border-gray-200'"
                        class="flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1.5 rounded-lg border transition-all"
                        :title="notifEnabled ? 'Notifications activées — cliquer pour désactiver' : 'Activer les notifications sur cet écran'"
                    >
                        {{-- Chat bubble icon --}}
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" x-show="false"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/>
                        </svg>
                        <span class="hidden sm:inline" x-text="notifEnabled ? 'Notif ON' : 'Notif'"></span>
                    </button>
                </div>

                <a href="{{ url('/menu/' . $tenant->id . '/A1') }}" target="_blank"
                   class="hidden sm:flex items-center gap-1.5 text-xs font-medium text-gray-500 hover:text-amber-600 bg-gray-50 hover:bg-amber-50 px-3 py-1.5 rounded-lg border border-gray-200 hover:border-amber-200 transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </svg>
                    Voir le menu
                </a>
                @endisset
                <span class="text-sm font-medium text-gray-700 hidden sm:block">{{ Auth::user()->name }}</span>
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 p-5 lg:p-6 page-content overflow-auto">
            @yield('content')
        </main>
    </div>
</div>

{{-- Toast system --}}
<x-ui.toast />

@stack('modals')
@stack('scripts')

{{-- ═══════════════════════════════════════════════
     ALERTE SONORE — Nouvelles commandes
     Actif uniquement sur les pages admin avec tenant
═══════════════════════════════════════════════ --}}
@isset($tenant)
<script>
// ── Alarme sonore puissante ─────────────────────────────────────────────────
class OrderAlarm {
    constructor() { this.ctx = null; this.comp = null; this.playing = false; }

    _init() {
        if (!this.ctx) {
            this.ctx  = new (window.AudioContext || window.webkitAudioContext)();
            // Compresseur → pousse le volume au maximum autorisé par le navigateur
            this.comp = this.ctx.createDynamicsCompressor();
            this.comp.threshold.value = -6;
            this.comp.knee.value      = 0;
            this.comp.ratio.value     = 20;
            this.comp.attack.value    = 0.001;
            this.comp.release.value   = 0.05;
            this.comp.connect(this.ctx.destination);
        }
        if (this.ctx.state === 'suspended') this.ctx.resume();
    }

    _tone(freq, start, dur) {
        const osc  = this.ctx.createOscillator();
        const gain = this.ctx.createGain();
        osc.type = 'square'; // onde carrée = son perçant et fort
        osc.frequency.value = freq;
        gain.gain.setValueAtTime(1.0, start);
        gain.gain.exponentialRampToValueAtTime(0.001, start + dur);
        osc.connect(gain);
        gain.connect(this.comp);
        osc.start(start);
        osc.stop(start + dur);
    }

    // Pattern d'alarme alternant 1400Hz/700Hz — 5 sonneries de 4 bips
    play(rings = 5) {
        this._init();
        if (this.playing) return;
        this.playing = true;
        const now = this.ctx.currentTime;
        for (let r = 0; r < rings; r++) {
            const base = now + r * 0.75;
            this._tone(1400, base + 0.00, 0.10);
            this._tone( 700, base + 0.15, 0.10);
            this._tone(1400, base + 0.30, 0.10);
            this._tone( 700, base + 0.45, 0.10);
        }
        setTimeout(() => { this.playing = false; }, rings * 750 + 300);
    }

    // Bip test discret à l'activation
    test() {
        this._init();
        const now = this.ctx.currentTime;
        this._tone(880, now, 0.12);
        this._tone(1100, now + 0.15, 0.12);
    }
}

window._orderAlarm = new OrderAlarm();
// Déverrouillez le contexte audio au premier clic (obligation navigateur)
document.addEventListener('click', () => window._orderAlarm._init(), { once: true });

// ── Notifications Push navigateur ───────────────────────────────────────────
async function _requestNotifPermission() {
    if (!('Notification' in window)) return;
    if (Notification.permission === 'default') {
        await Notification.requestPermission();
    }
}

function _showPushNotif(pendingCount) {
    if (!('Notification' in window) || Notification.permission !== 'granted') return;
    const n = new Notification('🛎️ Nouvelle commande !', {
        body: `${pendingCount} commande(s) en attente — cliquez pour voir`,
        icon: '/favicon.ico',
        tag:  'new-order',        // remplace la notif précédente au lieu d'empiler
        requireInteraction: true,  // reste jusqu'à ce que le gérant clique
    });
    n.onclick = () => { window.focus(); n.close(); };
}

// ── Composant Alpine ─────────────────────────────────────────────────────────
function orderNotif(tenantSlug, userId) {
    return {
        enabled: false,
        notifEnabled: false,
        pendingCount: 0,
        lastOrderId: 0,
        pollInterval: null,
        _keyEnabled()  { return `sm_sound_${userId}`; },
        _keyNotif()    { return `sm_notif_${userId}`; },
        _keyLastId()   { return `sm_last_order_${tenantSlug}`; },

        async init() {
            this.enabled      = localStorage.getItem(this._keyEnabled()) !== 'false';
            this.notifEnabled = localStorage.getItem(this._keyNotif()) === 'true';
            this.lastOrderId  = parseInt(localStorage.getItem(this._keyLastId()) || '0', 10);

            if (this.notifEnabled) await _requestNotifPermission();

            // Premier poll silencieux pour initialiser lastOrderId
            this._fetchOrders(true);

            // Polling toutes les 5 secondes (au lieu de 20)
            this.pollInterval = setInterval(() => this._fetchOrders(false), 5000);
        },

        toggle() {
            this.enabled = !this.enabled;
            localStorage.setItem(this._keyEnabled(), this.enabled ? 'true' : 'false');
            if (this.enabled) window._orderAlarm.test();
        },

        async toggleNotif() {
            if (!this.notifEnabled) {
                await _requestNotifPermission();
                this.notifEnabled = Notification.permission === 'granted';
            } else {
                this.notifEnabled = false;
            }
            localStorage.setItem(this._keyNotif(), this.notifEnabled ? 'true' : 'false');
        },

        async _fetchOrders(silent) {
            try {
                const res  = await fetch(`/admin/${tenantSlug}/orders-notify`, { credentials: 'same-origin' });
                if (!res.ok) return;
                const data = await res.json();

                const newLatestId = data.latest_id ?? 0;
                this.pendingCount = data.pending_count ?? 0;

                if (!silent && newLatestId > this.lastOrderId && this.lastOrderId > 0) {
                    if (this.enabled)      window._orderAlarm.play(5);
                    if (this.notifEnabled) _showPushNotif(this.pendingCount);
                }

                if (newLatestId > this.lastOrderId) {
                    this.lastOrderId = newLatestId;
                    localStorage.setItem(this._keyLastId(), String(newLatestId));
                }
            } catch (e) { /* réseau — ignoré */ }
        },
    };
}
</script>
@endisset
</body>
</html>
