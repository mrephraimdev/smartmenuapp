<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un Tenant</title>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="container mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">Créer un Nouveau Tenant</h1>
                    <a href="{{ route('superadmin.tenants.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 inline-flex items-center">
                        <x-heroicon-o-arrow-left class="w-5 h-5 mr-2" />Retour
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="container mx-auto px-4 py-8">
            <div class="max-w-4xl mx-auto">
                <form action="{{ route('superadmin.tenants.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Section: Informations Générales -->
                    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <x-heroicon-o-building-office class="w-5 h-5 mr-2 text-indigo-500" />
                            Informations Générales
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nom -->
                            <div class="md:col-span-2">
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nom du Restaurant/Établissement *
                                </label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror"
                                       placeholder="Ex: Le Petit Bistrot"
                                       required>
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Slug -->
                            <div class="md:col-span-2">
                                <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                                    Slug (URL unique) *
                                </label>
                                <input type="text" id="slug" name="slug" value="{{ old('slug') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('slug') border-red-500 @enderror"
                                       placeholder="le-petit-bistrot"
                                       required>
                                <p class="mt-1 text-sm text-gray-500">Utilisé dans l'URL: /admin/{slug}/dashboard</p>
                                @error('slug')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Type -->
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                                    Type d'établissement *
                                </label>
                                <select id="type" name="type"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('type') border-red-500 @enderror"
                                        required>
                                    <option value="restaurant" {{ old('type') == 'restaurant' ? 'selected' : '' }}>Restaurant</option>
                                    <option value="mariage" {{ old('type') == 'mariage' ? 'selected' : '' }}>Mariage/Événement</option>
                                </select>
                                @error('type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Statut actif -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Statut
                                </label>
                                <div class="flex items-center mt-2">
                                    <input type="checkbox" id="is_active" name="is_active" value="1"
                                           {{ old('is_active', true) ? 'checked' : '' }}
                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                        Tenant actif
                                    </label>
                                </div>
                            </div>

                            <!-- Devise -->
                            <div>
                                <label for="currency" class="block text-sm font-medium text-gray-700 mb-2">
                                    Devise *
                                </label>
                                <select id="currency" name="currency"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('currency') border-red-500 @enderror"
                                        required>
                                    <option value="XOF" {{ old('currency', 'XOF') == 'XOF' ? 'selected' : '' }}>XOF (FCFA)</option>
                                    <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR (Euro)</option>
                                    <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD (Dollar)</option>
                                </select>
                                @error('currency')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Langue -->
                            <div>
                                <label for="locale" class="block text-sm font-medium text-gray-700 mb-2">
                                    Langue *
                                </label>
                                <select id="locale" name="locale"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('locale') border-red-500 @enderror"
                                        required>
                                    <option value="fr" {{ old('locale', 'fr') == 'fr' ? 'selected' : '' }}>Français</option>
                                    <option value="en" {{ old('locale') == 'en' ? 'selected' : '' }}>English</option>
                                </select>
                                @error('locale')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Section: Informations de Contact -->
                    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <x-heroicon-o-phone class="w-5 h-5 mr-2 text-green-500" />
                            Informations de Contact
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Adresse -->
                            <div class="md:col-span-2">
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                                    Adresse
                                </label>
                                <input type="text" id="address" name="address" value="{{ old('address') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('address') border-red-500 @enderror"
                                       placeholder="Ex: 123 Rue de la Paix, Dakar, Sénégal">
                                <p class="mt-1 text-sm text-gray-500">Adresse complète visible par les clients</p>
                                @error('address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Téléphone -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Téléphone
                                </label>
                                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('phone') border-red-500 @enderror"
                                       placeholder="Ex: +221 77 123 45 67">
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email
                                </label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-500 @enderror"
                                       placeholder="Ex: contact@restaurant.com">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Section: Images & Branding -->
                    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <x-heroicon-o-photo class="w-5 h-5 mr-2 text-purple-500" />
                            Images & Branding
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Logo -->
                            <div>
                                <label for="logo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Logo
                                </label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-indigo-400 transition-colors">
                                    <div class="space-y-1 text-center">
                                        <x-heroicon-o-photo class="mx-auto h-12 w-12 text-gray-400" />
                                        <div class="flex text-sm text-gray-600">
                                            <label for="logo" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none">
                                                <span>Télécharger un fichier</span>
                                                <input id="logo" name="logo" type="file" class="sr-only" accept="image/*">
                                            </label>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, GIF jusqu'à 2MB</p>
                                    </div>
                                </div>
                                <div id="logo-preview" class="mt-2 hidden">
                                    <img src="" alt="Aperçu logo" class="h-20 w-20 object-cover rounded-full mx-auto border-2 border-gray-200">
                                </div>
                                @error('logo')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Image de Couverture -->
                            <div>
                                <label for="cover" class="block text-sm font-medium text-gray-700 mb-2">
                                    Image de Couverture
                                </label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-indigo-400 transition-colors">
                                    <div class="space-y-1 text-center">
                                        <x-heroicon-o-photo class="mx-auto h-12 w-12 text-gray-400" />
                                        <div class="flex text-sm text-gray-600">
                                            <label for="cover" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none">
                                                <span>Télécharger un fichier</span>
                                                <input id="cover" name="cover" type="file" class="sr-only" accept="image/*">
                                            </label>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG jusqu'à 5MB (recommandé: 1920x600)</p>
                                    </div>
                                </div>
                                <div id="cover-preview" class="mt-2 hidden">
                                    <img src="" alt="Aperçu couverture" class="w-full h-24 object-cover rounded-lg border-2 border-gray-200">
                                </div>
                                @error('cover')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Boutons -->
                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('superadmin.tenants.index') }}"
                           class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition-colors">
                            Annuler
                        </a>
                        <button type="submit"
                                class="bg-indigo-500 text-white px-6 py-2 rounded-lg hover:bg-indigo-600 inline-flex items-center transition-colors">
                            <x-heroicon-o-check class="w-5 h-5 mr-2" />Créer le Tenant
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Générer automatiquement le slug à partir du nom
        document.getElementById('name').addEventListener('input', function() {
            const name = this.value;
            const slug = name.toLowerCase()
                            .replace(/[àáâãäå]/g, 'a')
                            .replace(/[èéêë]/g, 'e')
                            .replace(/[ìíîï]/g, 'i')
                            .replace(/[òóôõö]/g, 'o')
                            .replace(/[ùúûü]/g, 'u')
                            .replace(/[ç]/g, 'c')
                            .replace(/[^a-z0-9\s-]/g, '')
                            .replace(/\s+/g, '-')
                            .replace(/-+/g, '-')
                            .trim('-');
            document.getElementById('slug').value = slug;
        });

        // Prévisualisation du logo
        document.getElementById('logo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('logo-preview');
                    preview.querySelector('img').src = e.target.result;
                    preview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });

        // Prévisualisation de la couverture
        document.getElementById('cover').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('cover-preview');
                    preview.querySelector('img').src = e.target.result;
                    preview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
