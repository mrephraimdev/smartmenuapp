<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Modifier Utilisateur - {{ $user->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="container mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">✏️ Modifier Utilisateur - {{ $user->name }}</h1>
                    <nav class="flex space-x-4">
                        <a href="{{ route('users.index') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            📋 Liste Utilisateurs
                        </a>
                        <a href="{{ route('users.show', $user) }}" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                            👁️ Voir Détails
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
                <h2 class="text-xl font-bold mb-6">Modifier les informations de l'utilisateur</h2>

                <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Informations de base -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nom complet *
                            </label>
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                                   required class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Adresse email *
                            </label>
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                                   required class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Mot de passe -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                Nouveau mot de passe
                            </label>
                            <input type="password" id="password" name="password"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="Laisser vide pour ne pas changer">
                            <p class="mt-1 text-sm text-gray-500">Minimum 8 caractères</p>
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                Confirmer le mot de passe
                            </label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('password_confirmation')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Tenant -->
                    <div>
                        <label for="tenant_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Tenant assigné
                        </label>
                        <select id="tenant_id" name="tenant_id"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Aucun tenant</option>
                            @foreach($tenants as $tenant)
                                <option value="{{ $tenant->id }}" {{ old('tenant_id', $user->tenant_id) == $tenant->id ? 'selected' : '' }}>
                                    {{ $tenant->name }} ({{ $tenant->slug }})
                                </option>
                            @endforeach
                        </select>
                        @error('tenant_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Rôles -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Rôles assignés
                        </label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach($roles as $role)
                                <label class="flex items-center">
                                    <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                           {{ $user->roles->contains($role->id) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm">{{ $role->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('roles')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                        <a href="{{ route('users.show', $user) }}"
                           class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                            Annuler
                        </a>
                        <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                            💾 Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
