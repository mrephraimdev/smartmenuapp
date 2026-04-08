<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Menu - Tableau de Bord</title>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .gradient-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stat-card {
            transition: all 0.3s ease;
        }

        .stat-card:hover {
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

        .pulse {
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <div class="gradient-card text-white">
        <div class="container mx-auto px-4 py-6">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <div class="bg-white/20 backdrop-blur-lg rounded-lg p-3">
                        @svg('heroicon-o-chart-bar', 'w-8 h-8')
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold">Tableau de Bord</h1>
                        <p class="text-white/80 text-sm">Vue d'ensemble de votre activité</p>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    @if(auth()->user()->tenant)
                    <div class="bg-white/20 backdrop-blur-lg rounded-lg px-4 py-2">
                        <p class="text-xs text-white/70">Restaurant</p>
                        <p class="font-semibold">{{ auth()->user()->tenant->name }}</p>
                    </div>
                    @endif

                    <a href="/welcome" class="bg-white/20 backdrop-blur-lg hover:bg-white/30 px-6 py-3 rounded-lg transition-all flex items-center">
                        @svg('heroicon-o-home', 'w-5 h-5 mr-2')
                        Accueil
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <!-- Statistiques rapides -->
        <div class="grid md:grid-cols-4 gap-6 mb-8">
            <!-- Total Commandes -->
            <div class="bg-white rounded-2xl shadow-lg p-6 stat-card animate-fade-in">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-blue-100 rounded-full p-4">
                        @svg('heroicon-o-clipboard-document-list', 'w-6 h-6 text-blue-600')
                    </div>
                    <span class="text-xs text-gray-500 bg-blue-50 px-3 py-1 rounded-full">Aujourd'hui</span>
                </div>
                <h3 class="text-gray-600 text-sm font-medium mb-1">Commandes</h3>
                <div class="flex items-end justify-between">
                    <p class="text-4xl font-bold text-gray-800">0</p>
                    <span class="text-green-600 text-sm font-medium flex items-center">
                        @svg('heroicon-o-arrow-up', 'w-4 h-4 mr-1') 0%
                    </span>
                </div>
            </div>

            <!-- Chiffre d'affaires -->
            <div class="bg-white rounded-2xl shadow-lg p-6 stat-card animate-fade-in" style="animation-delay: 0.1s">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-green-100 rounded-full p-4">
                        @svg('heroicon-o-banknotes', 'w-6 h-6 text-green-600')
                    </div>
                    <span class="text-xs text-gray-500 bg-green-50 px-3 py-1 rounded-full">Ce mois</span>
                </div>
                <h3 class="text-gray-600 text-sm font-medium mb-1">Chiffre d'affaires</h3>
                <div class="flex items-end justify-between">
                    <p class="text-4xl font-bold text-gray-800">0 FCFA</p>
                    <span class="text-green-600 text-sm font-medium flex items-center">
                        @svg('heroicon-o-arrow-up', 'w-4 h-4 mr-1') 0%
                    </span>
                </div>
            </div>

            <!-- Plats actifs -->
            <div class="bg-white rounded-2xl shadow-lg p-6 stat-card animate-fade-in" style="animation-delay: 0.2s">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-purple-100 rounded-full p-4">
                        @svg('heroicon-o-cake', 'w-6 h-6 text-purple-600')
                    </div>
                    <span class="text-xs text-gray-500 bg-purple-50 px-3 py-1 rounded-full">Menu</span>
                </div>
                <h3 class="text-gray-600 text-sm font-medium mb-1">Plats actifs</h3>
                <div class="flex items-end justify-between">
                    <p class="text-4xl font-bold text-gray-800">0</p>
                    <span class="text-gray-500 text-sm font-medium">
                        Total
                    </span>
                </div>
            </div>

            <!-- Tables -->
            <div class="bg-white rounded-2xl shadow-lg p-6 stat-card animate-fade-in" style="animation-delay: 0.3s">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-orange-100 rounded-full p-4">
                        @svg('heroicon-o-table-cells', 'w-6 h-6 text-orange-600')
                    </div>
                    <span class="text-xs text-gray-500 bg-orange-50 px-3 py-1 rounded-full">Actives</span>
                </div>
                <h3 class="text-gray-600 text-sm font-medium mb-1">Tables</h3>
                <div class="flex items-end justify-between">
                    <p class="text-4xl font-bold text-gray-800">0</p>
                    <span class="text-gray-500 text-sm font-medium">
                        Total
                    </span>
                </div>
            </div>
        </div>

        <!-- Actions rapides et informations -->
        <div class="grid md:grid-cols-3 gap-6">
            <!-- Actions rapides -->
            <div class="md:col-span-2 bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    @svg('heroicon-o-bolt', 'w-6 h-6 text-yellow-500 mr-2')
                    Actions Rapides
                </h2>

                <div class="grid md:grid-cols-3 gap-4">
                    <a href="/admin" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl p-6 hover:from-blue-600 hover:to-blue-700 transition-all text-center">
                        @svg('heroicon-o-cog-6-tooth', 'w-8 h-8 mx-auto mb-3')
                        <p class="font-medium">Administration</p>
                        <p class="text-xs text-blue-100 mt-1">Gérer les menus</p>
                    </a>

                    @if(auth()->user()->tenant)
                    <a href="/kds/{{ auth()->user()->tenant->slug }}" class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl p-6 hover:from-green-600 hover:to-green-700 transition-all text-center">
                        @svg('heroicon-o-fire', 'w-8 h-8 mx-auto mb-3')
                        <p class="font-medium">Espace Cuisine</p>
                        <p class="text-xs text-green-100 mt-1">Voir les commandes</p>
                    </a>
                    @endif

                    <a href="/menu?tenant=1&table=A1" class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl p-6 hover:from-purple-600 hover:to-purple-700 transition-all text-center">
                        @svg('heroicon-o-device-phone-mobile', 'w-8 h-8 mx-auto mb-3')
                        <p class="font-medium">Aperçu Menu</p>
                        <p class="text-xs text-purple-100 mt-1">Vue client</p>
                    </a>
                </div>
            </div>

            <!-- Informations utilisateur -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    @svg('heroicon-o-user-circle', 'w-6 h-6 text-purple-500 mr-2')
                    Mon Profil
                </h2>

                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Nom</p>
                        <p class="font-medium text-gray-800">{{ auth()->user()->name }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500 mb-1">Email</p>
                        <p class="font-medium text-gray-800 text-sm">{{ auth()->user()->email }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500 mb-1">Rôle</p>
                        <div class="flex flex-wrap gap-2">
                            @if(auth()->user()->role)
                                @php $roleEnum = \App\Enums\UserRole::tryFrom(auth()->user()->role) @endphp
                                <span class="px-3 py-1 rounded-full text-xs font-medium {{ $roleEnum ? $roleEnum->badgeClass() : 'bg-gray-100 text-gray-800' }}">
                                    {{ $roleEnum ? $roleEnum->label() : auth()->user()->role }}
                                </span>
                            @else
                                <span class="bg-gray-100 text-gray-500 px-3 py-1 rounded-full text-xs font-medium">
                                    Aucun rôle
                                </span>
                            @endif
                        </div>
                    </div>

                    @if(auth()->user()->tenant)
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Restaurant</p>
                        <p class="font-medium text-gray-800">{{ auth()->user()->tenant->name }}</p>
                        <p class="text-xs text-gray-500">{{ auth()->user()->tenant->slug }}</p>
                    </div>
                    @endif
                </div>

                <div class="mt-6 pt-6 border-t">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full bg-red-50 text-red-600 px-4 py-3 rounded-xl hover:bg-red-100 transition-all font-medium flex items-center justify-center">
                            @svg('heroicon-o-arrow-right-on-rectangle', 'w-5 h-5 mr-2')
                            Déconnexion
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Activité récente -->
        <div class="mt-8 bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                @svg('heroicon-o-clock', 'w-6 h-6 text-blue-500 mr-2')
                Activité Récente
            </h2>

            <div class="text-center py-12 text-gray-500">
                @svg('heroicon-o-clock', 'w-12 h-12 mx-auto mb-4 opacity-50')
                <p>Aucune activité récente</p>
                <p class="text-sm mt-2">Les dernières actions s'afficheront ici</p>
            </div>
        </div>
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
