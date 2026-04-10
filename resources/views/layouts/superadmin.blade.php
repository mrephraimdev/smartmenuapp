<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Super Admin') — SmartMenu</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'DM Sans', sans-serif; font-size: 15px; }
        .nav-active {
            background: rgba(167,139,250,0.15);
            color: #a78bfa;
            border-left: 3px solid #a78bfa;
        }
        .sidebar-scroll { scrollbar-width: thin; scrollbar-color: #334155 transparent; }
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        .page-content { animation: fadeUp 0.25s ease-out; }
        @keyframes fadeUp { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
    </style>
    @stack('head')
</head>
<body class="bg-gray-50 min-h-screen text-gray-800">

<div x-data="{ sidebarOpen: false }" class="flex min-h-screen">

    {{-- Mobile backdrop --}}
    <div x-show="sidebarOpen" x-cloak
         x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="fixed inset-0 bg-black/60 z-40 lg:hidden backdrop-blur-sm"></div>

    {{-- SIDEBAR --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 w-72 bg-slate-900 z-50 transform transition-transform duration-300 lg:relative lg:translate-x-0 flex flex-col shadow-2xl">

        {{-- Logo --}}
        <div class="h-16 flex items-center justify-between px-5 border-b border-slate-800 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-violet-500 flex items-center justify-center shadow-lg shadow-violet-500/30">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0H3"/>
                    </svg>
                </div>
                <div>
                    <span class="font-bold text-white text-sm">SmartMenu</span>
                    <p class="text-[10px] text-violet-400 font-semibold uppercase tracking-widest">Super Admin</p>
                </div>
            </div>
            <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto sidebar-scroll px-3 py-4 space-y-0.5">
            @php
                $saLink = fn($active) => $active
                    ? 'nav-active flex items-center gap-3 px-3 py-2.5 rounded-r-xl rounded-l-none -ml-3 pl-[calc(0.75rem+3px)] text-sm font-medium transition-all'
                    : 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800 transition-all';
            @endphp

            <a href="{{ route('superadmin.dashboard') }}"
               class="{{ $saLink(request()->routeIs('superadmin.dashboard')) }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Vue d'ensemble
            </a>

            <div class="pt-4 pb-1">
                <p class="px-3 text-[10px] font-semibold text-slate-500 uppercase tracking-widest">Gestion</p>
            </div>

            <a href="{{ route('superadmin.tenants.index') }}"
               class="{{ $saLink(request()->routeIs('superadmin.tenants*')) }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"/>
                </svg>
                Restaurants
                <x-ui.help-tooltip text="Chaque restaurant est un espace isolé dans le système" position="right" />
            </a>

            <a href="{{ route('superadmin.users.index') }}"
               class="{{ $saLink(request()->routeIs('superadmin.users*')) }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
                </svg>
                Utilisateurs
            </a>

            <a href="{{ route('superadmin.tenants.create') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-emerald-400 hover:bg-emerald-400/10 transition-all">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Nouveau Restaurant
            </a>
        </nav>

        {{-- User footer --}}
        <div class="px-3 py-3 border-t border-slate-800 flex-shrink-0">
            <div class="flex items-center gap-3 bg-slate-800/50 rounded-xl px-3 py-2">
                <div class="w-8 h-8 bg-violet-500/20 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] text-slate-500">Super Admin</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Déconnexion" class="text-slate-500 hover:text-red-400 transition-colors p-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- MAIN CONTENT --}}
    <div class="flex-1 flex flex-col min-h-screen overflow-hidden">

        {{-- Top bar --}}
        <header class="h-16 bg-white border-b border-gray-100 flex items-center justify-between px-6 sticky top-0 z-30 shadow-sm flex-shrink-0">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-800 p-1">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                    </svg>
                </button>
                <div>
                    <h1 class="text-lg font-bold text-gray-900">@yield('page-title', 'Super Admin')</h1>
                    @hasSection('breadcrumb')
                    <nav class="text-xs text-gray-400 mt-0.5">@yield('breadcrumb')</nav>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-medium bg-violet-100 text-violet-700 px-2.5 py-1 rounded-full">Super Admin</span>
            </div>
        </header>

        <main class="flex-1 p-5 lg:p-6 page-content overflow-auto">
            @yield('content')
        </main>
    </div>
</div>

<x-ui.toast />

@stack('modals')
@stack('scripts')
</body>
</html>
