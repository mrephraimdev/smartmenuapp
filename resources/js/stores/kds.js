/**
 * KDS Store - Alpine.js
 *
 * Gère l'état du Kitchen Display System :
 * - Auto-refresh des commandes (polling 3s)
 * - Mise à jour des statuts (RECU → PREP → PRET → SERVI)
 * - Notifications sonores pour nouvelles commandes
 * - Statistiques en temps réel
 */

export default function kdsStore() {
    return {
        // État principal
        orders: [],
        autoRefresh: true,
        refreshInterval: null,
        lastOrderCount: 0,
        isLoading: false,
        showNotification: false,
        notificationMessage: '',

        // Slug du tenant (passé depuis Blade)
        tenantSlug: '',

        // Lifecycle - appelé automatiquement par Alpine
        init() {
            // Ne rien faire ici, le tenantSlug n'est pas encore défini
            // L'initialisation se fait via startKds() appelé depuis x-init
        },

        // Démarrage réel du KDS (appelé depuis x-init après que tenantSlug soit défini)
        startKds() {
            if (!this.tenantSlug) {
                console.error('tenantSlug non défini');
                return;
            }
            console.log('KDS démarré pour:', this.tenantSlug);
            this.fetchOrders();
            this.startAutoRefresh();
        },

        // Fetch commandes depuis l'API
        async fetchOrders() {
            this.isLoading = true;
            try {
                const response = await fetch(`/api/orders/tenant/${this.tenantSlug}`);
                if (!response.ok) throw new Error(`HTTP ${response.status}`);

                const orders = await response.json();
                const newCount = orders.length;

                // Notification si nouvelle commande
                if (newCount > this.lastOrderCount && this.lastOrderCount > 0) {
                    this.showNewOrderNotification(newCount - this.lastOrderCount);
                    this.playNotificationSound();
                }

                this.lastOrderCount = newCount;
                this.orders = orders.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            } catch (error) {
                console.error('Erreur chargement commandes:', error);
            } finally {
                this.isLoading = false;
            }
        },

        // Démarrer l'auto-refresh
        startAutoRefresh() {
            if (this.refreshInterval) clearInterval(this.refreshInterval);
            this.refreshInterval = setInterval(() => {
                if (this.autoRefresh) this.fetchOrders();
            }, 3000); // 3 secondes
        },

        // Toggle auto-refresh
        toggleAutoRefresh() {
            this.autoRefresh = !this.autoRefresh;
            if (this.autoRefresh) {
                this.startAutoRefresh();
            } else {
                clearInterval(this.refreshInterval);
            }
        },

        // Filtrer par statut
        ordersByStatus(status) {
            return this.orders.filter(order => order.status === status);
        },

        // Statistiques
        get stats() {
            return {
                RECU: this.ordersByStatus('RECU').length,
                PREP: this.ordersByStatus('PREP').length,
                PRET: this.ordersByStatus('PRET').length,
                SERVI: this.ordersByStatus('SERVI').length
            };
        },

        // Mise à jour du statut d'une commande (optimistic update)
        async updateStatus(orderId, newStatus) {
            // Trouver la commande
            const orderIndex = this.orders.findIndex(o => o.id === orderId);
            if (orderIndex === -1) return;

            const order = this.orders[orderIndex];
            const previousStatus = order.status;

            // Mise à jour optimiste immédiate
            order._updating = true;
            order.status = newStatus;

            try {
                const response = await fetch(`/api/orders/${orderId}/status`, {
                    method: 'PATCH',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({ status: newStatus })
                });

                if (!response.ok) {
                    // Rollback si erreur HTTP
                    order.status = previousStatus;
                    console.error('Erreur HTTP:', response.status, response.statusText);
                    return;
                }

                const result = await response.json();
                if (!result.success) {
                    // Rollback si erreur API
                    order.status = previousStatus;
                    console.error('Erreur API:', result.message || result.error);
                }
            } catch (error) {
                // Rollback en cas d'erreur réseau
                order.status = previousStatus;
                console.error('Erreur mise à jour:', error);
            } finally {
                order._updating = false;
            }
        },

        // Formatage de l'heure
        formatTime(timestamp) {
            return new Date(timestamp).toLocaleTimeString('fr-FR', {
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        // Afficher notification nouvelle commande
        showNewOrderNotification(count) {
            this.notificationMessage = count === 1
                ? 'Une nouvelle commande est arrivée'
                : `${count} nouvelles commandes sont arrivées`;
            this.showNotification = true;
            setTimeout(() => this.showNotification = false, 5000);
        },

        // Jouer son de notification
        playNotificationSound() {
            try {
                const audio = new Audio('/sounds/notification.mp3');
                audio.volume = 0.5;
                audio.play().catch(() => {});
            } catch (e) {
                // Son non disponible
            }
        },

        // Cleanup
        destroy() {
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
            }
        }
    };
}
