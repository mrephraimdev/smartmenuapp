<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KDS - Cuisine</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-900 text-white">
    <div class="container mx-auto px-4 py-6">
        <!-- En-tête -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">👨‍🍳 Tableau des Commandes - KDS</h1>
            <div class="flex space-x-4">
                <button onclick="loadOrders()" class="bg-blue-500 px-4 py-2 rounded hover:bg-blue-600">
                    🔄 Actualiser
                </button>
                <button onclick="toggleAutoRefresh()" id="autoRefreshBtn" class="bg-green-500 px-4 py-2 rounded hover:bg-green-600">
                    🔄 Auto: ON
                </button>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-gray-800 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-yellow-400" id="count-recu">0</div>
                <div class="text-sm text-gray-400">En Attente</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-blue-400" id="count-prep">0</div>
                <div class="text-sm text-gray-400">En Préparation</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-green-400" id="count-pret">0</div>
                <div class="text-sm text-gray-400">Prêts</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-gray-400" id="count-servi">0</div>
                <div class="text-sm text-gray-400">Servis</div>
            </div>
        </div>

        <!-- Tableau des commandes -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Colonne Reçu -->
            <div class="bg-gray-800 rounded-lg p-4">
                <h2 class="text-xl font-bold mb-4 text-yellow-400">🟡 Reçu</h2>
                <div id="received-orders" class="space-y-4 min-h-32">
                    <div class="text-center text-gray-500 py-8">Aucune commande</div>
                </div>
            </div>

            <!-- Colonne En Préparation -->
            <div class="bg-gray-800 rounded-lg p-4">
                <h2 class="text-xl font-bold mb-4 text-blue-400">🔵 En Préparation</h2>
                <div id="preparation-orders" class="space-y-4 min-h-32">
                    <div class="text-center text-gray-500 py-8">Aucune commande</div>
                </div>
            </div>

            <!-- Colonne Prêt -->
            <div class="bg-gray-800 rounded-lg p-4">
                <h2 class="text-xl font-bold mb-4 text-green-400">🟢 Prêt</h2>
                <div id="ready-orders" class="space-y-4 min-h-32">
                    <div class="text-center text-gray-500 py-8">Aucune commande</div>
                </div>
            </div>

            <!-- Colonne Servi -->
            <div class="bg-gray-800 rounded-lg p-4">
                <h2 class="text-xl font-bold mb-4 text-gray-400">⚪ Servi</h2>
                <div id="served-orders" class="space-y-4 min-h-32">
                    <div class="text-center text-gray-500 py-8">Aucune commande</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let autoRefresh = true;
        let autoRefreshInterval;

        // Mapping des statuts vers les IDs des colonnes
        function getColumnIdByStatus(status) {
            const statusMap = {
                'RECU': 'received-orders',
                'PREP': 'preparation-orders',
                'PRET': 'ready-orders',
                'SERVI': 'served-orders'
            };
            return statusMap[status] || null;
        }

        // Démarrer l'actualisation automatique
        function startAutoRefresh() {
            autoRefreshInterval = setInterval(loadOrders, 3000); // Toutes les 3 secondes
        }

        // Basculer l'actualisation automatique
        function toggleAutoRefresh() {
            autoRefresh = !autoRefresh;
            const btn = document.getElementById('autoRefreshBtn');
            
            if (autoRefresh) {
                btn.textContent = '🔄 Auto: ON';
                btn.className = 'bg-green-500 px-4 py-2 rounded hover:bg-green-600';
                startAutoRefresh();
            } else {
                btn.textContent = '🔴 Auto: OFF';
                btn.className = 'bg-red-500 px-4 py-2 rounded hover:bg-red-600';
                clearInterval(autoRefreshInterval);
            }
        }

        // Charger les commandes
        async function loadOrders() {
            try {
                console.log('🔄 Chargement des commandes...');
                const response = await fetch('/api/orders?tenantId={{ $tenantId }}');

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const orders = await response.json();
                console.log(`📊 ${orders.length} commandes chargées pour tenant {{ $tenantId }}:`, orders);

                updateOrderColumns(orders);
                updateStats(orders);
            } catch (error) {
                console.error('❌ Erreur chargement commandes:', error);
            }
        }

        // Mettre à jour les statistiques
        function updateStats(orders) {
            const counts = {
                'RECU': 0,
                'PREP': 0,
                'PRET': 0,
                'SERVI': 0
            };

            orders.forEach(order => {
                counts[order.status] = (counts[order.status] || 0) + 1;
            });

            document.getElementById('count-recu').textContent = counts.RECU;
            document.getElementById('count-prep').textContent = counts.PREP;
            document.getElementById('count-pret').textContent = counts.PRET;
            document.getElementById('count-servi').textContent = counts.SERVI;

            console.log('📈 Statistiques:', counts);
        }

        // Mettre à jour les colonnes
        function updateOrderColumns(orders) {
            // Réinitialiser les colonnes
            const columns = {
                'RECU': document.getElementById('received-orders'),
                'PREP': document.getElementById('preparation-orders'),
                'PRET': document.getElementById('ready-orders'),
                'SERVI': document.getElementById('served-orders')
            };

            Object.values(columns).forEach(column => {
                column.innerHTML = '';
            });

            // Trier les commandes par date (plus récentes en premier)
            orders.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

            orders.forEach(order => {
                const orderElement = createOrderElement(order);
                const columnId = getColumnIdByStatus(order.status);
                const column = document.getElementById(columnId);
                
                if (column) {
                    column.appendChild(orderElement);
                } else {
                    console.warn(`Colonne non trouvée pour le statut: ${order.status}, ID: ${columnId}`);
                }
            });

            // Afficher message si colonne vide
            Object.entries(columns).forEach(([status, column]) => {
                if (column.children.length === 0) {
                    column.innerHTML = '<div class="text-center text-gray-500 py-8">Aucune commande</div>';
                }
            });
        }

        // Créer un élément de commande
        function createOrderElement(order) {
            const div = document.createElement('div');
            div.className = 'bg-gray-700 rounded p-3 border-l-4';
            
            // Couleur de la bordure selon le statut
            const borderColors = {
                'RECU': 'border-yellow-400',
                'PREP': 'border-blue-400',
                'PRET': 'border-green-400',
                'SERVI': 'border-gray-400'
            };
            
            div.className += ` ${borderColors[order.status]}`;

            const time = new Date(order.created_at).toLocaleTimeString('fr-FR', {
                hour: '2-digit',
                minute: '2-digit'
            });

            div.innerHTML = `
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <div class="font-bold">Table ${order.table.code}</div>
                        <div class="text-sm text-gray-300">#${order.id} • ${time}</div>
                    </div>
                    <div class="text-right">
                        <div class="font-bold">${order.total} FCFA</div>
                    </div>
                </div>
                <div class="mb-3">
                    ${order.items.map(item => `
                        <div class="text-sm mb-1">
                            <div class="flex justify-between">
                                <span>• ${item.dish.name} x${item.quantity}</span>
                                <span class="text-gray-400">${item.unit_price * item.quantity} FCFA</span>
                            </div>
                            ${item.variant ? `<div class="text-xs text-gray-400 ml-2">${item.variant.name}</div>` : ''}
                            ${item.notes ? `<div class="text-xs text-gray-400 ml-2">📝 ${item.notes}</div>` : ''}
                        </div>
                    `).join('')}
                </div>
                <div class="flex space-x-2">
                    ${order.status === 'RECU' ? `
                        <button onclick="updateStatus(${order.id}, 'PREP')" 
                                class="flex-1 bg-blue-500 text-white px-2 py-1 rounded text-sm hover:bg-blue-600">
                            Préparer
                        </button>
                    ` : ''}
                    ${order.status === 'PREP' ? `
                        <button onclick="updateStatus(${order.id}, 'PRET')" 
                                class="flex-1 bg-green-500 text-white px-2 py-1 rounded text-sm hover:bg-green-600">
                            Prêt
                        </button>
                    ` : ''}
                    ${order.status === 'PRET' ? `
                        <button onclick="updateStatus(${order.id}, 'SERVI')" 
                                class="flex-1 bg-gray-500 text-white px-2 py-1 rounded text-sm hover:bg-gray-600">
                            Servi
                        </button>
                    ` : ''}
                </div>
            `;
            return div;
        }

        // Mettre à jour le statut
        async function updateStatus(orderId, newStatus) {
            try {
                console.log(`🔄 Mise à jour statut commande ${orderId} → ${newStatus}`);

                const response = await fetch(`/api/orders/${orderId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ status: newStatus })
                });

                const result = await response.json();
                console.log('📥 Réponse mise à jour:', result);

                if (result.success) {
                    loadOrders(); // Recharger les commandes
                } else {
                    alert('❌ Erreur: ' + result.message);
                }
            } catch (error) {
                console.error('❌ Erreur mise à jour:', error);
                alert('❌ Erreur lors de la mise à jour');
            }
        }

        // Charger les commandes au démarrage et démarrer l'auto-refresh
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 KDS démarré');
            loadOrders();
            startAutoRefresh();
        });
    </script>
</body>
</html>