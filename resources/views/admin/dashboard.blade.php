<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - {{ $tenant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="container mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">🏪 {{ $tenant->name }} - Administration</h1>
                    <nav class="flex space-x-4">
                        <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            📊 Dashboard
                        </a>
                        <a href="{{ route('admin.menus', $tenant->slug) }}" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                            📋 Menus
                        </a>
                        <a href="{{ route('admin.tables.index', $tenant->slug) }}" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
                            🍽️ Tables
                        </a>
                        <a href="{{ route('admin.qrcodes', $tenant->slug) }}" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                            📱 QR Codes
                        </a>
                        <a href="{{ route('admin.statistics', $tenant->slug) }}" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600">
                            📊 Statistiques
                        </a>
                        <a href="/kds/{{ $tenant->slug }}" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                            👨‍🍳 KDS
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
            <!-- Statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                    <div class="text-3xl font-bold text-blue-600" id="totalOrders">-</div>
                    <div class="text-gray-600">Commandes Total</div>
                </div>
                <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                    <div class="text-3xl font-bold text-green-600" id="totalRevenue">-</div>
                    <div class="text-gray-600">Chiffre d'Affaires</div>
                </div>
                <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                    <div class="text-3xl font-bold text-purple-600" id="totalDishes">-</div>
                    <div class="text-gray-600">Plats Actifs</div>
                </div>
            </div>

            <!-- Menus existants -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold mb-4">📋 Vos Menus</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($menus as $menu)
                    <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                        <h3 class="font-bold text-lg mb-2">{{ $menu->title }}</h3>
                        <p class="text-sm text-gray-600 mb-2">
                            {{ $menu->categories->count() }} catégories •
                            {{ $menu->categories->flatMap->dishes->count() }} plats
                        </p>
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.categories', [$tenant->slug, $menu->id]) }}"
                               class="flex-1 bg-blue-500 text-white text-center py-2 rounded hover:bg-blue-600 text-sm">
                                Gérer
                            </a>
                            <span class="px-3 py-2 rounded text-sm {{ $menu->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $menu->active ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>
                    </div>
                    @endforeach

                    <!-- Bouton nouveau menu -->
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-gray-400 transition-colors">
                        <button onclick="openMenuModal()" class="w-full h-full py-8">
                            <i class="fas fa-plus text-3xl text-gray-400 mb-2"></i>
                            <div class="text-gray-600">Nouveau Menu</div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Plats populaires -->
            <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
                <h2 class="text-xl font-bold mb-4">🏆 Plats Populaires</h2>
                <div id="popularDishes" class="space-y-2">
                    <!-- Chargé en AJAX -->
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Nouveau Menu -->
    <div id="menuModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg p-6 w-96">
            <h3 class="text-xl font-bold mb-4">Nouveau Menu</h3>
            <form id="menuForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Nom du menu:</label>
                    <input type="text" name="title" required
                           class="w-full border rounded px-3 py-2" placeholder="Ex: Menu Principal">
                </div>
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="active" checked class="mr-2">
                        <span>Menu actif</span>
                    </label>
                </div>
                <div class="flex space-x-2">
                    <button type="button" onclick="closeMenuModal()"
                            class="flex-1 bg-gray-500 text-white py-2 rounded hover:bg-gray-600">
                        Annuler
                    </button>
                    <button type="submit"
                            class="flex-1 bg-blue-500 text-white py-2 rounded hover:bg-blue-600">
                        Créer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Charger les statistiques
        async function loadStatistics() {
            try {
                const response = await fetch('{{ route("admin.statistics", $tenant->slug) }}');
                const data = await response.json();

                if (data.success) {
                    document.getElementById('totalOrders').textContent = data.total_orders;
                    document.getElementById('totalRevenue').textContent = data.total_revenue + ' FCFA';
                    document.getElementById('totalDishes').textContent = {{ \App\Models\Dish::where('active', true)->count() }};

                    // Afficher les plats populaires
                    const popularDishes = document.getElementById('popularDishes');
                    popularDishes.innerHTML = data.popular_dishes.map(dish => `
                        <div class="flex justify-between items-center py-2 border-b">
                            <span>${dish.name}</span>
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">
                                ${dish.order_count} commandes
                            </span>
                        </div>
                    `).join('');
                }
            } catch (error) {
                console.error('Erreur chargement statistiques:', error);
            }
        }

        // Gestion du modal menu
        function openMenuModal() {
            document.getElementById('menuModal').classList.remove('hidden');
        }

        function closeMenuModal() {
            document.getElementById('menuModal').classList.add('hidden');
        }

        // Soumission du formulaire menu
        document.getElementById('menuForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = {
                title: formData.get('title'),
                active: formData.get('active') ? true : false
            };

            try {
                const response = await fetch('{{ route("admin.menus.store", $tenant->slug) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    alert('Menu créé avec succès!');
                    closeMenuModal();
                    location.reload();
                } else {
                    alert('Erreur: ' + result.message);
                }
            } catch (error) {
                alert('Erreur réseau: ' + error.message);
            }
        });

        // Charger au démarrage
        document.addEventListener('DOMContentLoaded', function() {
            loadStatistics();
        });
    </script>
</body>
</html>
