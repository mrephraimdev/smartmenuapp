/**
 * Menu Client Store - Alpine.js
 *
 * Gère l'état du menu client :
 * - Chargement du menu et tenant
 * - Panier persisté en localStorage
 * - Personnalisation des plats (variantes, options)
 * - Filtrage par catégorie
 * - Soumission des commandes
 * - Historique des commandes
 * - Avis clients
 * - Thème dynamique
 */

export default function menuClientStore() {
    return {
        // État principal
        loading: true,
        menu: null,
        tenant: null,
        table: null,

        // Panier (chargé depuis localStorage dans init)
        cart: [],

        // UI State
        selectedDish: null,
        selectedCategory: null,
        showCart: false,
        orderSuccess: false,

        // Navigation
        currentView: 'menu', // 'menu', 'orders', 'review'
        showRestaurantInfo: false,

        // Orders
        myOrders: [],
        isSubmitting: false, // Protection contre double soumission

        // Suivi de commande active
        activeOrder: null,
        orderTrackingInterval: null,

        // Review
        reviewForm: {
            customer_name: '',
            customer_email: '',
            food_rating: 0,
            service_rating: 0,
            ambiance_rating: 0,
            comment: '',
            is_anonymous: false
        },
        reviewLoading: false,
        reviewError: '',
        reviewSubmitted: false,
        ratingLabels: ['Mauvais', 'Passable', 'Bien', 'Très bien', 'Excellent'],

        // Waiter Call
        showCallWaiterModal: false,
        waiterCallLoading: false,
        waiterCallSuccess: false,

        // Personnalisation plat sélectionné
        customization: {
            variant: null,
            options: [],
            quantity: 1,
            notes: ''
        },

        // Lifecycle
        async init() {
            // Charger le panier depuis localStorage (synchrone, rapide)
            const savedCart = localStorage.getItem('smartmenu_cart');
            if (savedCart) {
                try {
                    this.cart = JSON.parse(savedCart);
                } catch (e) {
                    this.cart = [];
                }
            }

            // Observer les changements du panier pour sauvegarder
            this.$watch('cart', (value) => {
                localStorage.setItem('smartmenu_cart', JSON.stringify(value));
            });

            // Charger le menu (prioritaire - affiche le contenu)
            await this.loadMenu();

            // Charger thème et commandes en parallèle (non-bloquant)
            Promise.all([
                this.loadTheme(),
                this.checkActiveOrder()
            ]).catch(e => console.warn('Erreur chargement secondaire:', e));
        },

        // Chargement du menu via API
        async loadMenu() {
            try {
                // Récupérer la config depuis window (injectée par Blade) ou fallback
                const config = window.menuClientConfig || {};
                const tenantId = config.tenantId || '1';
                const tableCode = config.tableCode || 'A1';

                const response = await fetch(`/api/menu?tenant=${tenantId}&table=${tableCode}`);
                const data = await response.json();

                if (data.success) {
                    this.tenant = data.tenant;
                    this.table = data.table;
                    this.menu = data.menu;
                } else {
                    console.error('Erreur chargement menu:', data.message);
                }
            } catch (error) {
                console.error('Erreur:', error);
            } finally {
                this.loading = false;
            }
        },

        // Chargement et application du thème
        async loadTheme() {
            if (!this.tenant) return;

            try {
                const response = await fetch(`/api/tenants/${this.tenant.id}/theme`);
                const data = await response.json();

                if (data.theme) {
                    this.applyTheme(data.theme);
                }
            } catch (error) {
                console.warn('Thème non disponible:', error);
            }
        },

        // Application du thème CSS
        applyTheme(theme) {
            const styleEl = document.getElementById('theme-styles');
            if (!styleEl || !theme.colors) return;

            styleEl.textContent = `
                :root {
                    --theme-primary: ${theme.colors.primary};
                    --theme-secondary: ${theme.colors.secondary};
                    --theme-accent: ${theme.colors.accent || theme.colors.primary};
                }
                .bg-indigo-500 { background-color: var(--theme-primary) !important; }
                .hover\\:bg-indigo-600:hover { background-color: var(--theme-secondary) !important; }
                .text-indigo-500 { color: var(--theme-primary) !important; }
                .border-indigo-500 { border-color: var(--theme-primary) !important; }
                .bg-indigo-50 { background-color: color-mix(in srgb, var(--theme-primary) 10%, white) !important; }
                .focus\\:ring-indigo-500:focus { --tw-ring-color: var(--theme-primary) !important; }
            `;

            // Charger les polices si spécifiées
            if (theme.fonts?.heading) {
                const link = document.createElement('link');
                link.href = `https://fonts.googleapis.com/css2?family=${encodeURIComponent(theme.fonts.heading)}:wght@400;700&display=swap`;
                link.rel = 'stylesheet';
                document.head.appendChild(link);
            }
        },

        // Plats filtrés par catégorie
        get filteredDishes() {
            if (!this.menu?.categories) return [];

            let dishes = this.menu.categories.flatMap(cat =>
                (cat.dishes || []).map(dish => ({
                    ...dish,
                    is_available: dish.is_available !== false
                }))
            );

            if (this.selectedCategory) {
                const category = this.menu.categories.find(c => c.id === this.selectedCategory);
                dishes = (category?.dishes || []).map(dish => ({
                    ...dish,
                    is_available: dish.is_available !== false
                }));
            }

            return dishes;
        },

        // Sélectionner un plat pour personnalisation
        selectDish(dish) {
            this.selectedDish = dish;
            this.customization = {
                variant: dish.variants?.[0] || null,
                options: [],
                quantity: 1,
                notes: ''
            };
        },

        // Toggle une option
        toggleOption(option) {
            const index = this.customization.options.findIndex(o => o.id === option.id);
            if (index > -1) {
                this.customization.options.splice(index, 1);
            } else {
                this.customization.options.push(option);
            }
        },

        // Prix actuel avec variantes et options
        get currentPrice() {
            if (!this.selectedDish) return 0;

            let price = parseFloat(this.selectedDish.price_base) || 0;

            if (this.customization.variant) {
                price += parseFloat(this.customization.variant.extra_price) || 0;
            }

            this.customization.options.forEach(option => {
                price += parseFloat(option.extra_price) || 0;
            });

            return price * this.customization.quantity;
        },

        // Ajouter au panier
        addToCart() {
            if (!this.selectedDish) return;

            const cartItem = {
                id: Date.now(),
                dish_id: this.selectedDish.id,
                name: this.selectedDish.name,
                photo_url: this.selectedDish.photo_url,
                variant: this.customization.variant,
                options: [...this.customization.options],
                quantity: this.customization.quantity,
                notes: this.customization.notes,
                unit_price: this.currentPrice / this.customization.quantity,
                total_price: this.currentPrice
            };

            this.cart.push(cartItem);
            this.selectedDish = null;
            this.showCart = true;
        },

        // Supprimer du panier
        removeFromCart(itemId) {
            const index = this.cart.findIndex(item => item.id === itemId);
            if (index !== -1) {
                this.cart.splice(index, 1);
            }
        },

        // Total panier
        get cartTotal() {
            return this.cart.reduce((sum, item) => sum + item.total_price, 0);
        },

        // Nombre d'articles
        get cartCount() {
            return this.cart.reduce((sum, item) => sum + item.quantity, 0);
        },

        // Formatage prix
        formatPrice(price) {
            // Mapper les devises non-ISO vers les codes ISO valides
            const currencyMap = {
                'FCFA': 'XOF',
                'CFA': 'XOF',
                'F CFA': 'XOF'
            };

            let currency = this.tenant?.currency || 'XOF';
            currency = currencyMap[currency] || currency;

            try {
                return new Intl.NumberFormat('fr-FR', {
                    style: 'currency',
                    currency: currency,
                    minimumFractionDigits: 0
                }).format(price);
            } catch (e) {
                // Fallback si le code devise n'est pas reconnu
                return `${Math.round(price)} ${this.tenant?.currency || 'FCFA'}`;
            }
        },

        // Formatage date
        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return new Intl.DateTimeFormat('fr-FR', {
                day: '2-digit',
                month: 'short',
                hour: '2-digit',
                minute: '2-digit'
            }).format(date);
        },

        // Label de statut
        getStatusLabel(status) {
            const labels = {
                'RECU': 'Reçue',
                'PREP': 'En préparation',
                'PRET': 'Prête',
                'SERVI': 'Servie',
                'CANCELLED': 'Annulée'
            };
            return labels[status] || status;
        },

        // Icône de statut pour le suivi
        getStatusIcon(status) {
            const icons = {
                'RECU': '📋',
                'PREP': '👨‍🍳',
                'PRET': '✅',
                'SERVI': '🍽️'
            };
            return icons[status] || '⏳';
        },

        // Couleur de statut
        getStatusColor(status) {
            const colors = {
                'RECU': 'bg-yellow-500',
                'PREP': 'bg-blue-500',
                'PRET': 'bg-green-500',
                'SERVI': 'bg-gray-500'
            };
            return colors[status] || 'bg-gray-400';
        },

        // Vérifier si une étape est complétée
        isStepCompleted(step, currentStatus) {
            const order = ['RECU', 'PREP', 'PRET', 'SERVI'];
            const stepIndex = order.indexOf(step);
            const currentIndex = order.indexOf(currentStatus);
            return stepIndex <= currentIndex;
        },

        // Démarrer le suivi de commande
        startOrderTracking(orderId) {
            if (this.orderTrackingInterval) {
                clearInterval(this.orderTrackingInterval);
            }

            // Charger immédiatement
            this.loadActiveOrder(orderId);

            // Puis toutes les 5 secondes
            this.orderTrackingInterval = setInterval(() => {
                this.loadActiveOrder(orderId);
            }, 5000);
        },

        // Arrêter le suivi
        stopOrderTracking() {
            if (this.orderTrackingInterval) {
                clearInterval(this.orderTrackingInterval);
                this.orderTrackingInterval = null;
            }
        },

        // Charger la commande active
        async loadActiveOrder(orderId) {
            if (!orderId) return;

            try {
                console.log('Chargement commande:', orderId);
                const response = await fetch(`/api/orders/${orderId}`);
                const data = await response.json();
                console.log('Réponse API:', data);

                if (data.success && data.order) {
                    this.activeOrder = data.order;
                    console.log('activeOrder mis à jour:', this.activeOrder);

                    // Si la commande est payée, réinitialiser pour le prochain client
                    if (data.order.payment_status === 'PAID') {
                        this.handleOrderPaid();
                    }
                } else {
                    console.warn('Réponse API invalide:', data);
                }
            } catch (error) {
                console.error('Erreur chargement commande active:', error);
            }
        },

        // Gérer quand la commande est payée
        handleOrderPaid() {
            this.stopOrderTracking();

            // Afficher un message de remerciement puis réinitialiser
            setTimeout(() => {
                // Réinitialiser tout pour le prochain client
                this.activeOrder = null;
                this.myOrders = [];
                this.cart = [];
                this.currentView = 'menu';

                // Supprimer les données locales
                if (this.table?.id) {
                    localStorage.removeItem(`smartmenu_orders_${this.table.id}`);
                    localStorage.removeItem(`smartmenu_active_order_${this.table.id}`);
                }
                localStorage.removeItem('smartmenu_cart');

                // Message de confirmation
                this.orderSuccess = true;
                setTimeout(() => this.orderSuccess = false, 3000);
            }, 5000); // 5 secondes pour voir le message "Payé"
        },

        // Vérifier s'il y a une commande active au chargement
        async checkActiveOrder() {
            if (!this.table?.id) return;

            // Vérifier localStorage d'abord
            const activeOrderId = localStorage.getItem(`smartmenu_active_order_${this.table.id}`);
            if (activeOrderId) {
                console.log('Commande active trouvée:', activeOrderId);
                this.startOrderTracking(parseInt(activeOrderId));
                // Basculer vers la vue tracking si une commande active existe
                this.currentView = 'tracking';
            }
        },

        // Charger les commandes du client (basé sur la table)
        async loadMyOrders() {
            if (!this.tenant || !this.table) return;

            try {
                const response = await fetch(`/api/orders/table/${this.table.id}?tenant_id=${this.tenant.id}`);
                const data = await response.json();

                if (data.success) {
                    this.myOrders = data.orders || [];
                } else {
                    // Si l'API n'existe pas, utiliser localStorage
                    const savedOrders = localStorage.getItem(`smartmenu_orders_${this.table.id}`);
                    if (savedOrders) {
                        this.myOrders = JSON.parse(savedOrders);
                    }
                }
            } catch (error) {
                console.warn('Chargement commandes:', error);
                // Fallback localStorage
                const savedOrders = localStorage.getItem(`smartmenu_orders_${this.table.id}`);
                if (savedOrders) {
                    try {
                        this.myOrders = JSON.parse(savedOrders);
                    } catch (e) {
                        this.myOrders = [];
                    }
                }
            }
        },

        // Soumettre commande
        async submitOrder() {
            // Protection contre double-clic
            if (this.isSubmitting) return;

            if (this.cart.length === 0) {
                alert('Votre panier est vide');
                return;
            }

            this.isSubmitting = true;

            try {
                const response = await fetch('/api/orders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        tenant_id: this.tenant.id,
                        table_id: this.table.id,
                        items: this.cart.map(item => ({
                            dish_id: item.dish_id,
                            quantity: item.quantity,
                            variant_id: item.variant?.id || null,
                            options: item.options.map(opt => opt.name),
                            notes: item.notes || ''
                        })),
                        notes: 'Commande depuis l\'interface web'
                    })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    // L'API retourne order_id, order_number, total
                    const orderId = result.order_id;

                    // Sauvegarder la commande localement
                    const newOrder = {
                        id: orderId || Date.now(),
                        order_number: result.order_number,
                        status: 'RECU',
                        payment_status: 'PENDING',
                        total: this.cartTotal,
                        items: this.cart.map(item => ({
                            id: item.id,
                            quantity: item.quantity,
                            unit_price: item.unit_price,
                            dish: { name: item.name }
                        })),
                        created_at: new Date().toISOString()
                    };

                    // Ajouter aux commandes locales
                    this.myOrders.unshift(newOrder);
                    localStorage.setItem(`smartmenu_orders_${this.table.id}`, JSON.stringify(this.myOrders));

                    // Sauvegarder l'ID de la commande active pour le suivi
                    if (orderId) {
                        localStorage.setItem(`smartmenu_active_order_${this.table.id}`, orderId.toString());
                        // Initialiser activeOrder immédiatement pour affichage
                        this.activeOrder = newOrder;
                        this.startOrderTracking(orderId);
                    }

                    this.cart = [];
                    this.showCart = false;
                    this.orderSuccess = true;
                    setTimeout(() => this.orderSuccess = false, 5000);

                    // Basculer vers la vue de suivi
                    this.currentView = 'tracking';
                } else {
                    alert('Erreur: ' + (result.message || 'Erreur lors de l\'envoi'));
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors de l\'envoi de la commande');
            } finally {
                this.isSubmitting = false;
            }
        },

        // Soumettre un avis
        async submitReview() {
            // Validation
            if (this.reviewForm.food_rating === 0 || this.reviewForm.service_rating === 0 || this.reviewForm.ambiance_rating === 0) {
                this.reviewError = 'Veuillez donner une note pour chaque catégorie';
                return;
            }

            this.reviewLoading = true;
            this.reviewError = '';

            try {
                const response = await fetch(`/review/${this.tenant.slug}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        customer_name: this.reviewForm.customer_name,
                        customer_email: this.reviewForm.customer_email,
                        food_rating: this.reviewForm.food_rating,
                        service_rating: this.reviewForm.service_rating,
                        ambiance_rating: this.reviewForm.ambiance_rating,
                        comment: this.reviewForm.comment,
                        is_anonymous: this.reviewForm.is_anonymous,
                        table_code: this.table?.code || null
                    })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    this.reviewSubmitted = true;

                    // Reset form
                    this.reviewForm = {
                        customer_name: '',
                        customer_email: '',
                        food_rating: 0,
                        service_rating: 0,
                        ambiance_rating: 0,
                        comment: '',
                        is_anonymous: false
                    };

                    // Marquer l'avis comme envoyé en localStorage
                    localStorage.setItem(`smartmenu_review_${this.tenant.id}`, 'true');

                    setTimeout(() => {
                        this.reviewSubmitted = false;
                        this.currentView = 'menu';
                    }, 3000);
                } else {
                    this.reviewError = result.message || 'Une erreur est survenue';
                }
            } catch (error) {
                console.error('Erreur:', error);
                this.reviewError = 'Erreur de connexion. Veuillez réessayer.';
            } finally {
                this.reviewLoading = false;
            }
        },

        // Appeler un serveur
        async callWaiter(type) {
            if (!this.tenant || !this.table) {
                alert('Impossible d\'appeler un serveur. Veuillez rescanner le QR code.');
                return;
            }

            this.waiterCallLoading = true;

            try {
                const response = await fetch('/api/waiter-calls', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        tenant_id: this.tenant.id,
                        table_id: this.table.id,
                        call_type: type
                    })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    this.showCallWaiterModal = false;
                    this.waiterCallSuccess = true;
                    setTimeout(() => this.waiterCallSuccess = false, 4000);
                } else {
                    alert('Erreur: ' + (result.message || 'Impossible d\'envoyer l\'appel'));
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur de connexion. Veuillez réessayer.');
            } finally {
                this.waiterCallLoading = false;
            }
        }
    };
}
