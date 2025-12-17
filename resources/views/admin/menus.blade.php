<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gestion des Menus - {{ $tenant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="container mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">📋 Gestion des Menus - {{ $tenant->name }}</h1>
                    <nav class="flex space-x-4">
                        <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            📊 Dashboard
                        </a>
                        <a href="{{ route('admin.menus', $tenant->slug) }}" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                            📋 Menus
                        </a>
                        @if(auth()->user()->tenant)
                        <a href="/kds/{{ auth()->user()->tenant->slug }}" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
                            👨‍🍳 KDS
                        </a>
                        @else
                        <span class="bg-gray-500 text-white px-4 py-2 rounded">
                            👨‍🍳 Aucun tenant
                        </span>
                        @endif
                        <a href="/" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                            🏠 Accueil
                        </a>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="container mx-auto px-4 py-8">
            <!-- En-tête avec bouton nouveau menu -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold">Tous vos menus</h2>
                <button onclick="openMenuModal()" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    <i class="fas fa-plus mr-2"></i>Nouveau Menu
                </button>
            </div>

            <!-- Liste des menus -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($menus as $menu)
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="font-bold text-lg">{{ $menu->title }}</h3>
                        <span class="px-2 py-1 rounded text-xs {{ $menu->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $menu->active ? 'Actif' : 'Inactif' }}
                        </span>
                    </div>
                    
                    <div class="text-sm text-gray-600 mb-4">
                        <div>📁 {{ $menu->categories->count() }} catégories</div>
                        <div>🍽️ {{ $menu->categories->flatMap->dishes->count() }} plats</div>
                        <div>📅 Créé le: {{ $menu->created_at->format('d/m/Y') }}</div>
                    </div>

                    <div class="flex space-x-2">
                        <a href="{{ route('admin.categories', [$tenant->slug, $menu->id]) }}"
                           class="flex-1 bg-blue-500 text-white text-center py-2 rounded hover:bg-blue-600 text-sm">
                            👁️ Gérer
                        </a>
                        <button onclick="editMenu({{ json_encode($menu) }})" 
                                class="bg-yellow-500 text-white px-3 py-2 rounded hover:bg-yellow-600 text-sm">
                            ✏️ Modifier
                        </button>
                        <button onclick="deleteMenu({{ $menu->id }})" 
                                class="bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600 text-sm">
                            🗑️ Supprimer
                        </button>
                    </div>
                </div>
                @endforeach

                <!-- Carte nouveau menu -->
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors cursor-pointer"
                     onclick="openMenuModal()">
                    <div class="py-8">
                        <i class="fas fa-plus text-4xl text-gray-400 mb-3"></i>
                        <div class="text-gray-600 font-medium">Nouveau Menu</div>
                        <div class="text-sm text-gray-500 mt-1">Cliquez pour créer un nouveau menu</div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Nouveau/Édition Menu -->
    <div id="menuModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg p-6 w-96">
            <h3 id="modalTitle" class="text-xl font-bold mb-4">Nouveau Menu</h3>
            <form id="menuForm">
                <input type="hidden" id="menuId" name="id">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Nom du menu:</label>
                    <input type="text" id="menuTitle" name="title" required 
                           class="w-full border rounded px-3 py-2" placeholder="Ex: Menu Principal">
                </div>
                
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" id="menuActive" name="active" checked class="mr-2">
                        <span>Menu actif</span>
                    </label>
                </div>
                
                <div class="flex space-x-2">
                    <button type="button" onclick="closeMenuModal()" 
                            class="flex-1 bg-gray-500 text-white py-2 rounded hover:bg-gray-600">
                        Annuler
                    </button>
                    <button type="submit" id="submitButton"
                            class="flex-1 bg-blue-500 text-white py-2 rounded hover:bg-blue-600">
                        Créer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentEditingMenu = null;

        // Fonction pour récupérer le token CSRF
        function getCsrfToken() {
            const token = document.querySelector('meta[name="csrf-token"]');
            if (!token) {
                console.error('CSRF token not found!');
                return null;
            }
            return token.getAttribute('content');
        }

        // Ouvrir modal nouveau menu
        function openMenuModal() {
            currentEditingMenu = null;
            document.getElementById('modalTitle').textContent = 'Nouveau Menu';
            document.getElementById('submitButton').textContent = 'Créer';
            document.getElementById('menuId').value = '';
            document.getElementById('menuTitle').value = '';
            document.getElementById('menuActive').checked = true;
            document.getElementById('menuModal').classList.remove('hidden');
        }

        // Éditer un menu
        function editMenu(menu) {
            currentEditingMenu = menu;
            document.getElementById('modalTitle').textContent = 'Modifier le Menu';
            document.getElementById('submitButton').textContent = 'Modifier';
            document.getElementById('menuId').value = menu.id;
            document.getElementById('menuTitle').value = menu.title;
            document.getElementById('menuActive').checked = menu.active;
            document.getElementById('menuModal').classList.remove('hidden');
        }

        // Fermer modal
        function closeMenuModal() {
            document.getElementById('menuModal').classList.add('hidden');
        }

        // Soumission du formulaire avec CSRF
        document.getElementById('menuForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const csrfToken = getCsrfToken();
            if (!csrfToken) {
                alert('❌ Erreur: Token CSRF manquant');
                return;
            }
            
            const formData = new FormData(this);
            const data = {
                title: formData.get('title'),
                active: formData.get('active') ? true : false
            };

            const url = currentEditingMenu ? `{{ route('admin.menus.update', [$tenant->slug, ':id']) }}`.replace(':id', currentEditingMenu.id) : '{{ route('admin.menus.store', $tenant->slug) }}';
            const method = currentEditingMenu ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(data)
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const result = await response.json();
                
                if (result.success) {
                    alert(currentEditingMenu ? '✅ Menu modifié avec succès!' : '✅ Menu créé avec succès!');
                    closeMenuModal();
                    location.reload();
                } else {
                    alert('❌ Erreur: ' + (result.message || 'Erreur inconnue'));
                }
            } catch (error) {
                console.error('Erreur complète:', error);
                alert('❌ Erreur réseau: ' + error.message);
            }
        });

        // Supprimer un menu avec CSRF
        async function deleteMenu(menuId) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer ce menu ? Cette action est irréversible.')) {
                return;
            }

            const csrfToken = getCsrfToken();
            if (!csrfToken) {
                alert('❌ Erreur: Token CSRF manquant');
                return;
            }

            try {
                const response = await fetch(`{{ route('admin.menus.destroy', [$tenant->slug, ':id']) }}`.replace(':id', menuId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const result = await response.json();
                
                if (result.success) {
                    alert('✅ Menu supprimé avec succès!');
                    location.reload();
                } else {
                    alert('❌ Erreur: ' + result.message);
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('❌ Erreur réseau: ' + error.message);
            }
        }
    </script>
</body>
</html>