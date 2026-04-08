<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier {{ $user->name }} - {{ $tenant->name }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .gradient-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="gradient-header text-white shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.staff.index', $tenant->slug) }}" class="text-white/80 hover:text-white">
                        <x-heroicon-o-arrow-left class="w-6 h-6" />
                    </a>
                    <div>
                        <h1 class="text-xl font-bold">Modifier le membre</h1>
                        <p class="text-white/70 text-sm">{{ $tenant->name }}</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold mr-4">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800">{{ $user->name }}</h2>
                            <p class="text-sm text-gray-500">{{ $user->email }}</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.staff.update', [$tenant->slug, $user]) }}" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Nom -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nom complet <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror"
                            placeholder="Ex: Jean Dupont">
                        @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Adresse email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('email') border-red-500 @enderror"
                            placeholder="Ex: jean.dupont@email.com">
                        @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Rôle -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                            Rôle <span class="text-red-500">*</span>
                        </label>
                        <select id="role" name="role" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('role') border-red-500 @enderror">
                            @foreach($roles as $role)
                            <option value="{{ $role->value }}" {{ old('role', $user->role) == $role->value ? 'selected' : '' }}>
                                {{ $role->label() }} - {{ $role->description() }}
                            </option>
                            @endforeach
                        </select>
                        @error('role')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nouveau mot de passe (optionnel) -->
                    <div class="pt-4 border-t border-gray-100">
                        <p class="text-sm text-gray-500 mb-4">Laissez vide si vous ne souhaitez pas changer le mot de passe</p>

                        <div class="space-y-4">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nouveau mot de passe
                                </label>
                                <input type="password" id="password" name="password"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('password') border-red-500 @enderror"
                                    placeholder="Minimum 8 caractères">
                                @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                    Confirmer le nouveau mot de passe
                                </label>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Répétez le mot de passe">
                            </div>
                        </div>
                    </div>

                    <!-- Boutons -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                        <form method="POST" action="{{ route('admin.staff.destroy', [$tenant->slug, $user]) }}" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce membre ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition flex items-center">
                                <x-heroicon-o-trash class="w-5 h-5 mr-1" />
                                Supprimer
                            </button>
                        </form>

                        <div class="flex space-x-4">
                            <a href="{{ route('admin.staff.index', $tenant->slug) }}" class="px-6 py-3 text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition">
                                Annuler
                            </a>
                            <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition flex items-center">
                                <x-heroicon-o-check class="w-5 h-5 mr-2" />
                                Enregistrer
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Informations sur le compte -->
            <div class="mt-6 bg-gray-50 border border-gray-200 rounded-xl p-4">
                <h3 class="text-sm font-medium text-gray-700 mb-2">Informations du compte</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Créé le:</span>
                        <span class="text-gray-800 ml-1">{{ $user->created_at->format('d/m/Y à H:i') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Dernière modification:</span>
                        <span class="text-gray-800 ml-1">{{ $user->updated_at->format('d/m/Y à H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-8">
        <div class="container mx-auto px-4 py-6">
            <div class="text-center">
                <p class="text-gray-400 text-sm">SmartMenu © {{ date('Y') }}</p>
            </div>
        </div>
    </footer>
</body>
</html>
