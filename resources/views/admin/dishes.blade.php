<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Plats - {{ $category->name }}</title>
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
                        <h1 class="text-2xl font-bold text-gray-800">🍽️ Plats - {{ $category->name }}</h1>
                        <nav class="text-sm text-gray-600 mt-1">
                            <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="hover:text-blue-600">Dashboard</a>
                            > <a href="{{ route('admin.menus', $tenant->slug) }}" class="hover:text-blue-600">Menus</a>
                            > <a href="{{ route('admin.categories', [$tenant->slug, $category->menu->id]) }}" class="hover:text-blue-600">Catégories</a>
                            > <span>Plats</span>
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
                <h2 class="text-xl font-bold">Gestion des plats</h2>
                <button onclick="openDishModal()" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    <i class="fas fa-plus mr-2"></i>Nouveau Plat
                </button>
            </div>

            <!-- Liste des plats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($category->dishes as $dish)
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex justify-between items-start mb-3">
                        <h3 class="font-bold text-lg">{{ $dish->name }}</h3>
                        <div class="flex space-x-2">
                            <span class="px-2 py-1 rounded text-xs {{ $dish->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $dish->active ? 'Actif' : 'Inactif' }}
                            </span>
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                {{ $dish->price_base }} FCFA
                            </span>
                        </div>
                    </div>
                    
                    @if($dish->description)
                    <p class="text-sm text-gray-600 mb-3">{{ $dish->description }}</p>
                    @endif
                    
                    <div class="text-xs text-gray-500 mb-4">
                        <div>🔄 {{ $dish->variants->count() }} variantes</div>
                        <div>⚙️ {{ $dish->options->count() }} options</div>
                    </div>

                    <div class="flex space-x-2">
                        <button onclick="editDish({{ $dish->id }})" 
                                class="flex-1 bg-blue-500 text-white text-center py-2 rounded hover:bg-blue-600 text-sm">
                            ✏️ Modifier
                        </button>
                        <button onclick="toggleDish({{ $dish->id }})" 
                                class="bg-yellow-500 text-white px-3 py-2 rounded hover:bg-yellow-600 text-sm">
                            {{ $dish->active ? '❌' : '✅' }}
                        </button>
                        <button onclick="deleteDish({{ $dish->id }})" 
                                class="bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600 text-sm">
                            🗑️
                        </button>
                    </div>
                </div>
                @endforeach

                <!-- Carte nouveau plat -->
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors cursor-pointer"
                     onclick="openDishModal()">
                    <div class="py-8">
                        <i class="fas fa-plus text-4xl text-gray-400 mb-3"></i>
                        <div class="text-gray-600 font-medium">Nouveau Plat</div>
                        <div class="text-sm text-gray-500 mt-1">Cliquez pour créer un nouveau plat</div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Nouveau/Édition Plat -->
    <div id="dishModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <h3 id="modalDishTitle" class="text-xl font-bold mb-4">Nouveau Plat</h3>
            <form id="dishForm">
                <input type="hidden" id="dishId" name="id">
                
                <!-- Informations de base -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Nom du plat:</label>
                        <input type="text" id="dishName" name="name" required 
                               class="w-full border rounded px-3 py-2" placeholder="Ex: Salade César">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Prix de base (FCFA):</label>
                        <input type="number" id="dishPrice" name="price_base" required min="0" step="100"
                               class="w-full border rounded px-3 py-2" placeholder="Ex: 4500">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Description:</label>
                    <textarea id="dishDescription" name="description" 
                              class="w-full border rounded px-3 py-2" rows="3" 
                              placeholder="Description du plat..."></textarea>
                </div>

                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" id="dishActive" name="active" checked class="mr-2">
                        <span>Plat actif (visible dans le menu)</span>
                    </label>
                </div>

                <!-- Variantes -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="font-semibold">📏 Variantes (tailles, portions)</h4>
                        <button type="button" onclick="addVariant()" class="bg-blue-500 text-white px-3 py-1 rounded text-sm">
                            + Ajouter
                        </button>
                    </div>
                    <div id="variantsContainer" class="space-y-2">
                        <!-- Les variantes seront ajoutées ici dynamiquement -->
                    </div>
                </div>

                <!-- Options -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="font-semibold">⚙️ Options de personnalisation</h4>
                        <button type="button" onclick="addOption()" class="bg-blue-500 text-white px-3 py-1 rounded text-sm">
                            + Ajouter
                        </button>
                    </div>
                    <div id="optionsContainer" class="space-y-2">
                        <!-- Les options seront ajoutées ici dynamiquement -->
                    </div>
                </div>

                <div class="flex space-x-2">
                    <button type="button" onclick="closeDishModal()" 
                            class="flex-1 bg-gray-500 text-white py-2 rounded hover:bg-gray-600">
                        Annuler
                    </button>
                    <button type="submit" id="submitDishButton"
                            class="flex-1 bg-blue-500 text-white py-2 rounded hover:bg-blue-600">
                        Créer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentEditingDish = null;
        let variantCount = 0;
        let optionCount = 0;

        // Fonction pour récupérer le token CSRF
        function getCsrfToken() {
            const token = document.querySelector('meta[name="csrf-token"]');
            if (!token) {
                console.error('CSRF token not found!');
                return null;
            }
            return token.getAttribute('content');
        }

        // Ouvrir modal nouveau plat
        function openDishModal() {
            currentEditingDish = null;
            document.getElementById('modalDishTitle').textContent = 'Nouveau Plat';
            document.getElementById('submitDishButton').textContent = 'Créer';
            document.getElementById('dishId').value = '';
            document.getElementById('dishName').value = '';
            document.getElementById('dishPrice').value = '';
            document.getElementById('dishDescription').value = '';
            document.getElementById('dishActive').checked = true;
            
            // Réinitialiser variantes et options
            document.getElementById('variantsContainer').innerHTML = '';
            document.getElementById('optionsContainer').innerHTML = '';
            variantCount = 0;
            optionCount = 0;
            
            document.getElementById('dishModal').classList.remove('hidden');
        }

        // Éditer un plat
        async function editDish(dishId) {
            try {
                const response = await fetch(`/admin/{{ $tenant->slug }}/dishes/${dishId}`);
                const result = await response.json();
                
                if (result.success) {
                    currentEditingDish = result.dish;
                    document.getElementById('modalDishTitle').textContent = 'Modifier le Plat';
                    document.getElementById('submitDishButton').textContent = 'Modifier';
                    document.getElementById('dishId').value = result.dish.id;
                    document.getElementById('dishName').value = result.dish.name;
                    document.getElementById('dishPrice').value = result.dish.price_base;
                    document.getElementById('dishDescription').value = result.dish.description || '';
                    document.getElementById('dishActive').checked = result.dish.active;
                    
                    // Charger les variantes
                    document.getElementById('variantsContainer').innerHTML = '';
                    variantCount = 0;
                    result.dish.variants.forEach(variant => {
                        addVariant(variant.name, variant.extra_price);
                    });
                    
                    // Charger les options
                    document.getElementById('optionsContainer').innerHTML = '';
                    optionCount = 0;
                    result.dish.options.forEach(option => {
                        addOption(option.name, option.kind, option.extra_price);
                    });
                    
                    document.getElementById('dishModal').classList.remove('hidden');
                } else {
                    alert('Erreur: ' + result.message);
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur réseau: ' + error.message);
            }
        }

        // Fermer modal
        function closeDishModal() {
            document.getElementById('dishModal').classList.add('hidden');
        }

        // Ajouter une variante
        function addVariant(name = '', extraPrice = 0) {
            variantCount++;
            const variantHtml = `
                <div class="flex items-center space-x-2 p-2 border rounded variant-item">
                    <input type="text" name="variants[${variantCount}][name]" 
                           value="${name}" placeholder="Nom (ex: Grand, Moyen...)" 
                           class="flex-1 border rounded px-2 py-1 text-sm">
                    <input type="number" name="variants[${variantCount}][extra_price]" 
                           value="${extraPrice}" placeholder="Supplément" min="0" step="100"
                           class="w-24 border rounded px-2 py-1 text-sm">
                    <button type="button" onclick="this.parentElement.remove()" 
                            class="bg-red-500 text-white px-2 py-1 rounded text-sm">
                        ❌
                    </button>
                </div>
            `;
            document.getElementById('variantsContainer').insertAdjacentHTML('beforeend', variantHtml);
        }

        // Ajouter une option
        function addOption(name = '', kind = 'toggle', extraPrice = 0) {
            optionCount++;
            const optionHtml = `
                <div class="flex items-center space-x-2 p-2 border rounded option-item">
                    <input type="text" name="options[${optionCount}][name]" 
                           value="${name}" placeholder="Nom (ex: Sans gluten, Extra sauce...)" 
                           class="flex-1 border rounded px-2 py-1 text-sm">
                    <select name="options[${optionCount}][kind]" class="border rounded px-2 py-1 text-sm">
                        <option value="toggle" ${kind === 'toggle' ? 'selected' : ''}>Toggle</option>
                        <option value="multiple" ${kind === 'multiple' ? 'selected' : ''}>Multiple</option>
                    </select>
                    <input type="number" name="options[${optionCount}][extra_price]" 
                           value="${extraPrice}" placeholder="Supplément" min="0" step="100"
                           class="w-24 border rounded px-2 py-1 text-sm">
                    <button type="button" onclick="this.parentElement.remove()" 
                            class="bg-red-500 text-white px-2 py-1 rounded text-sm">
                        ❌
                    </button>
                </div>
            `;
            document.getElementById('optionsContainer').insertAdjacentHTML('beforeend', optionHtml);
        }

        // Soumission du formulaire avec CSRF
        document.getElementById('dishForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const csrfToken = getCsrfToken();
            if (!csrfToken) {
                alert('Erreur: Token CSRF manquant');
                return;
            }
            
            const formData = new FormData(this);
            const data = {
                name: formData.get('name'),
                description: formData.get('description'),
                price_base: parseFloat(formData.get('price_base')),
                active: formData.get('active') ? true : false,
                variants: [],
                options: []
            };

            // Récupérer les variantes
            document.querySelectorAll('.variant-item').forEach(item => {
                const name = item.querySelector('input[name$="[name]"]').value;
                const extraPrice = parseFloat(item.querySelector('input[name$="[extra_price]"]').value) || 0;
                if (name.trim()) {
                    data.variants.push({ name, extra_price: extraPrice });
                }
            });

            // Récupérer les options
            document.querySelectorAll('.option-item').forEach(item => {
                const name = item.querySelector('input[name$="[name]"]').value;
                const kind = item.querySelector('select[name$="[kind]"]').value;
                const extraPrice = parseFloat(item.querySelector('input[name$="[extra_price]"]').value) || 0;
                if (name.trim()) {
                    data.options.push({ name, kind, extra_price: extraPrice });
                }
            });

            const url = currentEditingDish ? `/admin/{{ $tenant->slug }}/dishes/${currentEditingDish.id}` : `/admin/{{ $tenant->slug }}/categories/{{ $category->id }}/dishes`;
            const method = currentEditingDish ? 'PUT' : 'POST';

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
                    alert(currentEditingDish ? 'Plat modifié avec succès!' : 'Plat créé avec succès!');
                    closeDishModal();
                    location.reload();
                } else {
                    alert('Erreur: ' + (result.message || 'Erreur inconnue'));
                }
            } catch (error) {
                console.error('Erreur complète:', error);
                alert('Erreur réseau: ' + error.message);
            }
        });

        // Toggle activation plat avec CSRF
        async function toggleDish(dishId) {
            const csrfToken = getCsrfToken();
            if (!csrfToken) {
                alert('Erreur: Token CSRF manquant');
                return;
            }

            try {
                const response = await fetch(`/admin/{{ $tenant->slug }}/dishes/${dishId}/toggle`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const result = await response.json();
                
                if (result.success) {
                    alert('Statut du plat mis à jour!');
                    location.reload();
                } else {
                    alert('Erreur: ' + result.message);
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur réseau: ' + error.message);
            }
        }

        // Supprimer un plat avec CSRF
        async function deleteDish(dishId) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer ce plat ? Cette action est irréversible.')) {
                return;
            }

            const csrfToken = getCsrfToken();
            if (!csrfToken) {
                alert('Erreur: Token CSRF manquant');
                return;
            }

            try {
                const response = await fetch(`/admin/{{ $tenant->slug }}/dishes/${dishId}`, {
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
                    alert('Plat supprimé avec succès!');
                    location.reload();
                } else {
                    alert('Erreur: ' + result.message);
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur réseau: ' + error.message);
            }
        }
    </script>
</body>
</html>