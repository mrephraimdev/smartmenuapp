<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du Tenant</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="container mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">🏢 Détails du Tenant: {{ $tenant->name }}</h1>
                    <div class="flex space-x-4">
                        <a href="{{ route('tenants.edit', $tenant) }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            <i class="fas fa-edit mr-2"></i>Modifier
                        </a>
                        <a href="{{ route('tenants.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                            <i class="fas fa-arrow-left mr-2"></i>Retour
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="container mx-auto px-4 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Informations principales -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-6">Informations Générales</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nom</label>
                                <p class="text-gray-900">{{ $tenant->name }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Slug</label>
                                <p class="text-gray-900">{{ $tenant->slug }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $tenant->type === 'restaurant' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800' }}">
                                    {{ ucfirst($tenant->type) }}
                                </span>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Devise</label>
                                <p class="text-gray-900">{{ $tenant->currency }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Langue</label>
                                <p class="text-gray-900">{{ $tenant->locale === 'fr' ? 'Français' : 'English' }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $tenant->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $tenant->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Créé le</label>
                                <p class="text-gray-900">{{ $tenant->created_at->format('d/m/Y H:i') }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Modifié le</label>
                                <p class="text-gray-900">{{ $tenant->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Statistiques -->
                    <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-6">Statistiques</h2>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="text-center">
                                <div class="text-3xl font-bold text-blue-600">{{ $tenant->users()->count() }}</div>
                                <div class="text-gray-600">Utilisateurs</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-green-600">{{ $tenant->menus()->count() }}</div>
                                <div class="text-gray-600">Menus</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-purple-600">{{ $tenant->orders()->count() }}</div>
                                <div class="text-gray-600">Commandes</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions rapides -->
                <div>
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-6">Actions Rapides</h2>

                        <div class="space-y-4">
                            <a href="{{ route('admin.dashboard', $tenant->slug) }}"
                               class="block w-full bg-green-500 text-white text-center px-4 py-3 rounded hover:bg-green-600">
                                <i class="fas fa-cog mr-2"></i>Administration
                            </a>

                            <a href="{{ route('admin.menus', $tenant->slug) }}"
                               class="block w-full bg-blue-500 text-white text-center px-4 py-3 rounded hover:bg-blue-600">
                                <i class="fas fa-utensils mr-2"></i>Gérer les Menus
                            </a>

                            <a href="{{ route('superadmin.users') }}?tenant={{ $tenant->id }}"
                               class="block w-full bg-purple-500 text-white text-center px-4 py-3 rounded hover:bg-purple-600">
                                <i class="fas fa-users mr-2"></i>Gérer les Utilisateurs
                            </a>

                            <a href="/menu?tenant={{ $tenant->id }}&table=1"
                               class="block w-full bg-orange-500 text-white text-center px-4 py-3 rounded hover:bg-orange-600">
                                <i class="fas fa-eye mr-2"></i>Voir le Menu Client
                            </a>
                        </div>
                    </div>

                    <!-- Utilisateurs récents -->
                    <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Utilisateurs Récents</h3>

                        <div class="space-y-3">
                            @forelse($tenant->users()->latest()->limit(5)->get() as $user)
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                </div>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    {{ $user->role ?? 'N/A' }}
                                </span>
                            </div>
                            @empty
                            <p class="text-gray-500 text-sm">Aucun utilisateur</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
