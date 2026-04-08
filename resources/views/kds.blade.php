<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KDS - {{ $tenantSlug }}</title>
    @vite(['resources/css/app.css'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .pulse { animation: pulse 2s ease-in-out infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.6; } }
    </style>
</head>
<body class="bg-gray-900 min-h-screen">
    <div x-data="kds('{{ $tenantSlug }}', {{ $tenantId ?? 0 }})" x-init="init()" class="p-3 text-white">

        <!-- Header -->
        <div class="bg-gradient-to-r from-orange-500 to-red-500 rounded-lg p-3 mb-3 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="bg-white/20 rounded-lg p-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold">KDS Cuisine</h1>
                    <p class="text-xs text-white/80">Temps réel</p>
                </div>
            </div>
            <div class="flex gap-2">
                <button @click="refresh()" class="bg-white/20 hover:bg-white/30 px-3 py-2 rounded-lg text-sm flex items-center gap-1">
                    <svg class="w-4 h-4" :class="loading && 'animate-spin'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span class="hidden sm:inline">Actualiser</span>
                </button>
                <button @click="autoRefresh = !autoRefresh"
                        :class="autoRefresh ? 'bg-green-500' : 'bg-red-500'"
                        class="px-3 py-2 rounded-lg text-sm">
                    Auto: <span x-text="autoRefresh ? 'ON' : 'OFF'"></span>
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-4 gap-2 mb-3">
            <div class="bg-yellow-500/20 border border-yellow-500/30 rounded-lg p-2 text-center">
                <div class="text-2xl font-bold text-yellow-400" x-text="countByStatus('RECU')"></div>
                <div class="text-xs text-yellow-300">Attente</div>
            </div>
            <div class="bg-blue-500/20 border border-blue-500/30 rounded-lg p-2 text-center">
                <div class="text-2xl font-bold text-blue-400" x-text="countByStatus('PREP')"></div>
                <div class="text-xs text-blue-300">Préparation</div>
            </div>
            <div class="bg-green-500/20 border border-green-500/30 rounded-lg p-2 text-center">
                <div class="text-2xl font-bold text-green-400" x-text="countByStatus('PRET')"></div>
                <div class="text-xs text-green-300">Prêts</div>
            </div>
            <div class="bg-gray-500/20 border border-gray-500/30 rounded-lg p-2 text-center">
                <div class="text-2xl font-bold text-gray-400" x-text="countByStatus('SERVI')"></div>
                <div class="text-xs text-gray-300">Servis</div>
            </div>
        </div>

        <!-- Kanban -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <!-- RECU -->
            <div class="bg-gray-800/50 rounded-lg p-3 border border-yellow-500/30">
                <h2 class="text-sm font-bold text-yellow-400 mb-3 flex items-center gap-2">
                    <span class="w-2 h-2 bg-yellow-500 rounded-full pulse"></span> Reçu
                </h2>
                <div class="space-y-2 max-h-[calc(100vh-220px)] overflow-y-auto">
                    <template x-for="order in filterByStatus('RECU')" :key="order.id">
                        <div class="bg-gray-700 rounded-lg p-3 border-l-4 border-yellow-400">
                            <div class="flex justify-between mb-2">
                                <div>
                                    <div class="font-bold text-sm" x-text="'Table ' + (order.table?.code || '?')"></div>
                                    <div class="text-xs text-gray-400" x-text="'#' + order.id + ' · ' + formatTime(order.created_at)"></div>
                                </div>
                                <div class="text-sm font-bold" x-text="order.total + ' F'"></div>
                            </div>
                            <div class="bg-black/30 rounded p-2 mb-2 text-xs space-y-1">
                                <template x-for="item in order.items" :key="item.id">
                                    <div class="flex gap-2">
                                        <span class="bg-white/20 rounded-full w-5 h-5 flex items-center justify-center" x-text="item.quantity"></span>
                                        <span x-text="item.dish?.name || 'Plat'"></span>
                                    </div>
                                </template>
                            </div>
                            <button @click="updateStatus(order.id, 'PREP')"
                                    class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 rounded text-xs font-medium">
                                Commencer
                            </button>
                        </div>
                    </template>
                    <div x-show="filterByStatus('RECU').length === 0" class="text-center text-gray-500 py-4 text-xs">Aucune</div>
                </div>
            </div>

            <!-- PREP -->
            <div class="bg-gray-800/50 rounded-lg p-3 border border-blue-500/30">
                <h2 class="text-sm font-bold text-blue-400 mb-3 flex items-center gap-2">
                    <span class="w-2 h-2 bg-blue-500 rounded-full pulse"></span> Préparation
                </h2>
                <div class="space-y-2 max-h-[calc(100vh-220px)] overflow-y-auto">
                    <template x-for="order in filterByStatus('PREP')" :key="order.id">
                        <div class="bg-gray-700 rounded-lg p-3 border-l-4 border-blue-400">
                            <div class="flex justify-between mb-2">
                                <div>
                                    <div class="font-bold text-sm" x-text="'Table ' + (order.table?.code || '?')"></div>
                                    <div class="text-xs text-gray-400" x-text="'#' + order.id + ' · ' + formatTime(order.created_at)"></div>
                                </div>
                                <div class="text-sm font-bold" x-text="order.total + ' F'"></div>
                            </div>
                            <div class="bg-black/30 rounded p-2 mb-2 text-xs space-y-1">
                                <template x-for="item in order.items" :key="item.id">
                                    <div class="flex gap-2">
                                        <span class="bg-white/20 rounded-full w-5 h-5 flex items-center justify-center" x-text="item.quantity"></span>
                                        <span x-text="item.dish?.name || 'Plat'"></span>
                                    </div>
                                </template>
                            </div>
                            <button @click="updateStatus(order.id, 'PRET')"
                                    class="w-full bg-green-500 hover:bg-green-600 text-white py-2 rounded text-xs font-medium">
                                Terminer
                            </button>
                        </div>
                    </template>
                    <div x-show="filterByStatus('PREP').length === 0" class="text-center text-gray-500 py-4 text-xs">Aucune</div>
                </div>
            </div>

            <!-- PRET -->
            <div class="bg-gray-800/50 rounded-lg p-3 border border-green-500/30">
                <h2 class="text-sm font-bold text-green-400 mb-3 flex items-center gap-2">
                    <span class="w-2 h-2 bg-green-500 rounded-full"></span> Prêt
                </h2>
                <div class="space-y-2 max-h-[calc(100vh-220px)] overflow-y-auto">
                    <template x-for="order in filterByStatus('PRET')" :key="order.id">
                        <div class="bg-gray-700 rounded-lg p-3 border-l-4 border-green-400">
                            <div class="flex justify-between mb-2">
                                <div>
                                    <div class="font-bold text-sm" x-text="'Table ' + (order.table?.code || '?')"></div>
                                    <div class="text-xs text-gray-400" x-text="'#' + order.id + ' · ' + formatTime(order.created_at)"></div>
                                </div>
                                <div class="text-sm font-bold" x-text="order.total + ' F'"></div>
                            </div>
                            <div class="bg-black/30 rounded p-2 mb-2 text-xs space-y-1">
                                <template x-for="item in order.items" :key="item.id">
                                    <div class="flex gap-2">
                                        <span class="bg-white/20 rounded-full w-5 h-5 flex items-center justify-center" x-text="item.quantity"></span>
                                        <span x-text="item.dish?.name || 'Plat'"></span>
                                    </div>
                                </template>
                            </div>
                            <button @click="updateStatus(order.id, 'SERVI')"
                                    class="w-full bg-gray-500 hover:bg-gray-600 text-white py-2 rounded text-xs font-medium">
                                Servir
                            </button>
                        </div>
                    </template>
                    <div x-show="filterByStatus('PRET').length === 0" class="text-center text-gray-500 py-4 text-xs">Aucune</div>
                </div>
            </div>

            <!-- SERVI -->
            <div class="bg-gray-800/50 rounded-lg p-3 border border-gray-500/30">
                <h2 class="text-sm font-bold text-gray-400 mb-3 flex items-center gap-2">
                    <span class="w-2 h-2 bg-gray-500 rounded-full"></span> Servi
                </h2>
                <div class="space-y-2 max-h-[calc(100vh-220px)] overflow-y-auto">
                    <template x-for="order in filterByStatus('SERVI')" :key="order.id">
                        <div class="bg-gray-700/50 rounded-lg p-3 border-l-4 border-gray-400 opacity-60">
                            <div class="flex justify-between mb-2">
                                <div>
                                    <div class="font-bold text-sm" x-text="'Table ' + (order.table?.code || '?')"></div>
                                    <div class="text-xs text-gray-400" x-text="'#' + order.id + ' · ' + formatTime(order.created_at)"></div>
                                </div>
                                <div class="text-sm font-bold" x-text="order.total + ' F'"></div>
                            </div>
                            <div class="bg-black/30 rounded p-2 text-xs space-y-1">
                                <template x-for="item in order.items" :key="item.id">
                                    <div class="flex gap-2">
                                        <span class="bg-white/20 rounded-full w-5 h-5 flex items-center justify-center" x-text="item.quantity"></span>
                                        <span x-text="item.dish?.name || 'Plat'"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                    <div x-show="filterByStatus('SERVI').length === 0" class="text-center text-gray-500 py-4 text-xs">Aucune</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Son de notification puissant pour KDS -->
    <script>
        // Classe pour générer un son d'alerte fort et répétitif
        class PowerfulNotificationSound {
            constructor() {
                this.audioContext = null;
                this.isPlaying = false;
            }

            init() {
                if (!this.audioContext) {
                    this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
                }
                if (this.audioContext.state === 'suspended') {
                    this.audioContext.resume();
                }
            }

            play(repeatCount = 3) {
                this.init();
                if (this.isPlaying) return;
                this.isPlaying = true;

                let currentRepeat = 0;

                const playSequence = () => {
                    if (currentRepeat >= repeatCount) {
                        this.isPlaying = false;
                        return;
                    }
                    currentRepeat++;

                    const ctx = this.audioContext;
                    const now = ctx.currentTime;

                    // Séquence de 3 bips aigus puissants
                    for (let i = 0; i < 3; i++) {
                        const startTime = now + i * 0.18;

                        const osc1 = ctx.createOscillator();
                        osc1.type = 'square';
                        osc1.frequency.setValueAtTime(880, startTime);

                        const osc2 = ctx.createOscillator();
                        osc2.type = 'sawtooth';
                        osc2.frequency.setValueAtTime(1320, startTime);

                        const osc3 = ctx.createOscillator();
                        osc3.type = 'triangle';
                        osc3.frequency.setValueAtTime(440, startTime);

                        const gainNode = ctx.createGain();
                        gainNode.gain.setValueAtTime(0, startTime);
                        gainNode.gain.linearRampToValueAtTime(0.9, startTime + 0.01);
                        gainNode.gain.setValueAtTime(0.9, startTime + 0.08);
                        gainNode.gain.exponentialRampToValueAtTime(0.01, startTime + 0.15);

                        osc1.connect(gainNode);
                        osc2.connect(gainNode);
                        osc3.connect(gainNode);
                        gainNode.connect(ctx.destination);

                        osc1.start(startTime);
                        osc1.stop(startTime + 0.15);
                        osc2.start(startTime);
                        osc2.stop(startTime + 0.15);
                        osc3.start(startTime);
                        osc3.stop(startTime + 0.15);
                    }

                    if (currentRepeat < repeatCount) {
                        setTimeout(playSequence, 800);
                    } else {
                        setTimeout(() => { this.isPlaying = false; }, 600);
                    }
                };

                playSequence();
            }
        }

        window.notificationSound = new PowerfulNotificationSound();
        document.addEventListener('click', () => {
            if (window.notificationSound) window.notificationSound.init();
        }, { once: true });
    </script>

    <script>
        function kds(tenantSlug, tenantId) {
            return {
                tenantSlug: tenantSlug,
                tenantId: tenantId,
                orders: [],
                loading: false,
                autoRefresh: true,
                interval: null,
                updating: false,
                pendingUpdates: {},
                knownOrderIds: new Set(), // Pour détecter les nouvelles commandes

                init() {
                    console.log('KDS init:', this.tenantSlug);
                    this.refresh();
                    this.startAutoRefresh();
                },

                async refresh() {
                    // Ne pas rafraîchir si une mise à jour est en cours
                    if (!this.tenantSlug || this.updating) return;

                    this.loading = true;
                    try {
                        const res = await fetch(`/api/orders/tenant/${this.tenantSlug}`, {
                            credentials: 'include',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        if (res.ok) {
                            let newOrders = await res.json();

                            // Dédupliquer les commandes par ID
                            const seen = new Set();
                            newOrders = newOrders.filter(order => {
                                if (seen.has(order.id)) return false;
                                seen.add(order.id);
                                return true;
                            });

                            // Détecter les nouvelles commandes RECU
                            const newRecuOrders = newOrders.filter(order =>
                                order.status === 'RECU' && !this.knownOrderIds.has(order.id)
                            );

                            // Jouer le son si nouvelles commandes
                            if (newRecuOrders.length > 0 && this.knownOrderIds.size > 0) {
                                this.playNotificationSound();
                            }

                            // Mettre à jour les IDs connus
                            newOrders.forEach(order => this.knownOrderIds.add(order.id));

                            // Préserver les statuts des mises à jour en attente
                            newOrders = newOrders.map(order => {
                                if (this.pendingUpdates[order.id]) {
                                    order.status = this.pendingUpdates[order.id];
                                }
                                return order;
                            });

                            this.orders = newOrders;
                        } else {
                            console.error('Erreur refresh:', res.status);
                        }
                    } catch (e) {
                        console.error('Erreur réseau:', e);
                    }
                    this.loading = false;
                },

                playNotificationSound() {
                    try {
                        if (window.notificationSound) {
                            window.notificationSound.play(3);
                        }
                    } catch (e) {
                        console.error('Erreur son notification:', e);
                    }
                },

                startAutoRefresh() {
                    this.interval = setInterval(() => {
                        if (this.autoRefresh && !this.updating) {
                            this.refresh();
                        }
                    }, 5000);
                },

                filterByStatus(status) {
                    return this.orders.filter(o => o.status === status);
                },

                countByStatus(status) {
                    return this.filterByStatus(status).length;
                },

                async updateStatus(orderId, newStatus) {
                    // Éviter les doubles clics
                    if (this.updating) return;

                    const order = this.orders.find(o => o.id === orderId);
                    if (!order) return;

                    const oldStatus = order.status;

                    // Bloquer les autres actions
                    this.updating = true;
                    this.pendingUpdates[orderId] = newStatus;

                    // Mise à jour optimiste immédiate
                    order.status = newStatus;

                    try {
                        const res = await fetch(`/api/orders/${orderId}/status`, {
                            method: 'PATCH',
                            credentials: 'include',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ status: newStatus })
                        });

                        const data = await res.json();

                        if (!res.ok || !data.success) {
                            // Rollback en cas d'erreur
                            order.status = oldStatus;
                            delete this.pendingUpdates[orderId];
                            console.error('Erreur API:', res.status, data);
                        } else {
                            // Succès - supprimer de pending après un délai
                            console.log('Statut mis à jour:', orderId, newStatus);
                            setTimeout(() => {
                                delete this.pendingUpdates[orderId];
                            }, 2000);
                        }
                    } catch (e) {
                        order.status = oldStatus;
                        delete this.pendingUpdates[orderId];
                        console.error('Erreur réseau:', e);
                    } finally {
                        this.updating = false;
                    }
                },

                formatTime(ts) {
                    if (!ts) return '';
                    return new Date(ts).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
                }
            };
        }
    </script>
</body>
</html>
