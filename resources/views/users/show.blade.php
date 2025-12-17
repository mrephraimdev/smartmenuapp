<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails Utilisateur - {{ $user->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="container mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">👤 Détails Utilisateur - {{ $user->name }}</h1>
                    <nav class="flex space-x-4">
                        <a href="{{ route('users.index') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            📋 Liste Utilisateurs
                        </a>
                        <a href="{{ route('users.edit', $user) }}" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                            ✏️ Modifier
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
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold mb-6">Informations de l'utilisateur</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Informations de base -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nom complet</label>
                            <p class="mt-1 text-lg">{{ $user->name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Adresse email</label>
                            <p class="mt-1 text-lg">{{ $user->email }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date de création</label>
                            <p class="mt-1 text-lg">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Dernière mise à jour</label>
                            <p class="mt-1 text-lg">{{ $user->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <!-- Informations avancées -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tenant assigné</label>
                            <p class="mt-1 text-lg">
                                @if($user->tenant)
                                    {{ $user->tenant->name }} ({{ $user->tenant->slug }})
                                @else
                                    <span class="text-gray-500">Aucun tenant assigné</span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Rôles</label>
                            <div class="mt-1 flex flex-wrap gap-2">
                                @forelse($user->roles as $role)
                                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                                        {{ $role->name }}
                                    </span>
                                @empty
                                    <span class="text-gray-500">Aucun rôle assigné</span>
                                @endforelse
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Statut</label>
                            <p class="mt-1">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    {{ $user->email_verified_at ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $user->email_verified_at ? 'Email vérifié' : 'Email non vérifié' }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-medium mb-4">Actions</h3>
                    <div class="flex space-x-4">
                        <a href="{{ route('users.edit', $user) }}"
                           class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                            ✏️ Modifier l'utilisateur
                        </a>

                        <form method="POST" action="{{ route('users.destroy', $user) }}"
                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')"
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                                🗑️ Supprimer l'utilisateur
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
