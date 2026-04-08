<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administration') - {{ $tenant->name ?? 'SmartMenu' }}</title>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div x-data="{ sidebarOpen: false }" class="flex min-h-screen">
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="sidebarOpen"
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 bg-black/50 z-40 lg:hidden"></div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed inset-y-0 left-0 w-64 bg-white shadow-xl z-50 transform transition-transform duration-300 lg:relative lg:translate-x-0 flex flex-col">
            <!-- Logo -->
            <div class="h-16 flex items-center justify-between px-6 border-b border-gray-100 flex-shrink-0">
                <a href="{{ isset($tenant) ? route('admin.dashboard', $tenant->slug) : '/' }}" class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                        @svg('heroicon-s-squares-2x2', 'w-6 h-6 text-white')
                    </div>
                    <span class="font-bold text-gray-800">SmartMenu</span>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-gray-500 hover:text-gray-700">
                    @svg('heroicon-o-x-mark', 'w-6 h-6')
                </button>
            </div>

            <!-- Tenant Info -->
            @isset($tenant)
            <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-100 flex-shrink-0">
                <div class="flex items-center space-x-3">
                    @if($tenant->logo_url)
                        <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}" class="w-10 h-10 rounded-lg object-cover">
                    @else
                        <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                            @svg('heroicon-o-building-storefront', 'w-5 h-5 text-indigo-600')
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ $tenant->name }}</p>
                        <p class="text-xs text-gray-500">{{ $tenant->slug }}</p>
                    </div>
                </div>
            </div>
            @endisset

            <!-- Navigation -->
            <nav class="px-4 py-4 space-y-1 flex-1 overflow-y-auto">
                @isset($tenant)
                @php
                    $user = auth()->user();
                    $isAdmin = $user->hasRole(\App\Enums\UserRole::ADMIN) || $user->hasRole(\App\Enums\UserRole::SUPER_ADMIN);
                    $isCaissier = $user->hasRole(\App\Enums\UserRole::CAISSIER);
                    $isChef = $user->hasRole(\App\Enums\UserRole::CHEF);
                    $isServeur = $user->hasRole(\App\Enums\UserRole::SERVEUR);
                @endphp

                <!-- Dashboard - ADMIN only -->
                @if($isAdmin)
                <a href="{{ route('admin.dashboard', $tenant->slug) }}"
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50' }}">
                    @svg('heroicon-o-chart-bar-square', 'w-5 h-5')
                    <span class="font-medium">Dashboard</span>
                </a>
                @endif

                <!-- Menu Section - ADMIN & CHEF (view) -->
                @if($isAdmin || $isChef)
                <div class="pt-4 mt-2 border-t border-gray-100">
                    <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Menu</p>
                </div>

                <a href="{{ route('admin.menus', $tenant->slug) }}"
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.menus*') || request()->routeIs('admin.categories*') || request()->routeIs('admin.dishes*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50' }}">
                    @svg('heroicon-o-clipboard-document-list', 'w-5 h-5')
                    <span class="font-medium">Menus & Plats</span>
                    @if($isChef)
                    <span class="ml-auto text-xs text-gray-400">(lecture)</span>
                    @endif
                </a>
                @endif

                <!-- Commandes Section - Everyone except CLIENT -->
                <div class="pt-4 mt-2 border-t border-gray-100">
                    <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Commandes</p>
                </div>

                <a href="{{ route('admin.orders.index', $tenant->slug) }}"
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.orders*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50' }}">
                    @svg('heroicon-o-shopping-cart', 'w-5 h-5')
                    <span class="font-medium">Commandes</span>
                </a>

                <!-- KDS - ADMIN, CHEF, SERVEUR, CAISSIER -->
                @if($isAdmin || $isChef || $isServeur || $isCaissier)
                <a href="{{ url('/kds/' . $tenant->slug) }}"
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.kds*') ? 'bg-orange-50 text-orange-700' : 'text-gray-600 hover:bg-orange-50 hover:text-orange-700' }}">
                    @svg('heroicon-o-fire', 'w-5 h-5')
                    <span class="font-medium">KDS Cuisine</span>
                    <span class="ml-auto bg-orange-100 text-orange-700 text-xs px-2 py-0.5 rounded-full">Live</span>
                </a>
                @endif

                <!-- Caisse Section - ADMIN & CAISSIER -->
                @if($isAdmin || $isCaissier)
                <div class="pt-4 mt-2 border-t border-gray-100">
                    <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Caisse</p>
                </div>

                <a href="{{ route('admin.pos.index', $tenant->slug) }}"
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.pos.index') ? 'bg-green-50 text-green-700' : 'text-gray-600 hover:bg-green-50 hover:text-green-700' }}">
                    @svg('heroicon-o-banknotes', 'w-5 h-5')
                    <span class="font-medium">Point de Vente</span>
                </a>

                <a href="{{ route('admin.pos.sessions', $tenant->slug) }}"
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.pos.sessions*') || request()->routeIs('admin.pos.z-report*') || request()->routeIs('admin.pos.x-report*') ? 'bg-green-50 text-green-700' : 'text-gray-600 hover:bg-gray-50' }}">
                    @svg('heroicon-o-rectangle-stack', 'w-5 h-5')
                    <span class="font-medium">Sessions Caisse</span>
                </a>

                <a href="{{ route('admin.payments.index', $tenant->slug) }}"
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.payments*') ? 'bg-green-50 text-green-700' : 'text-gray-600 hover:bg-gray-50' }}">
                    @svg('heroicon-o-currency-dollar', 'w-5 h-5')
                    <span class="font-medium">Paiements</span>
                </a>
                @endif

                <!-- Tables Section - ADMIN, CAISSIER, SERVEUR -->
                @if($isAdmin || $isCaissier || $isServeur)
                <div class="pt-4 mt-2 border-t border-gray-100">
                    <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Tables</p>
                </div>

                <a href="{{ route('admin.tables.index', $tenant->slug) }}"
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.tables*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50' }}">
                    @svg('heroicon-o-table-cells', 'w-5 h-5')
                    <span class="font-medium">Gestion Tables</span>
                </a>

                @if($isAdmin)
                <a href="{{ route('admin.qrcodes.index', $tenant->slug) }}"
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.qrcodes*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50' }}">
                    @svg('heroicon-o-qr-code', 'w-5 h-5')
                    <span class="font-medium">QR Codes</span>
                </a>
                @endif
                @endif

                <!-- Services Section - ADMIN only -->
                @if($isAdmin)
                <div class="pt-4 mt-2 border-t border-gray-100">
                    <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Services</p>
                </div>

                <a href="{{ route('admin.reservations.index', $tenant->slug) }}"
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.reservations*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50' }}">
                    @svg('heroicon-o-calendar-days', 'w-5 h-5')
                    <span class="font-medium">Reservations</span>
                </a>

                <a href="{{ route('admin.reviews.index', $tenant->slug) }}"
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.reviews*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50' }}">
                    @svg('heroicon-o-star', 'w-5 h-5')
                    <span class="font-medium">Avis Clients</span>
                </a>
                @endif

                <!-- Rapports Section - ADMIN (full) & CAISSIER (partial) -->
                @if($isAdmin || $isCaissier)
                <div class="pt-4 mt-2 border-t border-gray-100">
                    <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Rapports</p>
                </div>

                @if($isAdmin)
                <a href="{{ route('admin.statistics', $tenant->slug) }}"
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.statistics*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50' }}">
                    @svg('heroicon-o-chart-pie', 'w-5 h-5')
                    <span class="font-medium">Statistiques</span>
                </a>

                <a href="{{ route('admin.exports.orders', $tenant->slug) }}"
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.exports*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50' }}">
                    @svg('heroicon-o-arrow-down-tray', 'w-5 h-5')
                    <span class="font-medium">Exports CSV</span>
                </a>
                @endif

                <a href="{{ route('admin.print.daily-report', $tenant->slug) }}" target="_blank"
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all text-gray-600 hover:bg-gray-50">
                    @svg('heroicon-o-printer', 'w-5 h-5')
                    <span class="font-medium">Rapport du jour</span>
                </a>

                @if($isAdmin)
                <a href="{{ route('admin.audit-logs.index', $tenant->slug) }}"
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.audit-logs*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50' }}">
                    @svg('heroicon-o-clipboard-document-check', 'w-5 h-5')
                    <span class="font-medium">Audit Logs</span>
                </a>
                @endif
                @endif

                <!-- Parametres Section - ADMIN only -->
                @if($isAdmin)
                <div class="pt-4 mt-2 border-t border-gray-100">
                    <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Parametres</p>
                </div>

                <a href="{{ route('admin.staff.index', $tenant->slug) }}"
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.staff*') ? 'bg-orange-50 text-orange-700' : 'text-gray-600 hover:bg-gray-50' }}">
                    @svg('heroicon-o-users', 'w-5 h-5')
                    <span class="font-medium">Personnel</span>
                </a>

                <a href="{{ route('admin.themes.index', $tenant->slug) }}"
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.themes*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50' }}">
                    @svg('heroicon-o-paint-brush', 'w-5 h-5')
                    <span class="font-medium">Themes</span>
                </a>

                <!-- Liens Publics Section -->
                <div class="pt-4 mt-2 border-t border-gray-100">
                    <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Liens Publics</p>
                </div>

                <a href="{{ url('/menu/' . $tenant->id . '/A1') }}" target="_blank"
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all text-gray-600 hover:bg-green-50 hover:text-green-700">
                    @svg('heroicon-o-eye', 'w-5 h-5')
                    <span class="font-medium">Menu Client</span>
                    @svg('heroicon-o-arrow-top-right-on-square', 'w-4 h-4 ml-auto')
                </a>

                <a href="{{ route('reservation.form', $tenant->slug) }}" target="_blank"
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all text-gray-600 hover:bg-blue-50 hover:text-blue-700">
                    @svg('heroicon-o-calendar', 'w-5 h-5')
                    <span class="font-medium">Page Reservation</span>
                    @svg('heroicon-o-arrow-top-right-on-square', 'w-4 h-4 ml-auto')
                </a>

                <a href="{{ route('reviews.public', $tenant->slug) }}" target="_blank"
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all text-gray-600 hover:bg-yellow-50 hover:text-yellow-700">
                    @svg('heroicon-o-chat-bubble-left-ellipsis', 'w-5 h-5')
                    <span class="font-medium">Avis Publics</span>
                    @svg('heroicon-o-arrow-top-right-on-square', 'w-4 h-4 ml-auto')
                </a>
                @endif
                @endisset
            </nav>

            <!-- Footer -->
            <div class="px-4 py-4 border-t border-gray-100 flex-shrink-0 space-y-1">
                @if(auth()->user()?->hasRole(\App\Enums\UserRole::SUPER_ADMIN))
                <a href="{{ route('superadmin.dashboard') }}"
                   class="flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-purple-50 hover:text-purple-700 transition-all">
                    @svg('heroicon-o-building-office-2', 'w-5 h-5')
                    <span class="font-medium">Super Admin</span>
                </a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit"
                       class="flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-red-50 hover:text-red-700 transition-all w-full">
                        @svg('heroicon-o-arrow-right-on-rectangle', 'w-5 h-5')
                        <span class="font-medium">Déconnexion</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-h-screen lg:ml-0">
            <!-- Top Header -->
            <header class="h-16 bg-white border-b border-gray-100 flex items-center justify-between px-6 sticky top-0 z-30 flex-shrink-0">
                <div class="flex items-center space-x-4">
                    <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700">
                        @svg('heroicon-o-bars-3', 'w-6 h-6')
                    </button>
                    <div>
                        <h1 class="text-lg font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h1>
                        @hasSection('breadcrumb')
                            <nav class="text-sm text-gray-500">@yield('breadcrumb')</nav>
                        @endif
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    @auth
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                            @svg('heroicon-o-user', 'w-4 h-4 text-indigo-600')
                        </div>
                        <span class="text-sm font-medium text-gray-700 hidden sm:block">{{ Auth::user()->name }}</span>
                    </div>
                    @endauth
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-6">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('modals')
    @stack('scripts')
</body>
</html>
