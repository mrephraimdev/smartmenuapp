<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catégories - {{ $menu->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="container mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">📁 Catégories - {{ $menu->title }}</h1>
                        <nav class="text-sm text-gray-600 mt-1">
                            <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="hover:text-blue-600">Dashboard</a>
                            > <a href="{{ route('admin.menus', $tenant->slug) }}" class="hover:text-blue-600">Menus</a>
                            > <span>Catégories</span>
                        </nav>
                    </div>
                    <nav class="flex space-x-4">
                        <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            📊 Dashboard
                        </a>
                        <a href="{{ route('admin.menus', $tenant->slug) }}" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                            📋 Menus
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
            <!-- En-tête -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold">Gestion des catégories</h2>
                <button onclick="openCategoryModal()" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    <i class="fas fa-plus mr-2"></i>Nouvelle Catégorie
                </button>
            </div>

            <!-- Liste des catégories -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($menu->categories as $category)
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="font-bold text-lg">{{ $category->name }}</h3>
                        <span class="text-sm text-gray-500">#{{ $category->sort_order }}</span>
                    </div>
                    
                    <div class="text-sm text-gray-600 mb-4">
                        <div>🍽️ {{ $category->dishes->count() }} plats</div>
                        <div>📅 Créé le: {{ $category->created_at->format('d/m/Y') }}</div>
                    </div>

                    <div class="flex space-x-2">
                        <a href="{{ route('admin.dishes', [$tenant->slug, $category->id]) }}"
                           class="flex-1 bg-blue-500 text-white text-center py-2 rounded hover:bg-blue-600 text-sm">
                            👁️ Voir les plats
                        </a>
                    </div>
                </div>
                @endforeach

                <!-- Carte nouvelle catégorie -->
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors cursor-pointer"
                     onclick="openCategoryModal()">
                    <div class="py-8">
                        <i class="fas fa-plus text-4xl text-gray-400 mb-3"></i>
                        <div class="text-gray-600 font-medium">Nouvelle Catégorie</div>
                        <div class="text-sm text-gray-500 mt-1">Cliquez pour créer une nouvelle catégorie</div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Nouvelle Catégorie -->
    <div id="categoryModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg p-6 w-96">
            <h3 class="text-xl font-bold mb-4">Nouvelle Catégorie</h3>
            <form id="categoryForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Nom de la catégorie:</label>
                    <input type="text" name="name" required 
                           class="w-full border rounded px-3 py-2" placeholder="Ex: Entrées, Plats Principaux...">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Ordre d'affichage:</label>
                    <input type="number" name="sort_order" value="0" 
                           class="w-full border rounded px-3 py-2">
                </div>
                
                <div class="flex space-x-2">
                    <button type="button" onclick="closeCategoryModal()" 
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
        // Ouvrir modal catégorie
        function openCategoryModal() {
            document.getElementById('categoryModal').classList.remove('hidden');
        }

        // Fermer modal
        function closeCategoryModal() {
            document.getElementById('categoryModal').classList.add('hidden');
        }

        // Soumission du formulaire
        document.getElementById('categoryForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {
                name: formData.get('name'),
                sort_order: parseInt(formData.get('sort_order')) || 0
            };

            try {
                const response = await fetch('/admin/{{ $tenant->slug }}/menus/{{ $menu->id }}/categories', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                
                if (result.success) {
                    alert('Catégorie créée avec succès!');
                    closeCategoryModal();
                    location.reload();
                } else {
                    alert('Erreur: ' + result.message);
                }
            } catch (error) {
                alert('Erreur réseau: ' + error.message);
            }
        });
    </script>
</body>
</html>