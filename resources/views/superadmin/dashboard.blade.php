<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartMenu - Dashboard Super Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }

        .gradient-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stat-card {
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .animate-fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .hover-scale {
            transition: all 0.2s ease;
        }

        .hover-scale:hover {
            transform: scale(1.02);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen" x-data="{ mobileMenu: false }">
    <!-- Header -->
    <header class="gradient-header text-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <!-- Logo et titre -->
                <div class="flex items-center space-x-4">
                    <img src="{{ asset('images/SmartMenu.png') }}" alt="SmartMenu" class="h-10 w-auto">
                    <div>
                        <h1 class="text-xl font-bold">SmartMenu</h1>
                        <p class="text-white/70 text-xs">Dashboard Super Admin</p>
                    </div>
                </div>

                <!-- Navigation Desktop -->
                <nav class="hidden md:flex items-center space-x-2">
                    <a href="{{ route('superadmin.tenants.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded-lg text-white/80 hover:bg-white/10 hover:text-white transition">
                        <x-heroicon-o-building-storefront class="w-5 h-5" />
                        <span>Restaurants</span>
                    </a>
                    <a href="{{ route('superadmin.users.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded-lg text-white/80 hover:bg-white/10 hover:text-white transition">
                        <x-heroicon-o-users class="w-5 h-5" />
                        <span>Utilisateurs</span>
                    </a>
                    <a href="{{ route('superadmin.tenants.create') }}" class="flex items-center space-x-2 px-4 py-2 bg-white/20 rounded-lg text-white hover:bg-white/30 transition">
                        <x-heroicon-o-plus class="w-5 h-5" />
                        <span>Nouveau</span>
                    </a>

                    <!-- Profil et déconnexion -->
                    <div class="ml-4 pl-4 border-l border-white/20 flex items-center space-x-3">
                        <div class="text-right hidden lg:block">
                            <p class="text-sm font-medium">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-white/70">Super Admin</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="flex items-center px-3 py-2 rounded-lg bg-white/10 hover:bg-white/20 transition" title="Déconnexion">
                                <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5" />
                            </button>
                        </form>
                    </div>
                </nav>

                <!-- Mobile menu button -->
                <button @click="mobileMenu = !mobileMenu" class="md:hidden text-white p-2">
                    <x-heroicon-o-bars-3 x-show="!mobileMenu" class="w-6 h-6" />
                    <x-heroicon-o-x-mark x-show="mobileMenu" x-cloak class="w-6 h-6" />
                </button>
            </div>

            <!-- Mobile Navigation -->
            <div x-show="mobileMenu" x-cloak x-transition class="md:hidden mt-4 pb-4 space-y-2 border-t border-white/20 pt-4">
                <a href="{{ route('superadmin.tenants.index') }}" class="flex items-center space-x-2 px-4 py-3 rounded-lg text-white/80 hover:bg-white/10">
                    <x-heroicon-o-building-storefront class="w-5 h-5" />
                    <span>Restaurants</span>
                </a>
                <a href="{{ route('superadmin.users.index') }}" class="flex items-center space-x-2 px-4 py-3 rounded-lg text-white/80 hover:bg-white/10">
                    <x-heroicon-o-users class="w-5 h-5" />
                    <span>Utilisateurs</span>
                </a>
                <a href="{{ route('superadmin.tenants.create') }}" class="flex items-center space-x-2 px-4 py-3 bg-white/20 rounded-lg text-white">
                    <x-heroicon-o-plus class="w-5 h-5" />
                    <span>Nouveau Restaurant</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="block">
                    @csrf
                    <button type="submit" class="w-full flex items-center space-x-2 px-4 py-3 rounded-lg text-red-200 hover:bg-white/10">
                        <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5" />
                        <span>Déconnexion</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Statistiques globales -->
        <div class="grid grid-cols-2 gap-4 md:gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-lg p-6 stat-card animate-fade-in">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-blue-100 rounded-xl p-3">
                        <x-heroicon-o-building-storefront class="w-6 h-6 text-blue-600" />
                    </div>
                    <a href="{{ route('superadmin.tenants.create') }}" class="text-blue-600 hover:text-blue-800">
                        <x-heroicon-o-plus-circle class="w-6 h-6" />
                    </a>
                </div>
                <p class="text-gray-500 text-sm mb-1">Restaurants</p>
                <p class="text-3xl font-bold text-gray-800">{{ $stats['total_tenants'] }}</p>
                @if($stats['active_tenants'] > 0)
                <p class="text-xs text-green-600 mt-1">{{ $stats['active_tenants'] }} actif(s)</p>
                @endif
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6 stat-card animate-fade-in" style="animation-delay: 0.1s">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-green-100 rounded-xl p-3">
                        <x-heroicon-o-users class="w-6 h-6 text-green-600" />
                    </div>
                    <a href="{{ route('superadmin.users.create') }}" class="text-green-600 hover:text-green-800">
                        <x-heroicon-o-plus-circle class="w-6 h-6" />
                    </a>
                </div>
                <p class="text-gray-500 text-sm mb-1">Utilisateurs</p>
                <p class="text-3xl font-bold text-gray-800">{{ $stats['total_users'] }}</p>
            </div>
        </div>

        <!-- Grille principale -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Restaurants récents -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                            <x-heroicon-o-building-storefront class="w-5 h-5 mr-2 text-blue-600" />
                            Restaurants Récents
                        </h2>
                        <a href="{{ route('superadmin.tenants.create') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center font-medium">
                            <x-heroicon-o-plus class="w-4 h-4 mr-1" />
                            Ajouter
                        </a>
                    </div>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse($recentTenants as $tenant)
                    <div class="p-4 hover:bg-gray-50 transition">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white font-bold">
                                    {{ strtoupper(substr($tenant->name, 0, 1)) }}
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900">{{ $tenant->name }}</h3>
                                    <p class="text-xs text-gray-500">{{ $tenant->slug }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg bg-indigo-100 text-indigo-700 hover:bg-indigo-200 transition">
                                <x-heroicon-o-squares-2x2 class="w-3.5 h-3.5 mr-1" />
                                Admin
                            </a>
                            <a href="{{ route('kds', $tenant->slug) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg bg-orange-100 text-orange-700 hover:bg-orange-200 transition">
                                <x-heroicon-o-fire class="w-3.5 h-3.5 mr-1" />
                                KDS
                            </a>
                            <a href="{{ route('admin.pos.index', $tenant->slug) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 hover:bg-green-200 transition">
                                <x-heroicon-o-banknotes class="w-3.5 h-3.5 mr-1" />
                                Caisse
                            </a>
                            <a href="/menu/{{ $tenant->id }}/A1" target="_blank" class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                                <x-heroicon-o-eye class="w-3.5 h-3.5 mr-1" />
                                Menu
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center">
                        <x-heroicon-o-building-storefront class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                        <p class="text-gray-500 mb-3">Aucun restaurant</p>
                        <a href="{{ route('superadmin.tenants.create') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium">
                            <x-heroicon-o-plus class="w-4 h-4 mr-1" />
                            Créer un restaurant
                        </a>
                    </div>
                    @endforelse
                </div>

                @if($recentTenants->count() > 0)
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    <a href="{{ route('superadmin.tenants.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                        Voir tous les restaurants
                        <x-heroicon-o-arrow-right class="w-4 h-4 ml-1" />
                    </a>
                </div>
                @endif
            </div>

            <!-- Utilisateurs récents -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                            <x-heroicon-o-users class="w-5 h-5 mr-2 text-green-600" />
                            Utilisateurs Récents
                        </h2>
                        <a href="{{ route('superadmin.users.create') }}" class="text-sm text-green-600 hover:text-green-800 flex items-center font-medium">
                            <x-heroicon-o-plus class="w-4 h-4 mr-1" />
                            Ajouter
                        </a>
                    </div>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse($recentUsers as $user)
                    <div class="p-4 hover:bg-gray-50 transition">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full flex items-center justify-center text-white font-bold">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900">{{ $user->name }}</h3>
                                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                @if($user->role)
                                    @php $roleEnum = \App\Enums\UserRole::tryFrom($user->role) @endphp
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $roleEnum ? $roleEnum->badgeClass() : 'bg-gray-100 text-gray-600' }}">
                                        {{ $roleEnum ? $roleEnum->label() : $user->role }}
                                    </span>
                                @endif
                                @if($user->tenant)
                                    <p class="text-xs text-gray-500 mt-1">{{ $user->tenant->name }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center">
                        <x-heroicon-o-users class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                        <p class="text-gray-500">Aucun utilisateur</p>
                    </div>
                    @endforelse
                </div>

                @if($recentUsers->count() > 0)
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    <a href="{{ route('superadmin.users.index') }}" class="text-green-600 hover:text-green-800 text-sm font-medium flex items-center">
                        Voir tous les utilisateurs
                        <x-heroicon-o-arrow-right class="w-4 h-4 ml-1" />
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <x-heroicon-o-bolt class="w-5 h-5 mr-2 text-yellow-500" />
                    Actions Rapides
                </h2>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="{{ route('superadmin.tenants.create') }}" class="flex flex-col items-center p-4 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 transition hover-scale">
                        <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center mb-3">
                            <x-heroicon-o-plus class="w-6 h-6 text-white" />
                        </div>
                        <span class="text-sm font-medium text-center">Nouveau Restaurant</span>
                    </a>

                    <a href="{{ route('superadmin.users.create') }}" class="flex flex-col items-center p-4 rounded-xl bg-green-50 hover:bg-green-100 text-green-700 transition hover-scale">
                        <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center mb-3">
                            <x-heroicon-o-user-plus class="w-6 h-6 text-white" />
                        </div>
                        <span class="text-sm font-medium text-center">Nouvel Utilisateur</span>
                    </a>

                    <a href="{{ route('superadmin.tenants.index') }}" class="flex flex-col items-center p-4 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 transition hover-scale">
                        <div class="w-12 h-12 bg-indigo-500 rounded-xl flex items-center justify-center mb-3">
                            <x-heroicon-o-building-storefront class="w-6 h-6 text-white" />
                        </div>
                        <span class="text-sm font-medium text-center">Tous les Restaurants</span>
                    </a>

                    <a href="{{ route('superadmin.users.index') }}" class="flex flex-col items-center p-4 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 transition hover-scale">
                        <div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center mb-3">
                            <x-heroicon-o-users class="w-6 h-6 text-white" />
                        </div>
                        <span class="text-sm font-medium text-center">Tous les Utilisateurs</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Accès rapide aux restaurants -->
        @php $allTenants = \App\Models\Tenant::latest()->take(6)->get(); @endphp
        @if($allTenants->count() > 0)
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <x-heroicon-o-rectangle-stack class="w-5 h-5 mr-2 text-purple-600" />
                    Accès Rapide aux Restaurants
                </h2>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($allTenants as $t)
                    <div class="border border-gray-200 rounded-xl p-4 hover:border-indigo-300 hover:shadow-md transition">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center text-white font-bold">
                                {{ strtoupper(substr($t->name, 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $t->name }}</h3>
                                <p class="text-xs text-gray-500">{{ $t->slug }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-2">
                            <a href="{{ route('admin.dashboard', $t->slug) }}" class="flex flex-col items-center p-2 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-600 transition text-center">
                                <x-heroicon-o-squares-2x2 class="w-5 h-5 mb-1" />
                                <span class="text-xs">Admin</span>
                            </a>
                            <a href="{{ route('kds', $t->slug) }}" class="flex flex-col items-center p-2 rounded-lg bg-orange-50 hover:bg-orange-100 text-orange-600 transition text-center">
                                <x-heroicon-o-fire class="w-5 h-5 mb-1" />
                                <span class="text-xs">Cuisine</span>
                            </a>
                            <a href="{{ route('admin.pos.index', $t->slug) }}" class="flex flex-col items-center p-2 rounded-lg bg-green-50 hover:bg-green-100 text-green-600 transition text-center">
                                <x-heroicon-o-banknotes class="w-5 h-5 mb-1" />
                                <span class="text-xs">Caisse</span>
                            </a>
                            <a href="{{ route('admin.menus', $t->slug) }}" class="flex flex-col items-center p-2 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 transition text-center">
                                <x-heroicon-o-clipboard-document-list class="w-5 h-5 mb-1" />
                                <span class="text-xs">Menu</span>
                            </a>
                            <a href="{{ route('admin.statistics', $t->slug) }}" class="flex flex-col items-center p-2 rounded-lg bg-purple-50 hover:bg-purple-100 text-purple-600 transition text-center">
                                <x-heroicon-o-chart-pie class="w-5 h-5 mb-1" />
                                <span class="text-xs">Stats</span>
                            </a>
                            <a href="/menu/{{ $t->id }}/A1" target="_blank" class="flex flex-col items-center p-2 rounded-lg bg-gray-50 hover:bg-gray-100 text-gray-600 transition text-center">
                                <x-heroicon-o-eye class="w-5 h-5 mb-1" />
                                <span class="text-xs">Voir</span>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-8">
        <div class="container mx-auto px-4 py-6">
            <div class="text-center">
                <p class="text-gray-400 text-sm">
                    SmartMenu © {{ date('Y') }}
                </p>
            </div>
        </div>
    </footer>
</body>
</html>
