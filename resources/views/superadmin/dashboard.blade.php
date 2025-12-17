<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Super Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="container mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">👑 Dashboard Super Administrateur</h1>
                    <nav class="flex space-x-4">
                        <a href="{{ route('superadmin.tenants') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            🏢 Tenants
                        </a>
                        <a href="{{ route('superadmin.users') }}" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                            👥 Utilisateurs
                        </a>
                        <a href="{{ route('themes.index') }}" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                            🎨 Thèmes
                        </a>
                        <a href="/" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                            🏠 Accueil
                        </a>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="container mx-auto px-4 py-8">
            <!-- Statistiques globales -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                    <div class="text-3xl font-bold text-blue-600">{{ $stats['total_tenants'] }}</div>
                    <div class="text-gray-600">Tenants</div>
                </div>
                <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                    <div class="text-3xl font-bold text-green-600">{{ $stats['total_users'] }}</div>
                    <div class="text-gray-600">Utilisateurs</div>
                </div>
                <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                    <div class="text-3xl font-bold text-purple-600">{{ $stats['total_orders'] }}</div>
                    <div class="text-gray-600">Commandes</div>
                </div>
                <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                    <div class="text-3xl font-bold text-orange-600">{{ number_format($stats['total_revenue'], 0, ',', ' ') }} FCFA</div>
                    <div class="text-gray-600">Revenus Total</div>
                </div>
            </div>

            <!-- Tenants récents -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6">Tenants Récents</h2>

                    <div class="space-y-4">
                        @forelse($recentTenants as $tenant)
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded">
                            <div>
                                <h3 class="font-medium text-gray-900">{{ $tenant->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $tenant->slug }}</p>
                                <p class="text-sm text-gray-500">{{ ucfirst($tenant->type) }}</p>
                            </div>
                            <div class="flex space-x-2">
                                <a href="{{ route('tenants.show', $tenant) }}"
                                   class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                                    Voir
                                </a>
                                <a href="{{ route('admin.dashboard', $tenant->slug) }}"
                                   class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600">
                                    Admin
                                </a>
                            </div>
                        </div>
                        @empty
                        <p class="text-gray-500">Aucun tenant trouvé.</p>
                        @endforelse
                    </div>

                    <div class="mt-6">
                        <a href="{{ route('tenants.index') }}"
                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Voir tous les tenants →
                        </a>
                    </div>
                </div>

                <!-- Utilisateurs récents -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6">Utilisateurs Récents</h2>

                    <div class="space-y-4">
                        @forelse($recentUsers as $user)
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded">
                            <div>
                                <h3 class="font-medium text-gray-900">{{ $user->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                                @if($user->tenant)
                                <p class="text-sm text-gray-500">{{ $user->tenant->name }}</p>
                                @else
                                <p class="text-sm text-red-500">Super Admin</p>
                                @endif
                            </div>
                            <div class="text-right">
                                @if($user->roles->count() > 0)
                                    @foreach($user->roles as $role)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ $role->name }}
                                    </span>
                                    @endforeach
                                @else
                                <span class="text-sm text-gray-500">Aucun rôle</span>
                                @endif
                            </div>
                        </div>
                        @empty
                        <p class="text-gray-500">Aucun utilisateur trouvé.</p>
                        @endforelse
                    </div>

                    <div class="mt-6">
                        <a href="{{ route('superadmin.users') }}"
                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Voir tous les utilisateurs →
                        </a>
                    </div>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="bg-white rounded-lg shadow-lg p-6 mt-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Actions Rapides</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="{{ route('tenants.create') }}"
                       class="bg-blue-500 text-white text-center px-4 py-3 rounded hover:bg-blue-600">
                        <i class="fas fa-plus mr-2"></i>Créer un Tenant
                    </a>

                    <a href="{{ route('superadmin.users') }}"
                       class="bg-green-500 text-white text-center px-4 py-3 rounded hover:bg-green-600">
                        <i class="fas fa-users mr-2"></i>Gérer les Utilisateurs
                    </a>

                    <a href="/"
                       class="bg-gray-500 text-white text-center px-4 py-3 rounded hover:bg-gray-600">
                        <i class="fas fa-home mr-2"></i>Retour à l'accueil
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
