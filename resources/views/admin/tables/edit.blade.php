<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Table - {{ $tenant->name }}</title>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-6">
        <!-- En-tête -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">🍽️ Modifier Table</h1>
                <p class="text-gray-600">{{ $tenant->name }} - {{ $table->label }}</p>
            </div>
            <a href="{{ route('admin.tables.index', $tenant->slug) }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 inline-flex items-center">
                <x-heroicon-o-arrow-left class="w-5 h-5 mr-2" />Retour
            </a>
        </div>

        <!-- Formulaire -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <form action="{{ route('admin.tables.update', [$tenant->slug, $table->id]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Code de la table -->
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                            Code de la table <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="code" name="code" required maxlength="10"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               value="{{ old('code', $table->code) }}">
                        @error('code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Code unique pour identifier la table</p>
                    </div>

                    <!-- Nom de la table -->
                    <div>
                        <label for="label" class="block text-sm font-medium text-gray-700 mb-2">
                            Nom de la table <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="label" name="label" required maxlength="255"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               value="{{ old('label', $table->label) }}">
                        @error('label')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Nom affiché pour les clients</p>
                    </div>

                    <!-- Capacité -->
                    <div>
                        <label for="capacity" class="block text-sm font-medium text-gray-700 mb-2">
                            Capacité <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="capacity" name="capacity" required min="1" max="50"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               value="{{ old('capacity', $table->capacity) }}">
                        @error('capacity')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Nombre de personnes maximum</p>
                    </div>

                    <!-- Statut actif -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Statut
                        </label>
                        <div class="flex items-center">
                            <input type="checkbox" id="is_active" name="is_active" value="1" {{ $table->is_active ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                Table active
                            </label>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">Désactivez pour masquer la table temporairement</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-8 flex justify-end space-x-3">
                    <a href="{{ route('admin.tables.index', $tenant->slug) }}"
                       class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Annuler
                    </a>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 inline-flex items-center">
                        <x-heroicon-o-check class="w-5 h-5 mr-2" />Mettre à jour
                    </button>
                </div>
            </form>
        </div>

        <!-- Informations sur la table -->
        <div class="bg-gray-50 rounded-lg p-4 mt-6">
            <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                <x-heroicon-o-information-circle class="w-5 h-5 mr-2" />Informations de la table
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="font-medium text-gray-600">Créée le:</span>
                    <span class="text-gray-900">{{ $table->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-600">Dernière modification:</span>
                    <span class="text-gray-900">{{ $table->updated_at->format('d/m/Y H:i') }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-600">Commandes associées:</span>
                    <span class="text-gray-900">{{ $table->orders->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-format du code en majuscules
        document.getElementById('code').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });
    </script>
</body>
</html>
