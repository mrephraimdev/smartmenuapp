<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle Table - {{ $tenant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-6">
        <!-- En-tête -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">🍽️ Nouvelle Table</h1>
                <p class="text-gray-600">{{ $tenant->name }}</p>
            </div>
            <a href="{{ route('admin.tables.index', $tenant->slug) }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                <i class="fas fa-arrow-left mr-2"></i>Retour
            </a>
        </div>

        <!-- Formulaire -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <form action="{{ route('admin.tables.store', $tenant->slug) }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Code de la table -->
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                            Code de la table <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="code" name="code" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Ex: A01, T05, VIP1..."
                               maxlength="10">
                        <p class="text-sm text-gray-500 mt-1">Code unique pour identifier la table (max 10 caractères)</p>
                    </div>

                    <!-- Nom de la table -->
                    <div>
                        <label for="label" class="block text-sm font-medium text-gray-700 mb-2">
                            Nom de la table <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="label" name="label" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Ex: Table 1, Terrasse A, VIP..."
                               maxlength="255">
                        <p class="text-sm text-gray-500 mt-1">Nom affiché pour la table</p>
                    </div>

                    <!-- Capacité -->
                    <div>
                        <label for="capacity" class="block text-sm font-medium text-gray-700 mb-2">
                            Capacité <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="capacity" name="capacity" required min="1" max="50"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="4"
                               value="4">
                        <p class="text-sm text-gray-500 mt-1">Nombre de personnes maximum (1-50)</p>
                    </div>

                    <!-- Statut actif -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Statut
                        </label>
                        <div class="flex items-center">
                            <input type="checkbox" id="is_active" name="is_active" checked
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                Table active
                            </label>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Les tables inactives ne peuvent pas recevoir de commandes</p>
                    </div>
                </div>

                <!-- Boutons -->
                <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.tables.index', $tenant->slug) }}"
                       class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                        <i class="fas fa-times mr-2"></i>Annuler
                    </a>
                    <button type="submit"
                            class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                        <i class="fas fa-save mr-2"></i>Créer la table
                    </button>
                </div>
            </form>
        </div>

        <!-- Informations supplémentaires -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Informations importantes</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Le code doit être unique pour ce restaurant</li>
                            <li>Le QR code sera généré automatiquement avec ce code</li>
                            <li>Vous pouvez modifier ces informations plus tard</li>
                            <li>Pour créer plusieurs tables rapidement, utilisez la fonction "Générer"</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-format du code en majuscules
        document.getElementById('code').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });

        // Validation du formulaire
        document.querySelector('form').addEventListener('submit', function(e) {
            const code = document.getElementById('code').value.trim();
            const label = document.getElementById('label').value.trim();
            const capacity = document.getElementById('capacity').value;

            if (!code) {
                alert('Le code de la table est obligatoire.');
                e.preventDefault();
                return;
            }

            if (!label) {
                alert('Le nom de la table est obligatoire.');
                e.preventDefault();
                return;
            }

            if (!capacity || capacity < 1 || capacity > 50) {
                alert('La capacité doit être comprise entre 1 et 50 personnes.');
                e.preventDefault();
                return;
            }
        });
    </script>
</body>
</html>
