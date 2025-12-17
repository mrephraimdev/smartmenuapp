<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Tables - {{ $tenant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-6">
        <!-- En-tête -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">🍽️ Gestion des Tables</h1>
                <p class="text-gray-600">{{ $tenant->name }}</p>
            </div>
            <div class="flex space-x-3">
                <button onclick="openGenerateModal()" class="bg-purple-500 text-white px-4 py-2 rounded-lg hover:bg-purple-600">
                    <i class="fas fa-magic mr-2"></i>Générer
                </button>
                <a href="{{ route('admin.tables.create', $tenant->slug) }}" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
                    <i class="fas fa-plus mr-2"></i>Nouvelle Table
                </a>
                <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                    <i class="fas fa-arrow-left mr-2"></i>Retour
                </a>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm p-4 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $tables->count() }}</div>
                <div class="text-sm text-gray-600">Total Tables</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 text-center">
                <div class="text-2xl font-bold text-green-600">{{ $tables->where('is_active', true)->count() }}</div>
                <div class="text-sm text-gray-600">Tables Actives</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 text-center">
                <div class="text-2xl font-bold text-red-600">{{ $tables->where('is_active', false)->count() }}</div>
                <div class="text-sm text-gray-600">Tables Inactives</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 text-center">
                <div class="text-2xl font-bold text-purple-600">{{ $tables->sum('capacity') }}</div>
                <div class="text-sm text-gray-600">Places Totales</div>
            </div>
        </div>

        <!-- Liste des tables -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Vos Tables</h2>
            </div>

            @if($tables->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacité</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($tables as $table)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="text-sm font-medium text-gray-900">{{ $table->code }}</div>
                                            <a href="{{ route('qrcode.show', [$tenant->id, $table->code]) }}" target="_blank"
                                               class="ml-2 text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-qrcode text-sm"></i>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $table->label }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <i class="fas fa-users mr-1"></i>{{ $table->capacity }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $table->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $table->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('admin.tables.show', [$tenant->slug, $table->id]) }}"
                                               class="text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.tables.edit', [$tenant->slug, $table->id]) }}"
                                               class="text-indigo-600 hover:text-indigo-900">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="toggleTable({{ $table->id }}, {{ $table->is_active ? 'true' : 'false' }})"
                                                    class="text-yellow-600 hover:text-yellow-900">
                                                <i class="fas fa-{{ $table->is_active ? 'ban' : 'check' }}"></i>
                                            </button>
                                            <button onclick="deleteTable({{ $table->id }}, '{{ $table->label }}')"
                                                    class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-table text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune table trouvée</h3>
                    <p class="text-gray-500 mb-4">Commencez par créer votre première table.</p>
                    <a href="{{ route('admin.tables.create', $tenant->slug) }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                        <i class="fas fa-plus mr-2"></i>Créer une table
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal génération automatique -->
    <div id="generateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg p-6 w-96">
            <h3 class="text-xl font-bold mb-4">Générer des Tables Automatiquement</h3>
            <form id="generateForm" action="{{ route('admin.tables.generate', $tenant->slug) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Préfixe:</label>
                    <input type="text" name="prefix" required maxlength="5"
                           class="w-full border rounded px-3 py-2" placeholder="Ex: A, B, T..."
                           value="T">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Numéro de départ:</label>
                    <input type="number" name="start_number" required min="1"
                           class="w-full border rounded px-3 py-2" value="1">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Nombre de tables:</label>
                    <input type="number" name="count" required min="1" max="50"
                           class="w-full border rounded px-3 py-2" value="10">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Capacité par table:</label>
                    <input type="number" name="capacity" required min="1" max="50"
                           class="w-full border rounded px-3 py-2" value="4">
                </div>
                <div class="flex space-x-2">
                    <button type="button" onclick="closeGenerateModal()"
                            class="flex-1 bg-gray-500 text-white py-2 rounded hover:bg-gray-600">
                        Annuler
                    </button>
                    <button type="submit"
                            class="flex-1 bg-purple-500 text-white py-2 rounded hover:bg-purple-600">
                        Générer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openGenerateModal() {
            document.getElementById('generateModal').classList.remove('hidden');
        }

        function closeGenerateModal() {
            document.getElementById('generateModal').classList.add('hidden');
        }

        function toggleTable(tableId, currentStatus) {
            if (confirm(currentStatus ? 'Désactiver cette table ?' : 'Activer cette table ?')) {
                fetch(`{{ route('admin.tables.index', $tenant->slug) }}/${tableId}/toggle`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Erreur réseau: ' + error.message);
                });
            }
        }

        function deleteTable(tableId, tableName) {
            if (confirm(`Êtes-vous sûr de vouloir supprimer la table "${tableName}" ?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ route('admin.tables.index', $tenant->slug) }}/${tableId}`;

                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';

                const csrfField = document.createElement('input');
                csrfField.type = 'hidden';
                csrfField.name = '_token';
                csrfField.value = '{{ csrf_token() }}';

                form.appendChild(methodField);
                form.appendChild(csrfField);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
