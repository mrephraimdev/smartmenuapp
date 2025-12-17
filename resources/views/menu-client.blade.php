<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <meta name="csrf-token" content="{{ csrf_token() }}"> 
    <title>Menu Restaurant</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style id="theme-styles">
        /* Styles dynamiques du thème seront injectés ici */
    </style>
    <style>
        /* Animations et transitions améliorées */
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }

        .slide-up {
            animation: slideUp 0.3s ease-out;
        }

        .bounce-in {
            animation: bounceIn 0.4s ease-out;
        }

        .scale-in {
            animation: scaleIn 0.2s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.05); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); opacity: 1; }
        }

        @keyframes scaleIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        /* Loading spinner */
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive improvements */
        @media (max-width: 640px) {
            .cart-mobile {
                width: calc(100vw - 2rem);
                left: 1rem;
                right: 1rem;
                bottom: 1rem;
            }
        }

        /* Smooth transitions */
        * {
            transition: all 0.2s ease;
        }

        .dish-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .dish-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .category-btn {
            transition: all 0.2s ease;
        }

        .category-btn.active {
            background-color: var(--theme-primary);
            color: white;
            transform: scale(1.05);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header avec branding -->
    <header id="header" class="bg-white shadow-sm">
        <div class="container mx-auto px-4 py-4">
            <div class="text-center">
                <h1 id="restaurantName" class="text-2xl font-bold text-gray-800">Chargement...</h1>
                <p id="tableInfo" class="text-gray-600 mt-1">Table: ...</p>
            </div>
        </div>
    </header>

    <!-- Panier flottant -->
    <div id="cart" class="fixed bottom-4 right-4 bg-white rounded-lg shadow-lg p-4 w-80 hidden">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold">Votre Panier</h3>
            <button onclick="closeCart()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="cartItems" class="max-h-60 overflow-y-auto mb-4">
            <!-- Les items du panier apparaîtront ici -->
        </div>
        <div class="border-t pt-2">
            <div class="flex justify-between font-bold mb-2">
                <span>Total:</span>
                <span id="cartTotal">0 FCFA</span>
            </div>
            <button onclick="submitOrder()" class="w-full bg-green-500 text-white py-2 rounded-lg hover:bg-green-600">
                Commander
            </button>
        </div>
    </div>

    <!-- Menu principal -->
    <main class="container mx-auto px-4 py-6">
        <!-- Catégories -->
        <div id="categories" class="flex overflow-x-auto space-x-2 mb-6 pb-2">
            <!-- Les catégories seront chargées ici -->
        </div>

        <!-- Liste des plats -->
        <div id="dishes" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Les plats seront chargés ici -->
        </div>

        <!-- Message vide -->
        <div id="emptyMessage" class="text-center py-12 hidden">
            <i class="fas fa-utensils text-4xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">Aucun plat disponible pour le moment.</p>
        </div>
    </main>

    <!-- Bouton panier flottant -->
    <button id="cartButton" onclick="toggleCart()" 
            class="fixed bottom-4 left-4 bg-blue-500 text-white p-4 rounded-full shadow-lg hover:bg-blue-600">
        <i class="fas fa-shopping-cart"></i>
        <span id="cartCount" class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-6 h-6 text-sm flex items-center justify-center">0</span>
    </button>

    <!-- Modal de personnalisation -->
    <div id="customizeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg p-6 w-96 max-h-[90vh] overflow-y-auto">
            <h3 id="modalDishName" class="text-xl font-bold mb-4"></h3>
            
            <!-- Variantes -->
            <div id="variantsSection" class="mb-4 hidden">
                <h4 class="font-semibold mb-2">Taille:</h4>
                <div id="variantsList"></div>
            </div>

            <!-- Options -->
            <div id="optionsSection" class="mb-4 hidden">
                <h4 class="font-semibold mb-2">Options:</h4>
                <div id="optionsList"></div>
            </div>

            <!-- Notes -->
            <div class="mb-4">
                <label class="block font-semibold mb-2">Notes spéciales:</label>
                <textarea id="dishNotes" placeholder="Ex: Sans gluten, moins salé..." 
                         class="w-full border rounded px-3 py-2"></textarea>
            </div>

            <!-- Quantité -->
            <div class="mb-4">
                <label class="block font-semibold mb-2">Quantité:</label>
                <div class="flex items-center space-x-4">
                    <button onclick="changeQuantity(-1)" class="bg-gray-200 w-8 h-8 rounded-full">-</button>
                    <span id="quantityDisplay" class="font-bold">1</span>
                    <button onclick="changeQuantity(1)" class="bg-gray-200 w-8 h-8 rounded-full">+</button>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex space-x-2">
                <button onclick="closeModal()" class="flex-1 bg-gray-500 text-white py-2 rounded hover:bg-gray-600">
                    Annuler
                </button>
                <button onclick="addToCart()" class="flex-1 bg-blue-500 text-white py-2 rounded hover:bg-blue-600">
                    Ajouter
                </button>
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        let currentDish = null;
        let currentQuantity = 1;
        let selectedVariant = null;
        let selectedOptions = [];
        let cart = [];
        let tenantData = null;
        let tableData = null;

        // Charger le menu au démarrage
        document.addEventListener('DOMContentLoaded', function() {
            loadMenu();
        });

        // Charger le menu depuis l'API
        async function loadMenu() {
            const urlParams = new URLSearchParams(window.location.search);
            const tenantId = urlParams.get('tenant') || '1';
            const tableCode = urlParams.get('table') || 'A1';

            try {
                const response = await fetch(`/api/menu?tenant=${tenantId}&table=${tableCode}`);
                const data = await response.json();

                if (data.success) {
                    tenantData = data.tenant;
                    tableData = data.table;
                    displayMenu(data.menu);
                    updateBranding();
                } else {
                    showError('Erreur lors du chargement du menu');
                }
            } catch (error) {
                showError('Impossible de charger le menu: ' + error.message);
            }
        }

        // Afficher le menu
        function displayMenu(menu) {
            if (!menu || !menu.categories || menu.categories.length === 0) {
                document.getElementById('emptyMessage').classList.remove('hidden');
                return;
            }

            // Afficher les catégories
            const categoriesContainer = document.getElementById('categories');
            categoriesContainer.innerHTML = menu.categories.map(category => `
                <button onclick="filterByCategory(${category.id})" 
                        class="category-btn whitespace-nowrap px-4 py-2 bg-white border rounded-lg hover:bg-gray-50">
                    ${category.name}
                </button>
            `).join('');

            // Afficher tous les plats
            displayDishes(menu.categories.flatMap(cat => cat.dishes || []));
        }

        // Afficher les plats
        function displayDishes(dishes) {
            const dishesContainer = document.getElementById('dishes');
            
            if (dishes.length === 0) {
                document.getElementById('emptyMessage').classList.remove('hidden');
                dishesContainer.innerHTML = '';
                return;
            }

            document.getElementById('emptyMessage').classList.add('hidden');
            
            dishesContainer.innerHTML = dishes.map(dish => `
                <div class="bg-white rounded-lg shadow-sm border hover:shadow-md transition-shadow">
                    ${dish.photo_url ? `
                        <img src="${dish.photo_url}" alt="${dish.name}" 
                             class="w-full h-48 object-cover rounded-t-lg">
                    ` : `
                        <div class="w-full h-48 bg-gray-200 rounded-t-lg flex items-center justify-center">
                            <i class="fas fa-utensils text-3xl text-gray-400"></i>
                        </div>
                    `}
                    <div class="p-4">
                        <h3 class="font-bold text-lg mb-1">${dish.name}</h3>
                        <p class="text-gray-600 text-sm mb-2">${dish.description || ''}</p>
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-green-600">${dish.price_base} FCFA</span>
                            <button onclick="openCustomizeModal(${JSON.stringify(dish).replace(/"/g, '&quot;')})" 
                                    class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 text-sm">
                                Ajouter
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Ouvrir le modal de personnalisation
        function openCustomizeModal(dish) {
            currentDish = dish;
            currentQuantity = 1;
            selectedVariant = null;
            selectedOptions = [];

            document.getElementById('modalDishName').textContent = dish.name;
            
            // Gérer les variantes
            const variantsSection = document.getElementById('variantsSection');
            const variantsList = document.getElementById('variantsList');
            
            if (dish.variants && dish.variants.length > 0) {
                variantsSection.classList.remove('hidden');
                variantsList.innerHTML = dish.variants.map(variant => `
                    <label class="flex items-center space-x-2 mb-2">
                        <input type="radio" name="variant" value="${variant.id}" 
                               onchange="selectVariant(${variant.id})" 
                               class="text-blue-500">
                        <span>${variant.name} (+${variant.extra_price} FCFA)</span>
                    </label>
                `).join('');
                // Sélectionner la première variante par défaut
                if (dish.variants[0]) {
                    selectedVariant = dish.variants[0];
                    document.querySelector('input[name="variant"]').checked = true;
                }
            } else {
                variantsSection.classList.add('hidden');
            }

            // Gérer les options
            const optionsSection = document.getElementById('optionsSection');
            const optionsList = document.getElementById('optionsList');
            
            if (dish.options && dish.options.length > 0) {
                optionsSection.classList.remove('hidden');
                optionsList.innerHTML = dish.options.map(option => `
                    <label class="flex items-center space-x-2 mb-2">
                        <input type="checkbox" value="${option.id}" 
                               onchange="toggleOption(${option.id}, ${option.extra_price})"
                               class="text-blue-500">
                        <span>${option.name} ${option.extra_price > 0 ? `(+${option.extra_price} FCFA)` : ''}</span>
                    </label>
                `).join('');
            } else {
                optionsSection.classList.add('hidden');
            }

            document.getElementById('quantityDisplay').textContent = currentQuantity;
            document.getElementById('dishNotes').value = '';
            document.getElementById('customizeModal').classList.remove('hidden');
        }

        // Fermer le modal
        function closeModal() {
            document.getElementById('customizeModal').classList.add('hidden');
        }

        // Sélectionner une variante
        function selectVariant(variantId) {
            selectedVariant = currentDish.variants.find(v => v.id === variantId);
        }

        // Basculer une option
        function toggleOption(optionId, extraPrice) {
            const option = currentDish.options.find(o => o.id === optionId);
            const index = selectedOptions.findIndex(o => o.id === optionId);
            
            if (index > -1) {
                selectedOptions.splice(index, 1);
            } else {
                selectedOptions.push(option);
            }
        }

        // Changer la quantité
        function changeQuantity(change) {
            currentQuantity = Math.max(1, currentQuantity + change);
            document.getElementById('quantityDisplay').textContent = currentQuantity;
        }

        // Ajouter au panier
        function addToCart() {
            const notes = document.getElementById('dishNotes').value;
            
            const cartItem = {
                dish: currentDish,
                variant: selectedVariant,
                options: selectedOptions,
                quantity: currentQuantity,
                notes: notes,
                unitPrice: calculateUnitPrice()
            };

            cart.push(cartItem);
            updateCart();
            closeModal();
        }

        // Calculer le prix unitaire
        function calculateUnitPrice() {
            let price = currentDish.price_base;
            
            if (selectedVariant) {
                price += selectedVariant.extra_price;
            }
            
            selectedOptions.forEach(option => {
                price += option.extra_price;
            });
            
            return price;
        }

        // Mettre à jour le panier
        function updateCart() {
            const cartCount = document.getElementById('cartCount');
            const cartItems = document.getElementById('cartItems');
            const cartTotal = document.getElementById('cartTotal');
            
            // Mettre à jour le compteur
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            cartCount.textContent = totalItems;
            
            // Mettre à jour la liste des items
            cartItems.innerHTML = cart.map((item, index) => `
                <div class="flex justify-between items-center mb-2 pb-2 border-b">
                    <div class="flex-1">
                        <div class="font-medium">${item.dish.name}</div>
                        <div class="text-sm text-gray-600">
                            ${item.variant ? item.variant.name + ' • ' : ''}
                            Quantité: ${item.quantity}
                        </div>
                        ${item.notes ? `<div class="text-xs text-gray-500">${item.notes}</div>` : ''}
                    </div>
                    <div class="text-right">
                        <div class="font-medium">${item.unitPrice * item.quantity} FCFA</div>
                        <button onclick="removeFromCart(${index})" class="text-red-500 text-sm hover:text-red-700">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `).join('');
            
            // Mettre à jour le total
            const total = cart.reduce((sum, item) => sum + (item.unitPrice * item.quantity), 0);
            cartTotal.textContent = total + ' FCFA';
        }

        // Retirer du panier
        function removeFromCart(index) {
            cart.splice(index, 1);
            updateCart();
        }

        // Basculer l'affichage du panier
        function toggleCart() {
            const cartElement = document.getElementById('cart');
            cartElement.classList.toggle('hidden');
        }

        // Fermer le panier
        function closeCart() {
            document.getElementById('cart').classList.add('hidden');
        }

        // Soumettre la commande - VERSION CORRECTE
        async function submitOrder() {
    if (cart.length === 0) {
        alert('❌ Votre panier est vide');
        return;
    }

    console.log('🛒 Contenu du panier:', cart);

    // Préparer les données
    const orderData = {
        tenant_id: tenantData.id,
        table_id: tableData.id,
        items: cart.map(item => {
            return {
                dish_id: item.dish.id,
                quantity: item.quantity,
                variant_id: item.variant ? item.variant.id : null,
                options: item.options ? item.options.map(opt => opt.name) : [],
                notes: item.notes || ''
            };
        }),
        notes: 'Commande depuis l\'interface web'
    };

    console.log('📤 Envoi vers /orders:', orderData);

    try {
        // ✅ AJOUTER CES LIGNES
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const response = await fetch('api/orders', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken  // ✅ LE TOKEN ICI
            },
            body: JSON.stringify(orderData)
        });

        const result = await response.json();

        if (result.success) {
            alert(`✅ Commande #${result.order_id} créée avec succès!\nTotal: ${result.total} FCFA`);
            cart = [];
            updateCart();
            closeCart();
        } else {
            alert(`❌ Erreur: ${result.message}`);
        }

    } catch (error) {
        console.error('💥 Erreur:', error);
        alert('❌ Erreur réseau lors de l\'envoi de la commande');
    }
}       
 
// Filtrer par catégorie
        function filterByCategory(categoryId) {
            // Implémentation simple du filtrage
            const buttons = document.querySelectorAll('.category-btn');
            buttons.forEach(btn => btn.classList.remove('bg-blue-500', 'text-white'));
            event.target.classList.add('bg-blue-500', 'text-white');
            
            // Recharger les plats filtrés (à améliorer)
            loadMenu();
        }

        // Mettre à jour le branding
        async function updateBranding() {
            if (tenantData) {
                document.getElementById('restaurantName').textContent = tenantData.name;
                document.getElementById('tableInfo').textContent = `Table: ${tableData.code}`;

                // Charger le thème depuis l'API
                try {
                    const themeResponse = await fetch(`/api/tenants/${tenantData.id}/theme`);
                    const themeData = await themeResponse.json();

                    if (themeData.theme) {
                        applyTheme(themeData.theme);
                    }
                } catch (error) {
                    console.warn('Erreur lors du chargement du thème:', error);
                    // Fallback vers l'ancien système de branding
                    if (tenantData.branding && tenantData.branding.primaryColor) {
                        document.documentElement.style.setProperty('--primary-color', tenantData.branding.primaryColor);
                        document.querySelector('header').style.backgroundColor = tenantData.branding.primaryColor;
                        document.querySelector('header').style.color = '#fff';
                    }
                }
            }
        }

        // Appliquer le thème dynamique
        function applyTheme(theme) {
            const styleElement = document.getElementById('theme-styles');

            // Charger les polices Google si nécessaire
            if (theme.fonts) {
                loadGoogleFonts(theme.fonts);
            }

            // Appliquer les variables CSS
            const cssVariables = `
                :root {
                    --theme-primary: ${theme.colors.primary};
                    --theme-secondary: ${theme.colors.secondary};
                    --theme-accent: ${theme.colors.accent};
                    --theme-background: ${theme.colors.background};
                    --theme-text: ${theme.colors.text};
                    --theme-heading-font: '${theme.fonts.heading}', sans-serif;
                    --theme-body-font: '${theme.fonts.body}', sans-serif;
                }

                /* Appliquer les couleurs du thème */
                .bg-primary { background-color: var(--theme-primary) !important; }
                .text-primary { color: var(--theme-primary) !important; }
                .bg-secondary { background-color: var(--theme-secondary) !important; }
                .text-secondary { color: var(--theme-secondary) !important; }
                .bg-accent { background-color: var(--theme-accent) !important; }
                .text-accent { color: var(--theme-accent) !important; }
                .bg-theme { background-color: var(--theme-background) !important; }
                .text-theme { color: var(--theme-text) !important; }

                /* Appliquer les polices */
                .font-heading { font-family: var(--theme-heading-font) !important; }
                .font-body { font-family: var(--theme-body-font) !important; }

                /* Header avec branding */
                #header {
                    background-color: var(--theme-primary) !important;
                    color: white !important;
                }

                /* Boutons principaux */
                .bg-blue-500 { background-color: var(--theme-primary) !important; }
                .hover\\:bg-blue-600:hover { background-color: var(--theme-secondary) !important; }
                .text-blue-500 { color: var(--theme-primary) !important; }

                /* Boutons d'accent */
                .bg-green-500 { background-color: var(--theme-accent) !important; }
                .hover\\:bg-green-600:hover { background-color: var(--theme-secondary) !important; }
                .text-green-600 { color: var(--theme-accent) !important; }

                /* Corps de la page */
                body { background-color: var(--theme-background) !important; color: var(--theme-text) !important; }
            `;

            styleElement.textContent = cssVariables;
        }

        // Charger les polices Google Fonts
        function loadGoogleFonts(fonts) {
            const existingLink = document.querySelector('link[data-google-fonts]');
            if (existingLink) {
                existingLink.remove();
            }

            const fontFamilies = [];
            if (fonts.heading) fontFamilies.push(`family=${encodeURIComponent(fonts.heading)}:wght@400;700`);
            if (fonts.body && fonts.body !== fonts.heading) fontFamilies.push(`family=${encodeURIComponent(fonts.body)}:wght@400;500;600`);

            if (fontFamilies.length > 0) {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = `https://fonts.googleapis.com/css2?${fontFamilies.join('&')}&display=swap`;
                link.setAttribute('data-google-fonts', 'true');
                document.head.appendChild(link);
            }
        }

        // Afficher une erreur
        function showError(message) {
            const dishesContainer = document.getElementById('dishes');
            dishesContainer.innerHTML = `
                <div class="col-span-full text-center py-12">
                    <i class="fas fa-exclamation-triangle text-4xl text-red-300 mb-4"></i>
                    <p class="text-red-500">${message}</p>
                    <button onclick="loadMenu()" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Réessayer
                    </button>
                </div>
            `;
        }
    </script>
</body>
</html>   