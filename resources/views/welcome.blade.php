<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartMenu - Tableau de Bord</title>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .gradient-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .animate-fade-in {
            animation: fadeIn 0.6s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="gradient-header text-white">
        <div class="container mx-auto px-4 py-6">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <img src="{{ asset('images/SmartMenu.png') }}" alt="SmartMenu" class="h-12 w-auto">
                    <div>
                        <h1 class="text-2xl font-bold">SmartMenu</h1>
                        <p class="text-white/80 text-sm">Plateforme de Gestion Restaurant</p>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="text-right hidden md:block">
                        <p class="font-medium">{{ auth()->user()->name }}</p>
                        <p class="text-white/70 text-sm">{{ auth()->user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="bg-white/20 backdrop-blur-lg hover:bg-white/30 px-4 py-2 rounded-lg transition-all flex items-center">
                            @svg('heroicon-o-arrow-right-on-rectangle', 'w-5 h-5')
                            <span class="ml-2 hidden sm:inline">Déconnexion</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <!-- Carte utilisateur -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8 animate-fade-in">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full w-16 h-16 flex items-center justify-center text-white text-xl font-bold">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Bienvenue, {{ auth()->user()->name }}</h2>
                        @if(auth()->user()->role)
                            @php $roleEnum = \App\Enums\UserRole::tryFrom(auth()->user()->role) @endphp
                            @if($roleEnum)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $roleEnum->badgeClass() }} mt-1">
                                @svg('heroicon-o-shield-check', 'w-4 h-4 mr-1')
                                {{ $roleEnum->label() }}
                            </span>
                            @endif
                        @endif
                    </div>
                </div>

                @if(auth()->user()->tenant)
                <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl px-4 py-3 border border-indigo-200">
                    <div class="flex items-center space-x-3">
                        @svg('heroicon-o-building-storefront', 'w-6 h-6 text-indigo-600')
                        <div>
                            <p class="text-xs text-gray-500">Restaurant</p>
                            <p class="font-semibold text-gray-800">{{ auth()->user()->tenant->name }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        @php
            $user = auth()->user();
            $roleEnum = $user->role ? \App\Enums\UserRole::tryFrom($user->role) : null;
            $isSuperAdmin = $roleEnum === \App\Enums\UserRole::SUPER_ADMIN;
            $isAdmin = $roleEnum === \App\Enums\UserRole::ADMIN;
            $isCaissier = $roleEnum === \App\Enums\UserRole::CAISSIER;
            $isChef = $roleEnum === \App\Enums\UserRole::CHEF;
            $isServeur = $roleEnum === \App\Enums\UserRole::SERVEUR;
            $tenant = $user->tenant;
        @endphp

        <!-- Message si pas de tenant assigné -->
        @if(!$tenant && !$isSuperAdmin)
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 mb-8 animate-fade-in">
            <div class="flex items-start space-x-4">
                @svg('heroicon-o-exclamation-triangle', 'w-8 h-8 text-yellow-500 flex-shrink-0')
                <div>
                    <h3 class="font-bold text-yellow-800 mb-1">Restaurant non assigné</h3>
                    <p class="text-yellow-700">Vous n'êtes pas encore assigné à un restaurant. Veuillez contacter un administrateur pour être ajouté à un établissement.</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Accès rapides selon le rôle -->
        <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
            @svg('heroicon-o-squares-2x2', 'w-6 h-6 mr-2 text-indigo-600')
            Vos Accès
        </h3>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- SUPER_ADMIN -->
            @if($isSuperAdmin)
            <a href="{{ route('superadmin.dashboard') }}" class="bg-white rounded-2xl shadow-lg overflow-hidden card-hover animate-fade-in">
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-5 text-white">
                    @svg('heroicon-o-shield-check', 'w-8 h-8 mb-2')
                    <h3 class="text-lg font-bold">Super Admin</h3>
                </div>
                <div class="p-5">
                    <p class="text-gray-600 text-sm mb-4">Gestion globale de la plateforme</p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center">@svg('heroicon-o-check', 'w-4 h-4 text-green-500 mr-2')Gestion des restaurants</li>
                        <li class="flex items-center">@svg('heroicon-o-check', 'w-4 h-4 text-green-500 mr-2')Gestion des utilisateurs</li>
                        <li class="flex items-center">@svg('heroicon-o-check', 'w-4 h-4 text-green-500 mr-2')Statistiques globales</li>
                    </ul>
                </div>
            </a>

            <a href="{{ route('superadmin.tenants.index') }}" class="bg-white rounded-2xl shadow-lg overflow-hidden card-hover animate-fade-in">
                <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 p-5 text-white">
                    @svg('heroicon-o-building-storefront', 'w-8 h-8 mb-2')
                    <h3 class="text-lg font-bold">Restaurants</h3>
                </div>
                <div class="p-5">
                    <p class="text-gray-600 text-sm mb-4">Gérer tous les restaurants</p>
                    <span class="inline-flex items-center text-indigo-600 font-medium text-sm">
                        @svg('heroicon-o-arrow-right', 'w-4 h-4 mr-1')
                        Accéder
                    </span>
                </div>
            </a>

            <a href="{{ route('superadmin.users.index') }}" class="bg-white rounded-2xl shadow-lg overflow-hidden card-hover animate-fade-in">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-5 text-white">
                    @svg('heroicon-o-users', 'w-8 h-8 mb-2')
                    <h3 class="text-lg font-bold">Utilisateurs</h3>
                </div>
                <div class="p-5">
                    <p class="text-gray-600 text-sm mb-4">Gérer tous les utilisateurs</p>
                    <span class="inline-flex items-center text-blue-600 font-medium text-sm">
                        @svg('heroicon-o-arrow-right', 'w-4 h-4 mr-1')
                        Accéder
                    </span>
                </div>
            </a>
            @endif

            <!-- ADMIN -->
            @if($isAdmin && $tenant)
            <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="bg-white rounded-2xl shadow-lg overflow-hidden card-hover animate-fade-in">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-5 text-white">
                    @svg('heroicon-o-cog-6-tooth', 'w-8 h-8 mb-2')
                    <h3 class="text-lg font-bold">Administration</h3>
                </div>
                <div class="p-5">
                    <p class="text-gray-600 text-sm mb-4">Gérer votre restaurant</p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center">@svg('heroicon-o-check', 'w-4 h-4 text-green-500 mr-2')Menus et plats</li>
                        <li class="flex items-center">@svg('heroicon-o-check', 'w-4 h-4 text-green-500 mr-2')Tables et QR codes</li>
                        <li class="flex items-center">@svg('heroicon-o-check', 'w-4 h-4 text-green-500 mr-2')Statistiques</li>
                    </ul>
                </div>
            </a>

            <a href="{{ route('admin.pos.index', $tenant->slug) }}" class="bg-white rounded-2xl shadow-lg overflow-hidden card-hover animate-fade-in">
                <div class="bg-gradient-to-r from-green-500 to-green-600 p-5 text-white">
                    @svg('heroicon-o-banknotes', 'w-8 h-8 mb-2')
                    <h3 class="text-lg font-bold">Caisse (POS)</h3>
                </div>
                <div class="p-5">
                    <p class="text-gray-600 text-sm mb-4">Point de vente et paiements</p>
                    <span class="inline-flex items-center text-green-600 font-medium text-sm">
                        @svg('heroicon-o-arrow-right', 'w-4 h-4 mr-1')
                        Accéder
                    </span>
                </div>
            </a>

            <a href="{{ route('kds', $tenant->slug) }}" class="bg-white rounded-2xl shadow-lg overflow-hidden card-hover animate-fade-in">
                <div class="bg-gradient-to-r from-orange-500 to-orange-600 p-5 text-white">
                    @svg('heroicon-o-fire', 'w-8 h-8 mb-2')
                    <h3 class="text-lg font-bold">Cuisine (KDS)</h3>
                </div>
                <div class="p-5">
                    <p class="text-gray-600 text-sm mb-4">Écran des commandes cuisine</p>
                    <span class="inline-flex items-center text-orange-600 font-medium text-sm">
                        @svg('heroicon-o-arrow-right', 'w-4 h-4 mr-1')
                        Accéder
                    </span>
                </div>
            </a>
            @endif

            <!-- CAISSIER -->
            @if($isCaissier && $tenant)
            <a href="{{ route('caisse.pos.index', $tenant->slug) }}" class="bg-white rounded-2xl shadow-lg overflow-hidden card-hover animate-fade-in">
                <div class="bg-gradient-to-r from-green-500 to-green-600 p-5 text-white">
                    @svg('heroicon-o-banknotes', 'w-8 h-8 mb-2')
                    <h3 class="text-lg font-bold">Caisse (POS)</h3>
                </div>
                <div class="p-5">
                    <p class="text-gray-600 text-sm mb-4">Point de vente et paiements</p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center">@svg('heroicon-o-check', 'w-4 h-4 text-green-500 mr-2')Encaissements</li>
                        <li class="flex items-center">@svg('heroicon-o-check', 'w-4 h-4 text-green-500 mr-2')Commandes</li>
                        <li class="flex items-center">@svg('heroicon-o-check', 'w-4 h-4 text-green-500 mr-2')Tickets</li>
                    </ul>
                </div>
            </a>

            <a href="{{ route('caisse.orders.index', $tenant->slug) }}" class="bg-white rounded-2xl shadow-lg overflow-hidden card-hover animate-fade-in">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-5 text-white">
                    @svg('heroicon-o-clipboard-document-list', 'w-8 h-8 mb-2')
                    <h3 class="text-lg font-bold">Commandes</h3>
                </div>
                <div class="p-5">
                    <p class="text-gray-600 text-sm mb-4">Voir toutes les commandes</p>
                    <span class="inline-flex items-center text-blue-600 font-medium text-sm">
                        @svg('heroicon-o-arrow-right', 'w-4 h-4 mr-1')
                        Accéder
                    </span>
                </div>
            </a>
            @endif

            <!-- CHEF -->
            @if($isChef && $tenant)
            <a href="{{ route('kds', $tenant->slug) }}" class="bg-white rounded-2xl shadow-lg overflow-hidden card-hover animate-fade-in">
                <div class="bg-gradient-to-r from-orange-500 to-orange-600 p-5 text-white">
                    @svg('heroicon-o-fire', 'w-8 h-8 mb-2')
                    <h3 class="text-lg font-bold">Cuisine (KDS)</h3>
                </div>
                <div class="p-5">
                    <p class="text-gray-600 text-sm mb-4">Écran des commandes</p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center">@svg('heroicon-o-check', 'w-4 h-4 text-green-500 mr-2')Commandes en attente</li>
                        <li class="flex items-center">@svg('heroicon-o-check', 'w-4 h-4 text-green-500 mr-2')En préparation</li>
                        <li class="flex items-center">@svg('heroicon-o-check', 'w-4 h-4 text-green-500 mr-2')Prêtes à servir</li>
                    </ul>
                </div>
            </a>
            @endif

            <!-- SERVEUR -->
            @if($isServeur && $tenant)
            <a href="{{ route('kds', $tenant->slug) }}" class="bg-white rounded-2xl shadow-lg overflow-hidden card-hover animate-fade-in">
                <div class="bg-gradient-to-r from-teal-500 to-teal-600 p-5 text-white">
                    @svg('heroicon-o-clipboard-document-check', 'w-8 h-8 mb-2')
                    <h3 class="text-lg font-bold">Commandes</h3>
                </div>
                <div class="p-5">
                    <p class="text-gray-600 text-sm mb-4">Suivi des commandes</p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center">@svg('heroicon-o-check', 'w-4 h-4 text-green-500 mr-2')Voir les commandes</li>
                        <li class="flex items-center">@svg('heroicon-o-check', 'w-4 h-4 text-green-500 mr-2')Statuts en temps réel</li>
                        <li class="flex items-center">@svg('heroicon-o-check', 'w-4 h-4 text-green-500 mr-2')Commandes prêtes</li>
                    </ul>
                </div>
            </a>
            @endif
        </div>

        <!-- Actions rapides pour ADMIN et SUPER_ADMIN -->
        @if(($isAdmin && $tenant) || $isSuperAdmin)
        <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
            @svg('heroicon-o-bolt', 'w-6 h-6 mr-2 text-yellow-500')
            Actions Rapides
        </h3>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-8">
            @if($isAdmin && $tenant)
            <a href="{{ route('admin.menus', $tenant->slug) }}" class="bg-white rounded-xl shadow p-4 hover:shadow-lg transition-all text-center card-hover">
                @svg('heroicon-o-clipboard-document-list', 'w-7 h-7 mx-auto mb-2 text-blue-500')
                <p class="font-medium text-gray-800 text-sm">Menus</p>
            </a>

            <a href="{{ route('admin.tables.index', $tenant->slug) }}" class="bg-white rounded-xl shadow p-4 hover:shadow-lg transition-all text-center card-hover">
                @svg('heroicon-o-table-cells', 'w-7 h-7 mx-auto mb-2 text-purple-500')
                <p class="font-medium text-gray-800 text-sm">Tables</p>
            </a>

            <a href="{{ route('admin.qrcodes.index', $tenant->slug) }}" class="bg-white rounded-xl shadow p-4 hover:shadow-lg transition-all text-center card-hover">
                @svg('heroicon-o-qr-code', 'w-7 h-7 mx-auto mb-2 text-teal-500')
                <p class="font-medium text-gray-800 text-sm">QR Codes</p>
            </a>

            <a href="{{ route('admin.statistics', $tenant->slug) }}" class="bg-white rounded-xl shadow p-4 hover:shadow-lg transition-all text-center card-hover">
                @svg('heroicon-o-chart-bar', 'w-7 h-7 mx-auto mb-2 text-green-500')
                <p class="font-medium text-gray-800 text-sm">Statistiques</p>
            </a>

            <a href="{{ route('admin.reservations.index', $tenant->slug) }}" class="bg-white rounded-xl shadow p-4 hover:shadow-lg transition-all text-center card-hover">
                @svg('heroicon-o-calendar-days', 'w-7 h-7 mx-auto mb-2 text-indigo-500')
                <p class="font-medium text-gray-800 text-sm">Réservations</p>
            </a>

            <a href="/menu/{{ $tenant->id }}/A1" target="_blank" class="bg-white rounded-xl shadow p-4 hover:shadow-lg transition-all text-center card-hover">
                @svg('heroicon-o-eye', 'w-7 h-7 mx-auto mb-2 text-gray-500')
                <p class="font-medium text-gray-800 text-sm">Aperçu Menu</p>
            </a>
            @endif

            @if($isSuperAdmin)
            <a href="{{ route('superadmin.tenants.create') }}" class="bg-white rounded-xl shadow p-4 hover:shadow-lg transition-all text-center card-hover">
                @svg('heroicon-o-plus-circle', 'w-7 h-7 mx-auto mb-2 text-green-500')
                <p class="font-medium text-gray-800 text-sm">Nouveau Restaurant</p>
            </a>

            <a href="{{ route('superadmin.users.create') }}" class="bg-white rounded-xl shadow p-4 hover:shadow-lg transition-all text-center card-hover">
                @svg('heroicon-o-user-plus', 'w-7 h-7 mx-auto mb-2 text-blue-500')
                <p class="font-medium text-gray-800 text-sm">Nouvel Utilisateur</p>
            </a>
            @endif
        </div>
        @endif

        <!-- Liste des restaurants pour SUPER_ADMIN -->
        @if($isSuperAdmin)
        @php $tenants = \App\Models\Tenant::latest()->take(6)->get(); @endphp
        @if($tenants->count() > 0)
        <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
            @svg('heroicon-o-building-storefront', 'w-6 h-6 mr-2 text-indigo-600')
            Restaurants Récents
        </h3>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($tenants as $t)
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden card-hover">
                <div class="bg-gradient-to-r from-indigo-500 to-purple-500 p-4 text-white">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center text-white font-bold">
                            {{ strtoupper(substr($t->name, 0, 1)) }}
                        </div>
                        <div>
                            <h4 class="font-bold">{{ $t->name }}</h4>
                            <p class="text-white/70 text-sm">{{ $t->slug }}</p>
                        </div>
                    </div>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-3 gap-2">
                        <a href="{{ route('admin.dashboard', $t->slug) }}" class="flex flex-col items-center p-2 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-600 transition">
                            @svg('heroicon-o-squares-2x2', 'w-5 h-5 mb-1')
                            <span class="text-xs">Admin</span>
                        </a>
                        <a href="{{ route('kds', $t->slug) }}" class="flex flex-col items-center p-2 rounded-lg bg-orange-50 hover:bg-orange-100 text-orange-600 transition">
                            @svg('heroicon-o-fire', 'w-5 h-5 mb-1')
                            <span class="text-xs">KDS</span>
                        </a>
                        <a href="/menu/{{ $t->id }}/A1" target="_blank" class="flex flex-col items-center p-2 rounded-lg bg-gray-50 hover:bg-gray-100 text-gray-600 transition">
                            @svg('heroicon-o-eye', 'w-5 h-5 mb-1')
                            <span class="text-xs">Menu</span>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
        @endif
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12">
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
