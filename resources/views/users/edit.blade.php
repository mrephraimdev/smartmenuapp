<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>Modifier Utilisateur - {{ $user->name }}</title>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="container mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">Modifier: {{ $user->name }}</h1>
                    <nav class="flex space-x-4">
                        <a href="{{ route('superadmin.users.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 inline-flex items-center">
                            <x-heroicon-o-arrow-left class="w-5 h-5 mr-2" />Retour
                        </a>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="container mx-auto px-4 py-8">
            <div class="max-w-2xl mx-auto">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <form method="POST" action="{{ route('superadmin.users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nom -->
                            <div class="md:col-span-2">
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nom complet *
                                </label>
                                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror"
                                       required>
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="md:col-span-2">
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Adresse email *
                                </label>
                                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-500 @enderror"
                                       required>
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Mot de passe -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nouveau mot de passe
                                </label>
                                <input type="password" id="password" name="password"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('password') border-red-500 @enderror"
                                       placeholder="Laisser vide pour ne pas changer">
                                <p class="mt-1 text-sm text-gray-500">Minimum 8 caractères</p>
                                @error('password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Confirmation mot de passe -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                    Confirmer le mot de passe
                                </label>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <!-- Rôle -->
                            <div class="md:col-span-2">
                                <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                                    Rôle *
                                </label>
                                <select id="role" name="role"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('role') border-red-500 @enderror"
                                        required>
                                    <option value="">-- Sélectionner un rôle --</option>
                                    @foreach(\App\Enums\UserRole::cases() as $role)
                                    <option value="{{ $role->value }}" {{ old('role', $user->role) == $role->value ? 'selected' : '' }}>
                                        {{ $role->label() }} - {{ $role->description() }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror

                                <!-- Description des rôles -->
                                <div class="mt-3 p-4 bg-gray-50 rounded-lg text-sm">
                                    <p class="font-medium text-gray-700 mb-2">Description des rôles :</p>
                                    <ul class="space-y-1 text-gray-600">
                                        <li><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 mr-2">SUPER_ADMIN</span> Accès global à tous les tenants</li>
                                        <li><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 mr-2">ADMIN</span> Gestion complète de son restaurant</li>
                                        <li><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 mr-2">CAISSIER</span> POS, paiements, encaissements</li>
                                        <li><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800 mr-2">CHEF</span> Cuisine, préparation des commandes (KDS)</li>
                                        <li><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mr-2">SERVEUR</span> Service en salle, commandes, tables</li>
                                        <li><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 mr-2">CLIENT</span> Menu public et commandes</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Tenant -->
                            <div class="md:col-span-2" id="tenant-field">
                                <label for="tenant_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Restaurant assigné
                                </label>
                                <select id="tenant_id" name="tenant_id"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('tenant_id') border-red-500 @enderror">
                                    <option value="">-- Aucun (Super Admin) --</option>
                                    @foreach(\App\Models\Tenant::where('is_active', true)->orderBy('name')->get() as $tenant)
                                    <option value="{{ $tenant->id }}" {{ old('tenant_id', $user->tenant_id) == $tenant->id ? 'selected' : '' }}>
                                        {{ $tenant->name }} ({{ $tenant->slug }})
                                    </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-sm text-gray-500" id="tenant-help">
                                    @if($user->role === 'SUPER_ADMIN')
                                        Le Super Admin a accès à tous les restaurants.
                                    @elseif($user->tenant)
                                        Actuellement assigné à: {{ $user->tenant->name }}
                                    @else
                                        Aucun restaurant assigné.
                                    @endif
                                </p>
                                @error('tenant_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Informations -->
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Informations</h3>
                            <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                                <div>
                                    <span class="font-medium">Créé le:</span>
                                    {{ $user->created_at->format('d/m/Y H:i') }}
                                </div>
                                <div>
                                    <span class="font-medium">Dernière modification:</span>
                                    {{ $user->updated_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        </div>

                        <!-- Boutons -->
                        <div class="mt-8 flex justify-end space-x-4">
                            <a href="{{ route('superadmin.users.index') }}"
                               class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition-colors">
                                Annuler
                            </a>
                            <button type="submit"
                                    class="bg-indigo-500 text-white px-6 py-2 rounded-lg hover:bg-indigo-600 inline-flex items-center transition-colors">
                                <x-heroicon-o-check class="w-5 h-5 mr-2" />Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Afficher/masquer le champ tenant en fonction du rôle
        document.getElementById('role').addEventListener('change', function() {
            const tenantSelect = document.getElementById('tenant_id');
            const tenantHelp = document.getElementById('tenant-help');

            if (this.value === 'SUPER_ADMIN') {
                tenantSelect.value = '';
                tenantHelp.textContent = 'Le Super Admin a accès à tous les restaurants.';
            } else if (this.value === 'CLIENT') {
                tenantSelect.value = '';
                tenantHelp.textContent = 'Les clients n\'ont pas besoin d\'être assignés à un restaurant.';
            } else if (this.value) {
                tenantHelp.textContent = 'Ce rôle doit être associé à un restaurant.';
            }
        });
    </script>
</body>
</html>
