<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Tenant</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="container mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">🏢 Modifier le Tenant: {{ $tenant->name }}</h1>
                    <a href="{{ route('tenants.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        <i class="fas fa-arrow-left mr-2"></i>Retour
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="container mx-auto px-4 py-8">
            <div class="max-w-2xl mx-auto">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <form action="{{ route('tenants.update', $tenant) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nom -->
                            <div class="md:col-span-2">
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nom du Tenant *
                                </label>
                                <input type="text" id="name" name="name" value="{{ old('name', $tenant->name) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror"
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
                                <input type="text" id="slug" name="slug" value="{{ old('slug', $tenant->slug) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('slug') border-red-500 @enderror"
                                       required>
                                <p class="mt-1 text-sm text-gray-500">Utilisé dans l'URL: /admin/{slug}/dashboard</p>
                                @error('slug')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Type -->
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                                    Type *
                                </label>
                                <select id="type" name="type"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('type') border-red-500 @enderror"
                                        required>
                                    <option value="restaurant" {{ old('type', $tenant->type) == 'restaurant' ? 'selected' : '' }}>Restaurant</option>
                                    <option value="mariage" {{ old('type', $tenant->type) == 'mariage' ? 'selected' : '' }}>Mariage</option>
                                </select>
                                @error('type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Devise -->
                            <div>
                                <label for="currency" class="block text-sm font-medium text-gray-700 mb-2">
                                    Devise *
                                </label>
                                <select id="currency" name="currency"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('currency') border-red-500 @enderror"
                                        required>
                                    <option value="FCFA" {{ old('currency', $tenant->currency) == 'FCFA' ? 'selected' : '' }}>FCFA</option>
                                    <option value="EUR" {{ old('currency', $tenant->currency) == 'EUR' ? 'selected' : '' }}>EUR</option>
                                    <option value="USD" {{ old('currency', $tenant->currency) == 'USD' ? 'selected' : '' }}>USD</option>
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
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('locale') border-red-500 @enderror"
                                        required>
                                    <option value="fr" {{ old('locale', $tenant->locale) == 'fr' ? 'selected' : '' }}>Français</option>
                                    <option value="en" {{ old('locale', $tenant->locale) == 'en' ? 'selected' : '' }}>English</option>
                                </select>
                                @error('locale')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Statut actif -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Statut
                                </label>
                                <div class="flex items-center">
                                    <input type="checkbox" id="is_active" name="is_active" value="1"
                                           {{ old('is_active', $tenant->is_active) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                        Tenant actif
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Boutons -->
                        <div class="mt-8 flex justify-end space-x-4">
                            <a href="{{ route('tenants.index') }}"
                               class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                                Annuler
                            </a>
                            <button type="submit"
                                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                <i class="fas fa-save mr-2"></i>Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
