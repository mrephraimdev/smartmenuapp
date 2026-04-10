<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Menu Restaurant</title>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/menu-client.js'])

    <style id="theme-styles"></style>

    <!-- Configuration du menu client -->
    <script>
        window.menuClientConfig = {
            tenantId: '{{ $tenantId ?? 1 }}',
            tableCode: '{{ $tableCode ?? "A1" }}'
        };
    </script>

    <style>
        /* Bottom navigation safe area */
        .pb-safe { padding-bottom: calc(5rem + env(safe-area-inset-bottom, 0px)); }
        /* Smooth scroll behavior */
        html { scroll-behavior: smooth; }
        /* Hide scrollbar for categories */
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-gray-50 pb-safe" x-data="menuClientStore()" x-init="init()">

    <!-- Loading State -->
    <div x-show="loading" class="fixed inset-0 bg-white z-50 flex items-center justify-center">
        <div class="text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto mb-4"></div>
            <p class="text-gray-600">Chargement du menu...</p>
        </div>
    </div>

    <!-- Cover Section with Restaurant Info -->
    <div x-show="!loading && currentView === 'menu'" class="relative h-72 md:h-80 bg-gradient-to-r from-gray-800 to-gray-600 overflow-hidden">
        <div class="absolute inset-0 bg-cover bg-center opacity-70"
             :style="tenant?.cover_url ? `background-image: url(${tenant.cover_url})` : ''"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
        <div class="relative z-10 flex flex-col items-center justify-center h-full px-4">
            <!-- Logo -->
            <div class="mb-4">
                <template x-if="tenant?.logo_url">
                    <img :src="tenant.logo_url" alt="Logo"
                         class="w-24 h-24 md:w-28 md:h-28 mx-auto rounded-full border-4 border-white shadow-xl object-cover">
                </template>
                <template x-if="!tenant?.logo_url">
                    <div class="w-24 h-24 md:w-28 md:h-28 mx-auto rounded-full border-4 border-white shadow-xl bg-indigo-500 flex items-center justify-center">
                        <span class="text-4xl font-bold text-white" x-text="tenant?.name?.charAt(0) || 'R'"></span>
                    </div>
                </template>
            </div>
            <!-- Restaurant Name -->
            <h1 class="text-3xl md:text-4xl font-bold text-white text-center mb-2" x-text="tenant?.name || 'Restaurant'"></h1>
            <!-- Table Badge -->
            <div class="bg-white/20 backdrop-blur-sm text-white px-4 py-1.5 rounded-full text-sm font-medium mb-4">
                <span class="flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    Table <span x-text="table?.code || '...'"></span>
                </span>
            </div>
            <!-- Contact Info -->
            <div class="flex flex-wrap items-center justify-center gap-4 text-white/90 text-sm">
                <template x-if="tenant?.address">
                    <a :href="'https://maps.google.com/?q=' + encodeURIComponent(tenant.address)"
                       target="_blank"
                       class="flex items-center hover:text-white transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="truncate max-w-[200px]" x-text="tenant.address"></span>
                    </a>
                </template>
                <template x-if="tenant?.phone">
                    <a :href="'tel:' + tenant.phone" class="flex items-center hover:text-white transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        <span x-text="tenant.phone"></span>
                    </a>
                </template>
            </div>
        </div>
    </div>

    <!-- Floating Header (shows on scroll) -->
    <header x-data="{ showHeader: false }"
            @scroll.window="showHeader = window.scrollY > 280 && currentView === 'menu'"
            x-show="showHeader"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-full"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="fixed top-0 left-0 right-0 bg-white shadow-md z-40">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <template x-if="tenant?.logo_url">
                        <img :src="tenant.logo_url" alt="Logo" class="w-10 h-10 rounded-full border-2 border-gray-200 object-cover">
                    </template>
                    <template x-if="!tenant?.logo_url">
                        <div class="w-10 h-10 rounded-full bg-indigo-500 flex items-center justify-center">
                            <span class="text-lg font-bold text-white" x-text="tenant?.name?.charAt(0) || 'R'"></span>
                        </div>
                    </template>
                    <div>
                        <h1 class="text-lg font-bold text-gray-800" x-text="tenant?.name"></h1>
                        <p class="text-sm text-gray-500" x-text="'Table ' + (table?.code || '')"></p>
                    </div>
                </div>
                <button @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
                        class="bg-indigo-500 text-white p-2 rounded-full hover:bg-indigo-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Cart Sidebar -->
    <div x-show="showCart"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed bottom-20 right-4 bg-white rounded-2xl shadow-2xl p-5 w-80 max-h-[70vh] overflow-hidden z-50 border border-gray-100">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-lg flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Votre Panier
            </h3>
            <button @click="showCart = false" class="text-gray-400 hover:text-gray-600 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Empty Cart -->
        <div x-show="cart.length === 0" class="text-center py-8 text-gray-500">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <p class="text-gray-500">Votre panier est vide</p>
            <p class="text-sm text-gray-400 mt-1">Ajoutez des plats pour commencer</p>
        </div>

        <!-- Cart Items -->
        <div x-show="cart.length > 0" class="max-h-52 overflow-y-auto mb-4 space-y-3 pr-1">
            <template x-for="(item, index) in cart" :key="item.id">
                <div class="flex justify-between items-start pb-3 border-b border-gray-100">
                    <div class="flex-1">
                        <div class="font-medium text-gray-800" x-text="item.name"></div>
                        <div class="text-sm text-gray-500">
                            <span x-show="item.variant" x-text="item.variant?.name + ' • '"></span>
                            <span x-text="'Qté: ' + item.quantity"></span>
                        </div>
                        <div x-show="item.notes" class="text-xs text-gray-400 italic mt-1" x-text="item.notes"></div>
                    </div>
                    <div class="text-right ml-3">
                        <div class="font-semibold text-gray-800" x-text="formatPrice(item.total_price)"></div>
                        <button @click="removeFromCart(item.id)" class="text-red-500 text-sm hover:text-red-700 mt-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Cart Footer -->
        <div x-show="cart.length > 0" class="border-t border-gray-100 pt-4">
            <div class="flex justify-between font-bold text-lg mb-4">
                <span>Total:</span>
                <span class="text-green-600" x-text="formatPrice(cartTotal)"></span>
            </div>
            <button @click="submitOrder()"
                    :disabled="isSubmitting"
                    :class="isSubmitting ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-500 hover:bg-green-600 hover:shadow-lg'"
                    class="w-full text-white py-3.5 rounded-xl font-semibold transition-all flex items-center justify-center">
                <template x-if="isSubmitting">
                    <svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </template>
                <template x-if="!isSubmitting">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </template>
                <span x-text="isSubmitting ? 'Envoi en cours...' : 'Valider la commande'"></span>
            </button>
        </div>
    </div>

    <!-- Main Content - Menu View -->
    <main x-show="!loading && currentView === 'menu'" class="container mx-auto px-4 py-6">
        <!-- Categories Navigation -->
        <div x-show="menu?.categories?.length > 0" class="flex overflow-x-auto space-x-2 mb-6 pb-2 scrollbar-hide">
            <button @click="selectedCategory = null"
                    :class="selectedCategory === null ? 'bg-indigo-500 text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-50'"
                    class="whitespace-nowrap px-5 py-2.5 border border-gray-200 rounded-full transition-all font-medium">
                Tous
            </button>
            <template x-for="category in menu?.categories" :key="category.id">
                <button @click="selectedCategory = category.id"
                        :class="selectedCategory === category.id ? 'bg-indigo-500 text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-50'"
                        class="whitespace-nowrap px-5 py-2.5 border border-gray-200 rounded-full transition-all font-medium"
                        x-text="category.name">
                </button>
            </template>
        </div>

        <!-- Dishes Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <template x-for="dish in filteredDishes" :key="dish.id">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 overflow-hidden group">
                    <!-- Dish Image -->
                    <div class="relative overflow-hidden">
                        <template x-if="dish.photo_url">
                            <img :src="dish.photo_url" :alt="dish.name"
                                 loading="lazy"
                                 class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                        </template>
                        <template x-if="!dish.photo_url">
                            <div class="w-full h-48 bg-gradient-to-br from-indigo-50 to-purple-50 flex items-center justify-center">
                                <svg class="w-12 h-12 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z"></path>
                                </svg>
                            </div>
                        </template>
                        <!-- Badge disponibilité -->
                        <div x-show="!dish.is_available"
                             class="absolute top-3 right-3 bg-red-500 text-white text-xs px-3 py-1 rounded-full font-medium">
                            Indisponible
                        </div>
                    </div>

                    <!-- Dish Info -->
                    <div class="p-4">
                        <h3 class="font-bold text-lg text-gray-800 mb-1" x-text="dish.name"></h3>
                        <p class="text-gray-500 text-sm mb-3 line-clamp-2" x-text="dish.description || 'Délicieux plat préparé avec soin'"></p>
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-green-600 text-xl" x-text="formatPrice(dish.price_base)"></span>
                            <button @click="selectDish(dish)"
                                    :disabled="!dish.is_available"
                                    :class="dish.is_available ? 'bg-indigo-500 hover:bg-indigo-600 hover:shadow-md' : 'bg-gray-300 cursor-not-allowed'"
                                    class="text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-all flex items-center">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Ajouter
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Empty State -->
        <div x-show="filteredDishes.length === 0" class="text-center py-16">
            <svg class="w-16 h-16 mx-auto text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z"></path>
            </svg>
            <p class="text-gray-500 text-lg">Aucun plat disponible pour le moment.</p>
            <p class="text-gray-400 text-sm mt-1">Revenez plus tard ou consultez une autre catégorie</p>
        </div>
    </main>

    <!-- Orders View -->
    <div x-show="!loading && currentView === 'orders'" class="min-h-screen">
        <!-- Header -->
        <div class="bg-white border-b border-gray-100 sticky top-0 z-30">
            <div class="container mx-auto px-4 py-4">
                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Mes Commandes
                </h2>
            </div>
        </div>

        <div class="container mx-auto px-4 py-6">
            <!-- Orders List -->
            <div x-show="myOrders.length > 0" class="space-y-4">
                <template x-for="order in myOrders" :key="order.id">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="p-4 border-b border-gray-50">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm text-gray-500">Commande #<span x-text="order.id"></span></p>
                                    <p class="text-xs text-gray-400 mt-0.5" x-text="formatDate(order.created_at)"></p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-medium"
                                      :class="{
                                          'bg-yellow-100 text-yellow-700': order.status === 'RECU',
                                          'bg-blue-100 text-blue-700': order.status === 'PREP',
                                          'bg-green-100 text-green-700': order.status === 'PRET',
                                          'bg-gray-100 text-gray-700': order.status === 'SERVI'
                                      }"
                                      x-text="getStatusLabel(order.status)"></span>
                            </div>
                        </div>
                        <div class="p-4">
                            <template x-for="item in order.items" :key="item.id">
                                <div class="flex justify-between items-center py-2 text-sm">
                                    <span class="text-gray-700">
                                        <span x-text="item.quantity"></span>x <span x-text="item.dish?.name || 'Plat'"></span>
                                    </span>
                                    <span class="text-gray-600 font-medium" x-text="formatPrice(item.unit_price * item.quantity)"></span>
                                </div>
                            </template>
                            <div class="flex justify-between items-center pt-3 mt-2 border-t border-gray-100">
                                <span class="font-semibold text-gray-800">Total</span>
                                <span class="font-bold text-green-600 text-lg" x-text="formatPrice(order.total)"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Empty State -->
            <div x-show="myOrders.length === 0" class="text-center py-16">
                <svg class="w-16 h-16 mx-auto text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <p class="text-gray-500 text-lg">Aucune commande pour le moment</p>
                <p class="text-gray-400 text-sm mt-1">Vos commandes apparaîtront ici</p>
                <button @click="currentView = 'menu'" class="mt-6 bg-indigo-500 text-white px-6 py-2.5 rounded-xl font-medium hover:bg-indigo-600 transition-colors">
                    Voir le menu
                </button>
            </div>
        </div>
    </div>

    <!-- Order Tracking View -->
    <div x-show="!loading && currentView === 'tracking'" class="min-h-screen bg-gradient-to-b from-indigo-50 to-white">
        <!-- Header -->
        <div class="bg-white border-b border-gray-100 sticky top-0 z-30">
            <div class="container mx-auto px-4 py-4">
                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    Suivi de commande
                </h2>
            </div>
        </div>

        <div class="container mx-auto px-4 py-6">
            <!-- Active Order Tracking -->
            <template x-if="activeOrder">
                <div class="space-y-6">
                    <!-- Order Status Card -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-lg overflow-hidden">
                        <!-- Status Header -->
                        <div class="p-6 text-center border-b border-gray-100"
                             :class="{
                                 'bg-yellow-50': activeOrder.status === 'RECU',
                                 'bg-blue-50': activeOrder.status === 'PREP',
                                 'bg-green-50': activeOrder.status === 'PRET',
                                 'bg-gray-50': activeOrder.status === 'SERVI'
                             }">
                            <!-- Animated Status Icon -->
                            <div class="text-6xl mb-4"
                                 :class="{'animate-bounce': activeOrder.status === 'PREP'}">
                                <span x-text="getStatusIcon(activeOrder.status)"></span>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-800" x-text="getStatusLabel(activeOrder.status)"></h3>
                            <p class="text-gray-500 mt-2">
                                Commande #<span x-text="activeOrder.order_number || activeOrder.id"></span>
                            </p>
                        </div>

                        <!-- Progress Steps -->
                        <div class="p-6">
                            <div class="flex items-center justify-between relative">
                                <!-- Progress Bar Background -->
                                <div class="absolute top-5 left-0 right-0 h-1 bg-gray-200 mx-8"></div>
                                <!-- Progress Bar Fill -->
                                <div class="absolute top-5 left-0 h-1 bg-indigo-500 mx-8 transition-all duration-500"
                                     :style="{
                                         width: activeOrder.status === 'RECU' ? '0%' :
                                                activeOrder.status === 'PREP' ? '33%' :
                                                activeOrder.status === 'PRET' ? '66%' :
                                                activeOrder.status === 'SERVI' ? '100%' : '0%'
                                     }"></div>

                                <!-- Step 1: RECU -->
                                <div class="relative z-10 flex flex-col items-center">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg transition-all"
                                         :class="isStepCompleted('RECU', activeOrder.status) ? 'bg-indigo-500 text-white' : 'bg-gray-200 text-gray-400'">
                                        📋
                                    </div>
                                    <span class="text-xs mt-2 text-gray-600 font-medium">Reçue</span>
                                </div>

                                <!-- Step 2: PREP -->
                                <div class="relative z-10 flex flex-col items-center">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg transition-all"
                                         :class="isStepCompleted('PREP', activeOrder.status) ? 'bg-indigo-500 text-white' : 'bg-gray-200 text-gray-400'">
                                        <span :class="activeOrder.status === 'PREP' ? 'animate-pulse' : ''">👨‍🍳</span>
                                    </div>
                                    <span class="text-xs mt-2 text-gray-600 font-medium">Préparation</span>
                                </div>

                                <!-- Step 3: PRET -->
                                <div class="relative z-10 flex flex-col items-center">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg transition-all"
                                         :class="isStepCompleted('PRET', activeOrder.status) ? 'bg-indigo-500 text-white' : 'bg-gray-200 text-gray-400'">
                                        ✅
                                    </div>
                                    <span class="text-xs mt-2 text-gray-600 font-medium">Prête</span>
                                </div>

                                <!-- Step 4: SERVI -->
                                <div class="relative z-10 flex flex-col items-center">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg transition-all"
                                         :class="isStepCompleted('SERVI', activeOrder.status) ? 'bg-indigo-500 text-white' : 'bg-gray-200 text-gray-400'">
                                        🍽️
                                    </div>
                                    <span class="text-xs mt-2 text-gray-600 font-medium">Servie</span>
                                </div>
                            </div>
                        </div>

                        <!-- Order Details -->
                        <div class="px-6 pb-6">
                            <div class="bg-gray-50 rounded-xl p-4">
                                <h4 class="font-semibold text-gray-700 mb-3">Détails de la commande</h4>
                                <template x-for="item in activeOrder.items" :key="item.id">
                                    <div class="flex justify-between items-center py-2 text-sm border-b border-gray-100 last:border-0">
                                        <span class="text-gray-700">
                                            <span x-text="item.quantity"></span>x <span x-text="item.dish?.name || 'Plat'"></span>
                                        </span>
                                        <span class="text-gray-600 font-medium" x-text="formatPrice(item.unit_price * item.quantity)"></span>
                                    </div>
                                </template>
                                <div class="flex justify-between items-center pt-3 mt-2 border-t border-gray-200">
                                    <span class="font-semibold text-gray-800">Total</span>
                                    <span class="font-bold text-green-600 text-lg" x-text="formatPrice(activeOrder.total)"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Status -->
                        <div class="px-6 pb-6">
                            <div class="rounded-xl p-4"
                                 :class="activeOrder.payment_status === 'PAID' ? 'bg-green-50 border border-green-200' : 'bg-yellow-50 border border-yellow-200'">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <template x-if="activeOrder.payment_status === 'PAID'">
                                            <svg class="w-6 h-6 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </template>
                                        <template x-if="activeOrder.payment_status !== 'PAID'">
                                            <svg class="w-6 h-6 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </template>
                                        <span class="font-medium"
                                              :class="activeOrder.payment_status === 'PAID' ? 'text-green-700' : 'text-yellow-700'"
                                              x-text="activeOrder.payment_status === 'PAID' ? 'Payé' : 'En attente de paiement'"></span>
                                    </div>
                                </div>
                                <template x-if="activeOrder.payment_status === 'PAID'">
                                    <p class="text-green-600 text-sm mt-2">
                                        Merci pour votre visite ! La page sera réinitialisée pour le prochain client.
                                    </p>
                                </template>
                            </div>
                        </div>

                        <!-- Auto-refresh indicator -->
                        <div class="px-6 pb-6">
                            <div class="flex items-center justify-center text-gray-400 text-sm">
                                <svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Mise à jour automatique toutes les 5 secondes
                            </div>
                        </div>
                    </div>

                    <!-- Back to Menu Button -->
                    <button @click="currentView = 'menu'"
                            class="w-full bg-white text-gray-700 py-3.5 rounded-xl font-medium border border-gray-200 hover:bg-gray-50 transition-colors flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Retour au menu
                    </button>
                </div>
            </template>

            <!-- No Active Order -->
            <template x-if="!activeOrder">
                <div class="text-center py-16">
                    <svg class="w-20 h-20 mx-auto text-gray-200 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <p class="text-gray-500 text-lg mb-2">Pas de commande en cours</p>
                    <p class="text-gray-400 text-sm mb-6">Passez une commande pour suivre son statut ici</p>
                    <button @click="currentView = 'menu'"
                            class="bg-indigo-500 text-white px-8 py-3 rounded-xl font-semibold hover:bg-indigo-600 transition-colors">
                        Commander maintenant
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- Review View -->
    <div x-show="!loading && currentView === 'review'" class="min-h-screen">
        <!-- Header -->
        <div class="bg-white border-b border-gray-100 sticky top-0 z-30">
            <div class="container mx-auto px-4 py-4">
                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                    </svg>
                    Donner votre avis
                </h2>
            </div>
        </div>

        <div class="container mx-auto px-4 py-6">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 max-w-lg mx-auto">
                <!-- Rating Section -->
                <div class="space-y-5 mb-6">
                    <p class="text-gray-800 font-semibold text-lg">Notez votre expérience</p>

                    <!-- Food Rating -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cuisine</label>
                        <div class="flex items-center space-x-1">
                            <template x-for="star in 5" :key="'food-' + star">
                                <button type="button" @click="reviewForm.food_rating = star"
                                        class="focus:outline-none transition-transform hover:scale-110">
                                    <svg class="w-8 h-8" :class="star <= reviewForm.food_rating ? 'text-yellow-400' : 'text-gray-300'"
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                </button>
                            </template>
                            <span class="ml-2 text-sm text-gray-500" x-text="ratingLabels[reviewForm.food_rating - 1] || ''"></span>
                        </div>
                    </div>

                    <!-- Service Rating -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Service</label>
                        <div class="flex items-center space-x-1">
                            <template x-for="star in 5" :key="'service-' + star">
                                <button type="button" @click="reviewForm.service_rating = star"
                                        class="focus:outline-none transition-transform hover:scale-110">
                                    <svg class="w-8 h-8" :class="star <= reviewForm.service_rating ? 'text-yellow-400' : 'text-gray-300'"
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                </button>
                            </template>
                            <span class="ml-2 text-sm text-gray-500" x-text="ratingLabels[reviewForm.service_rating - 1] || ''"></span>
                        </div>
                    </div>

                    <!-- Ambiance Rating -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ambiance</label>
                        <div class="flex items-center space-x-1">
                            <template x-for="star in 5" :key="'ambiance-' + star">
                                <button type="button" @click="reviewForm.ambiance_rating = star"
                                        class="focus:outline-none transition-transform hover:scale-110">
                                    <svg class="w-8 h-8" :class="star <= reviewForm.ambiance_rating ? 'text-yellow-400' : 'text-gray-300'"
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                </button>
                            </template>
                            <span class="ml-2 text-sm text-gray-500" x-text="ratingLabels[reviewForm.ambiance_rating - 1] || ''"></span>
                        </div>
                    </div>
                </div>

                <!-- Comment -->
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Votre commentaire</label>
                    <textarea x-model="reviewForm.comment"
                              rows="4"
                              placeholder="Décrivez votre expérience..."
                              class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"></textarea>
                </div>

                <!-- Customer Info -->
                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Votre nom (optionnel)</label>
                        <input type="text" x-model="reviewForm.customer_name"
                               placeholder="Entrez votre nom"
                               class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Votre email (optionnel)</label>
                        <input type="email" x-model="reviewForm.customer_email"
                               placeholder="Pour recevoir une réponse"
                               class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <p class="mt-1 text-xs text-gray-500">Pour recevoir une notification si le restaurant répond</p>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="is_anonymous" x-model="reviewForm.is_anonymous"
                               class="h-4 w-4 text-indigo-500 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="is_anonymous" class="ml-2 block text-sm text-gray-700">
                            Publier de manière anonyme
                        </label>
                    </div>
                </div>

                <!-- Error Message -->
                <div x-show="reviewError" x-cloak class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl">
                    <p class="text-sm text-red-600" x-text="reviewError"></p>
                </div>

                <!-- Submit Button -->
                <button @click="submitReview()"
                        :disabled="reviewLoading || reviewSubmitted"
                        :class="!reviewLoading && !reviewSubmitted ? 'bg-indigo-500 hover:bg-indigo-600' : 'bg-gray-300 cursor-not-allowed'"
                        class="w-full text-white py-3.5 rounded-xl font-semibold transition-all flex items-center justify-center">
                    <template x-if="reviewLoading">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <span x-text="reviewLoading ? 'Envoi en cours...' : 'Envoyer mon avis'"></span>
                </button>

                <!-- Success Message -->
                <div x-show="reviewSubmitted" class="mt-4 p-4 bg-green-50 border border-green-200 rounded-xl text-center">
                    <svg class="w-8 h-8 mx-auto text-green-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-green-700 font-medium">Merci pour votre avis !</p>
                    <p class="text-green-600 text-sm mt-1">Il sera publié après modération.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Call Waiter Button -->
    <button @click="showCallWaiterModal = true"
            x-show="!loading"
            class="fixed bottom-24 left-4 bg-orange-500 text-white p-4 rounded-2xl shadow-lg hover:bg-orange-600 transition-all hover:scale-105 hover:shadow-xl z-40">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
    </button>

    <!-- Floating Cart Button -->
    <button @click="showCart = !showCart"
            x-show="!loading && currentView === 'menu'"
            class="fixed bottom-24 right-4 bg-indigo-500 text-white p-4 rounded-2xl shadow-lg hover:bg-indigo-600 transition-all hover:scale-105 hover:shadow-xl z-40">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <span x-show="cartCount > 0"
              x-text="cartCount"
              class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full min-w-[24px] h-6 text-sm flex items-center justify-center font-bold px-1.5"></span>
    </button>

    <!-- Bottom Navigation Bar -->
    <nav x-show="!loading" class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 z-50 safe-area-bottom">
        <div class="flex justify-around items-center h-16">
            <!-- Menu -->
            <button @click="currentView = 'menu'; showCart = false"
                    class="flex flex-col items-center justify-center flex-1 h-full transition-colors"
                    :class="currentView === 'menu' ? 'text-indigo-500' : 'text-gray-400 hover:text-gray-600'">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <span class="text-xs mt-1 font-medium">Menu</span>
            </button>

            <!-- Suivi Commande -->
            <button @click="currentView = 'tracking'; showCart = false"
                    class="flex flex-col items-center justify-center flex-1 h-full transition-colors relative"
                    :class="currentView === 'tracking' ? 'text-indigo-500' : 'text-gray-400 hover:text-gray-600'">
                <div class="relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    <!-- Notification Badge -->
                    <span x-show="activeOrder && activeOrder.payment_status !== 'PAID'"
                          class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full animate-pulse"></span>
                </div>
                <span class="text-xs mt-1 font-medium">Suivi</span>
            </button>

            <!-- Donner un avis -->
            <button @click="currentView = 'review'; showCart = false"
                    class="flex flex-col items-center justify-center flex-1 h-full transition-colors"
                    :class="currentView === 'review' ? 'text-yellow-500' : 'text-gray-400 hover:text-gray-600'">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                </svg>
                <span class="text-xs mt-1 font-medium">Avis</span>
            </button>

            <!-- Info Restaurant -->
            <button @click="showRestaurantInfo = true"
                    class="flex flex-col items-center justify-center flex-1 h-full text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-xs mt-1 font-medium">Info</span>
            </button>
        </div>
    </nav>

    <!-- Restaurant Info Modal -->
    <div x-show="showRestaurantInfo"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         class="fixed inset-0 bg-black/50 flex items-end z-50"
         @click.self="showRestaurantInfo = false">
        <div x-show="showRestaurantInfo"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full"
             class="bg-white rounded-t-3xl w-full max-h-[80vh] overflow-y-auto">
            <div class="p-6">
                <!-- Handle -->
                <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto mb-6"></div>

                <!-- Logo & Name -->
                <div class="text-center mb-6">
                    <template x-if="tenant?.logo_url">
                        <img :src="tenant.logo_url" alt="Logo" class="w-20 h-20 mx-auto rounded-full border-4 border-gray-100 shadow-lg object-cover mb-4">
                    </template>
                    <h3 class="text-2xl font-bold text-gray-800" x-text="tenant?.name"></h3>
                </div>

                <!-- Contact Info -->
                <div class="space-y-4">
                    <template x-if="tenant?.address">
                        <a :href="'https://maps.google.com/?q=' + encodeURIComponent(tenant.address)"
                           target="_blank"
                           class="flex items-center p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                            <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mr-4">
                                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Adresse</p>
                                <p class="font-medium text-gray-800" x-text="tenant.address"></p>
                            </div>
                        </a>
                    </template>

                    <template x-if="tenant?.phone">
                        <a :href="'tel:' + tenant.phone"
                           class="flex items-center p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mr-4">
                                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Téléphone</p>
                                <p class="font-medium text-gray-800" x-text="tenant.phone"></p>
                            </div>
                        </a>
                    </template>

                    <template x-if="tenant?.email">
                        <a :href="'mailto:' + tenant.email"
                           class="flex items-center p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
                                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Email</p>
                                <p class="font-medium text-gray-800" x-text="tenant.email"></p>
                            </div>
                        </a>
                    </template>
                </div>

                <!-- Close Button -->
                <button @click="showRestaurantInfo = false"
                        class="w-full mt-6 py-3.5 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition-colors">
                    Fermer
                </button>
            </div>
        </div>
    </div>

    <!-- Customization Modal -->
    <div x-show="selectedDish"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
         @click.self="selectedDish = null">
        <div x-show="selectedDish"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             class="bg-white rounded-2xl w-full max-w-md max-h-[90vh] overflow-y-auto shadow-2xl">

            <!-- Modal Header with Image -->
            <div class="relative">
                <template x-if="selectedDish?.photo_url">
                    <img :src="selectedDish.photo_url" class="w-full h-48 object-cover">
                </template>
                <template x-if="!selectedDish?.photo_url">
                    <div class="w-full h-32 bg-gradient-to-br from-indigo-50 to-purple-50"></div>
                </template>
                <button @click="selectedDish = null"
                        class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm text-gray-600 p-2 rounded-full hover:bg-white transition-colors shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-1" x-text="selectedDish?.name"></h3>
                <p class="text-gray-500 text-sm mb-4" x-text="selectedDish?.description"></p>

                <!-- Variants Section -->
                <div x-show="selectedDish?.variants?.length > 0" class="mb-5">
                    <h4 class="font-semibold text-gray-700 mb-3 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        Choisir une taille
                    </h4>
                    <div class="space-y-2">
                        <template x-for="variant in selectedDish?.variants" :key="variant.id">
                            <label class="flex items-center p-3.5 border-2 rounded-xl cursor-pointer transition-all"
                                   :class="customization.variant?.id === variant.id ? 'border-indigo-500 bg-indigo-50' : 'border-gray-100 hover:border-gray-200'">
                                <input type="radio"
                                       :name="'variant_' + selectedDish?.id"
                                       :checked="customization.variant?.id === variant.id"
                                       @change="customization.variant = variant"
                                       class="w-4 h-4 text-indigo-500 mr-3">
                                <span class="flex-1 font-medium" x-text="variant.name"></span>
                                <span class="text-gray-500 font-medium"
                                      x-text="variant.extra_price > 0 ? '+' + formatPrice(variant.extra_price) : 'Inclus'"></span>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- Options Section -->
                <div x-show="selectedDish?.options?.length > 0" class="mb-5">
                    <h4 class="font-semibold text-gray-700 mb-3 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Options supplémentaires
                    </h4>
                    <div class="space-y-2">
                        <template x-for="option in selectedDish?.options" :key="option.id">
                            <label class="flex items-center p-3.5 border-2 rounded-xl cursor-pointer transition-all"
                                   :class="customization.options.some(o => o.id === option.id) ? 'border-indigo-500 bg-indigo-50' : 'border-gray-100 hover:border-gray-200'">
                                <input type="checkbox"
                                       :checked="customization.options.some(o => o.id === option.id)"
                                       @change="toggleOption(option)"
                                       class="w-4 h-4 text-indigo-500 rounded mr-3">
                                <span class="flex-1 font-medium" x-text="option.name"></span>
                                <span class="text-gray-500 font-medium"
                                      x-text="option.extra_price > 0 ? '+' + formatPrice(option.extra_price) : 'Gratuit'"></span>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-5">
                    <label class="block font-semibold text-gray-700 mb-2">Notes spéciales</label>
                    <textarea x-model="customization.notes"
                              placeholder="Ex: Sans gluten, moins salé, bien cuit..."
                              class="w-full border-2 border-gray-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
                              rows="2"></textarea>
                </div>

                <!-- Quantity -->
                <div class="mb-6">
                    <label class="block font-semibold text-gray-700 mb-3">Quantité</label>
                    <div class="flex items-center justify-center space-x-6">
                        <button @click="customization.quantity = Math.max(1, customization.quantity - 1)"
                                class="bg-gray-100 hover:bg-gray-200 w-12 h-12 rounded-xl text-xl font-bold transition-colors flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                        </button>
                        <span class="text-3xl font-bold w-16 text-center" x-text="customization.quantity"></span>
                        <button @click="customization.quantity++"
                                class="bg-gray-100 hover:bg-gray-200 w-12 h-12 rounded-xl text-xl font-bold transition-colors flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Price & Add Button -->
                <div class="flex items-center space-x-4">
                    <div class="flex-1">
                        <p class="text-sm text-gray-500">Prix total</p>
                        <p class="text-2xl font-bold text-green-600" x-text="formatPrice(currentPrice)"></p>
                    </div>
                    <button @click="addToCart()"
                            class="flex-1 bg-indigo-500 text-white py-4 rounded-xl hover:bg-indigo-600 font-semibold transition-all hover:shadow-lg flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Ajouter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Success Toast -->
    <div x-show="orderSuccess"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         class="fixed top-4 left-4 right-4 bg-green-500 text-white px-6 py-4 rounded-2xl shadow-xl z-50">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <div>
                <p class="font-bold">Commande envoyée !</p>
                <p class="text-sm text-green-100">Votre commande a été transmise à la cuisine.</p>
            </div>
        </div>
    </div>

    <!-- Call Waiter Modal -->
    <div x-show="showCallWaiterModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         class="fixed inset-0 bg-black/50 flex items-end z-50"
         @click.self="showCallWaiterModal = false">
        <div x-show="showCallWaiterModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full"
             class="bg-white rounded-t-3xl w-full max-h-[80vh] overflow-y-auto">
            <div class="p-6">
                <!-- Handle -->
                <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto mb-6"></div>

                <!-- Title -->
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Appeler un serveur</h3>
                    <p class="text-gray-500 text-sm mt-1">Table <span x-text="table?.code || '...'"></span></p>
                </div>

                <!-- Call Types -->
                <div class="space-y-3 mb-6">
                    <!-- Service -->
                    <button @click="callWaiter('SERVICE')"
                            :disabled="waiterCallLoading"
                            class="w-full flex items-center p-4 bg-blue-50 border-2 border-blue-100 rounded-2xl hover:border-blue-300 hover:bg-blue-100 transition-all group">
                        <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <div class="text-left">
                            <p class="font-semibold text-gray-800">Demander un service</p>
                            <p class="text-sm text-gray-500">Commander, addition, serviettes...</p>
                        </div>
                    </button>

                    <!-- Question/Préoccupation -->
                    <button @click="callWaiter('QUESTION')"
                            :disabled="waiterCallLoading"
                            class="w-full flex items-center p-4 bg-yellow-50 border-2 border-yellow-100 rounded-2xl hover:border-yellow-300 hover:bg-yellow-100 transition-all group">
                        <div class="w-12 h-12 bg-yellow-500 rounded-xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="text-left">
                            <p class="font-semibold text-gray-800">J'ai une question</p>
                            <p class="text-sm text-gray-500">Allergènes, ingrédients, conseils...</p>
                        </div>
                    </button>

                    <!-- Urgence -->
                    <button @click="callWaiter('URGENCE')"
                            :disabled="waiterCallLoading"
                            class="w-full flex items-center p-4 bg-red-50 border-2 border-red-100 rounded-2xl hover:border-red-300 hover:bg-red-100 transition-all group">
                        <div class="w-12 h-12 bg-red-500 rounded-xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform animate-pulse">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div class="text-left">
                            <p class="font-semibold text-gray-800">Urgence</p>
                            <p class="text-sm text-gray-500">Problème urgent, incident...</p>
                        </div>
                    </button>
                </div>

                <!-- Loading State -->
                <div x-show="waiterCallLoading" class="text-center py-4">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-500 mx-auto"></div>
                    <p class="text-gray-500 mt-2">Envoi en cours...</p>
                </div>

                <!-- Close Button -->
                <button @click="showCallWaiterModal = false"
                        :disabled="waiterCallLoading"
                        class="w-full py-3.5 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition-colors">
                    Annuler
                </button>
            </div>
        </div>
    </div>

    <!-- Waiter Call Success Toast -->
    <div x-show="waiterCallSuccess"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         class="fixed top-4 left-4 right-4 bg-orange-500 text-white px-6 py-4 rounded-2xl shadow-xl z-50">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
            </div>
            <div>
                <p class="font-bold">Serveur appelé !</p>
                <p class="text-sm text-orange-100">Un serveur arrive à votre table.</p>
            </div>
        </div>
    </div>

</body>
</html>
