<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Menu Restaurant</title>

    @vite(['resources/css/app.css', 'resources/js/menu-client.js'])

    <!-- DM Sans — même police que l'admin -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style id="theme-styles"></style>

    <script>
        window.menuClientConfig = {
            tenantId: '{{ $tenantId ?? 1 }}',
            tableCode: '{{ $tableCode ?? "A1" }}',
            preview: {{ isset($preview) && $preview ? 'true' : 'false' }},
        };
    </script>

    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'DM Sans', system-ui, sans-serif; font-size: 15px; }

        .pb-safe { padding-bottom: calc(4.5rem + env(safe-area-inset-bottom, 0px)); }

        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

        /* Default theme: amber-400 (#fbbf24) replaces indigo
           JS applyTheme() will override these with tenant colors */
        .bg-indigo-500  { background-color: #fbbf24 !important; }
        .hover\:bg-indigo-600:hover { background-color: #f59e0b !important; }
        .text-indigo-500 { color: #fbbf24 !important; }
        .border-indigo-500 { border-color: #fbbf24 !important; }
        .bg-indigo-50   { background-color: #fffbeb !important; }
        .focus\:ring-indigo-500:focus { --tw-ring-color: #fbbf24 !important; }

        /* Card hover lift — same as admin */
        .dish-card { transition: transform 0.18s ease, box-shadow 0.18s ease; }
        .dish-card:hover { transform: translateY(-2px); }

        /* Drawer backdrop */
        .drawer-backdrop {
            background: rgba(0,0,0,0.45);
        }

        /* Star rating */
        .star-btn { transition: transform 0.1s ease; }
        .star-btn:hover { transform: scale(1.15); }

        /* Page load animation — same as admin fadeUp */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-up { animation: fadeUp 0.3s ease both; }
    </style>
</head>

<body class="bg-gray-50 pb-safe" x-data="menuClientStore()" x-init="init()">

<!-- ═══════════════════════════════════════
     LOADING STATE
═══════════════════════════════════════ -->
<div x-show="loading" class="fixed inset-0 bg-gray-50 z-50 flex flex-col items-center justify-center">
    <!-- Logo box — identique à l'admin -->
    <div class="w-12 h-12 rounded-xl bg-amber-400 shadow-lg shadow-amber-400/30 flex items-center justify-center mb-5">
        <svg class="w-6 h-6 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z"></path>
        </svg>
    </div>
    <div class="w-5 h-5 border-2 border-gray-200 border-t-amber-400 rounded-full animate-spin mb-4"></div>
    <p class="text-gray-400 text-sm font-medium">Chargement du menu...</p>
</div>

<!-- ═══════════════════════════════════════
     COVER SECTION
═══════════════════════════════════════ -->
<div x-show="!loading && currentView === 'menu'" class="relative h-72 md:h-88 overflow-hidden">
    <div class="absolute inset-0 bg-slate-900"></div>
    <div class="absolute inset-0 bg-cover bg-center transition-opacity duration-500"
         :style="tenant?.cover_url ? `background-image: url(${tenant.cover_url}); opacity: 0.3` : 'opacity:0'"></div>
    {{-- Gradient renforcé pour garantir la lisibilité du texte --}}
    <div class="absolute inset-0" style="background: linear-gradient(to top, rgba(15,23,42,1) 0%, rgba(15,23,42,0.7) 50%, rgba(15,23,42,0.2) 100%);"></div>

    <!-- Content -->
    <div class="relative z-10 h-full flex flex-col items-center justify-end pb-7 px-6 text-center">
        <!-- Logo -->
        <div class="mb-3">
            <template x-if="tenant?.logo_url">
                <img :src="tenant.logo_url" alt="Logo"
                     class="w-16 h-16 md:w-20 md:h-20 mx-auto rounded-xl border-2 border-white/10 shadow-xl object-cover">
            </template>
            <template x-if="!tenant?.logo_url">
                <div class="w-16 h-16 md:w-20 md:h-20 mx-auto rounded-xl bg-amber-400 shadow-lg shadow-amber-400/30 flex items-center justify-center">
                    <span class="text-2xl font-bold text-slate-900" x-text="tenant?.name?.charAt(0) || 'R'"></span>
                </div>
            </template>
        </div>

        <!-- Restaurant Name -->
        <h1 class="text-2xl md:text-3xl font-bold text-white text-center leading-tight mb-1"
            style="text-shadow: 0 2px 8px rgba(0,0,0,0.5);"
            x-text="tenant?.name || 'Restaurant'"></h1>

        <!-- Address visible directement sous le nom -->
        <template x-if="tenant?.address">
            <p class="text-slate-300 text-xs mb-3" style="text-shadow: 0 1px 4px rgba(0,0,0,0.6);" x-text="tenant.address"></p>
        </template>
        <template x-if="!tenant?.address">
            <div class="mb-3"></div>
        </template>

        <!-- Table Badge -->
        <div class="inline-flex items-center bg-amber-400/20 border border-amber-400/40 text-amber-300 px-4 py-1.5 rounded-lg text-sm font-semibold mb-3">
            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
            </svg>
            Table <span class="ml-1" x-text="table?.code || '...'"></span>
        </div>

        <!-- Info + Contact -->
        <div class="flex items-center justify-center gap-4 text-slate-300 text-xs">
            <button @click="showRestaurantInfo = true"
                    class="inline-flex items-center gap-1 hover:text-amber-400 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Infos du restaurant
            </button>
            <template x-if="tenant?.phone">
                <a :href="'tel:' + tenant.phone"
                   class="inline-flex items-center gap-1 hover:text-amber-400 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                    <span x-text="tenant.phone"></span>
                </a>
            </template>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════
     FLOATING HEADER (on scroll)
═══════════════════════════════════════ -->
<header x-data="{ showHeader: false }"
        @scroll.window="showHeader = window.scrollY > 240 && currentView === 'menu'"
        x-show="showHeader"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-full"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-full"
        class="fixed top-0 left-0 right-0 h-14 bg-white border-b border-gray-100 shadow-sm z-40">
    <div class="container mx-auto px-4 h-full flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <template x-if="tenant?.logo_url">
                <img :src="tenant.logo_url" alt="Logo" class="w-9 h-9 rounded-lg border border-gray-100 object-cover">
            </template>
            <template x-if="!tenant?.logo_url">
                <div class="w-9 h-9 rounded-lg bg-amber-400 shadow-sm shadow-amber-400/30 flex items-center justify-center">
                    <span class="text-sm font-bold text-slate-900" x-text="tenant?.name?.charAt(0) || 'R'"></span>
                </div>
            </template>
            <div>
                <h1 class="text-sm font-semibold text-gray-800 leading-tight" x-text="tenant?.name"></h1>
                <p class="text-xs text-gray-400" x-text="'Table ' + (table?.code || '')"></p>
            </div>
        </div>
        <button @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
                class="bg-amber-400 text-slate-900 p-2 rounded-lg hover:bg-amber-500 transition-colors shadow-sm shadow-amber-400/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
            </svg>
        </button>
    </div>
</header>

<!-- ═══════════════════════════════════════
     CART VIEW (plein écran)
═══════════════════════════════════════ -->
<div x-show="!loading && currentView === 'cart'" class="min-h-screen bg-gray-50 fade-up">

    <!-- Header -->
    <div class="bg-white border-b border-gray-100 shadow-sm sticky top-0 z-30">
        <div class="container mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button @click="currentView = 'menu'" class="p-2 -ml-2 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </button>
                <h2 class="text-base font-semibold text-gray-800">Votre Panier</h2>
            </div>
            <span x-show="cartCount > 0"
                  class="bg-amber-50 border border-amber-300 text-amber-600 text-xs font-semibold px-2.5 py-1 rounded-full"
                  x-text="cartCount + ' article' + (cartCount > 1 ? 's' : '')"></span>
        </div>
    </div>

    <div class="container mx-auto px-4 py-5">

        <!-- Empty Cart -->
        <div x-show="cart.length === 0" class="flex flex-col items-center justify-center py-24">
            <div class="w-16 h-16 bg-gray-100 rounded-xl flex items-center justify-center mb-5">
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <p class="font-semibold text-gray-600 text-base mb-1">Votre panier est vide</p>
            <p class="text-gray-400 text-sm mb-6">Ajoutez des plats pour commencer</p>
            <button @click="currentView = 'menu'"
                    class="bg-amber-400 text-slate-900 px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-amber-500 transition-colors shadow-sm shadow-amber-400/20">
                Voir le menu
            </button>
        </div>

        <!-- Cart Items -->
        <div x-show="cart.length > 0" class="space-y-2 mb-4">
            <template x-for="(item, index) in cart" :key="item.id">
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm flex items-start gap-3 p-4">
                    <div class="w-14 h-14 rounded-xl bg-gray-100 flex-shrink-0 overflow-hidden">
                        <template x-if="item.photo_url">
                            <img :src="item.photo_url" :alt="item.name" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!item.photo_url">
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z"></path>
                                </svg>
                            </div>
                        </template>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-800 text-sm leading-tight" x-text="item.name"></p>
                        <p class="text-gray-400 text-xs mt-0.5">
                            <span x-show="item.variant" x-text="item.variant?.name + ' · '"></span>
                            <span x-text="'Qté : ' + item.quantity"></span>
                        </p>
                        <p x-show="item.notes" class="text-gray-400 text-xs italic mt-0.5" x-text="item.notes"></p>
                        <p class="font-bold text-amber-600 text-sm mt-1.5" x-text="formatPrice(item.total_price)"></p>
                    </div>
                    <button @click="removeFromCart(item.id)"
                            class="p-2 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </template>
        </div>

        <!-- Total + Submit -->
        <div x-show="cart.length > 0" class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-5">
                <span class="text-gray-500 text-sm">Total de la commande</span>
                <span class="text-2xl font-bold text-gray-800" x-text="formatPrice(cartTotal)"></span>
            </div>
            <button @click="submitOrder()"
                    :disabled="isSubmitting"
                    :class="isSubmitting ? 'bg-gray-200 cursor-not-allowed text-gray-400' : 'bg-amber-400 hover:bg-amber-500 text-slate-900 shadow-sm shadow-amber-400/20 active:scale-[0.98]'"
                    class="w-full py-4 rounded-xl font-semibold transition-all flex items-center justify-center text-sm">
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
</div>

<!-- ═══════════════════════════════════════
     MAIN CONTENT — MENU VIEW
═══════════════════════════════════════ -->
<main x-show="!loading && currentView === 'menu'" class="container mx-auto px-4 py-5 fade-up">

    <!-- Categories Navigation -->
    <div x-show="menu?.categories?.length > 0" class="flex overflow-x-auto space-x-2 mb-6 pb-1 scrollbar-hide">
        <button @click="selectedCategory = null"
                :class="selectedCategory === null
                    ? 'bg-amber-400 text-slate-900 shadow-sm shadow-amber-400/20'
                    : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200'"
                class="whitespace-nowrap px-5 py-2 rounded-full text-sm font-medium transition-all duration-200">
            Tous
        </button>
        <template x-for="category in menu?.categories" :key="category.id">
            <button @click="selectedCategory = category.id"
                    :class="selectedCategory === category.id
                        ? 'bg-amber-400 text-slate-900 shadow-sm shadow-amber-400/20'
                        : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200'"
                    class="whitespace-nowrap px-5 py-2 rounded-full text-sm font-medium transition-all duration-200"
                    x-text="category.name">
            </button>
        </template>
    </div>

    <!-- Dishes Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">
        <template x-for="dish in filteredDishes" :key="dish.id">
            <div class="dish-card bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md overflow-hidden group">
                <!-- Image -->
                <div class="relative overflow-hidden aspect-[4/3]">
                    <template x-if="dish.photo_url">
                        <img :src="dish.photo_url" :alt="dish.name"
                             loading="lazy"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    </template>
                    <template x-if="!dish.photo_url">
                        <div class="w-full h-full bg-gradient-to-br from-amber-50 to-gray-100 flex items-center justify-center">
                            <svg class="w-10 h-10 text-amber-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z"></path>
                            </svg>
                        </div>
                    </template>
                    <div x-show="!dish.is_available"
                         class="absolute top-2 right-2 bg-slate-900 text-white text-xs px-2.5 py-1 rounded-lg font-medium">
                        Indisponible
                    </div>
                </div>

                <!-- Info -->
                <div class="p-3">
                    <h3 class="font-semibold text-gray-800 text-sm leading-tight mb-1 line-clamp-1" x-text="dish.name"></h3>
                    <p class="text-gray-400 text-xs mb-3 line-clamp-2 leading-relaxed"
                       x-text="dish.description || 'Délicieux plat préparé avec soin'"></p>
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-amber-600 text-sm" x-text="formatPrice(dish.price_base)"></span>
                        <button @click="selectDish(dish)"
                                :disabled="!dish.is_available"
                                :class="dish.is_available
                                    ? 'bg-amber-400 hover:bg-amber-500 text-slate-900 shadow-sm shadow-amber-400/20'
                                    : 'bg-gray-100 text-gray-400 cursor-not-allowed'"
                                class="p-2 rounded-lg transition-all active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Empty State -->
    <div x-show="filteredDishes.length === 0" class="flex flex-col items-center justify-center py-20">
        <div class="w-16 h-16 bg-amber-50 rounded-xl flex items-center justify-center mb-5">
            <svg class="w-8 h-8 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z"></path>
            </svg>
        </div>
        <p class="font-semibold text-gray-600 text-base mb-1">Aucun plat disponible</p>
        <p class="text-gray-400 text-sm text-center">Revenez plus tard ou consultez une autre catégorie</p>
    </div>
</main>

<!-- ═══════════════════════════════════════
     ORDERS VIEW
═══════════════════════════════════════ -->
<div x-show="!loading && currentView === 'orders'" class="min-h-screen fade-up">
    <div class="bg-white border-b border-gray-100 shadow-sm sticky top-0 z-30">
        <div class="container mx-auto px-4 py-4">
            <h2 class="text-base font-semibold text-gray-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Mes Commandes
            </h2>
        </div>
    </div>

    <div class="container mx-auto px-4 py-5">
        <div x-show="myOrders.length > 0" class="space-y-3">
            <template x-for="order in myOrders" :key="order.id">
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-50 flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-400">Commande #<span x-text="order.id"></span></p>
                            <p class="text-xs text-gray-400 mt-0.5" x-text="formatDate(order.created_at)"></p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold"
                              :class="{
                                  'bg-amber-50 border border-amber-200 text-amber-700': order.status === 'RECU',
                                  'bg-blue-50 border border-blue-200 text-blue-700': order.status === 'PREP',
                                  'bg-green-50 border border-green-200 text-green-700': order.status === 'PRET',
                                  'bg-gray-100 border border-gray-200 text-gray-600': order.status === 'SERVI'
                              }"
                              x-text="getStatusLabel(order.status)"></span>
                    </div>
                    <div class="px-4 py-3">
                        <template x-for="item in order.items" :key="item.id">
                            <div class="flex justify-between items-center py-1.5 text-sm">
                                <span class="text-gray-600">
                                    <span class="font-medium text-amber-600" x-text="item.quantity"></span>
                                    <span class="mx-1 text-gray-300">×</span>
                                    <span x-text="item.dish?.name || 'Plat'"></span>
                                </span>
                                <span class="text-gray-600 font-medium" x-text="formatPrice(item.unit_price * item.quantity)"></span>
                            </div>
                        </template>
                        <div class="flex justify-between items-center pt-3 mt-1 border-t border-gray-50">
                            <span class="text-gray-500 text-sm">Total</span>
                            <span class="font-bold text-gray-800 text-base" x-text="formatPrice(order.total)"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div x-show="myOrders.length === 0" class="flex flex-col items-center justify-center py-20">
            <div class="w-16 h-16 bg-gray-100 rounded-xl flex items-center justify-center mb-5">
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
            <p class="font-semibold text-gray-600 text-base mb-1">Aucune commande</p>
            <p class="text-gray-400 text-sm mb-6">Vos commandes apparaîtront ici</p>
            <button @click="currentView = 'menu'" class="bg-amber-400 text-slate-900 px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-amber-500 transition-colors shadow-sm shadow-amber-400/20">
                Voir le menu
            </button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════
     ORDER TRACKING VIEW
═══════════════════════════════════════ -->
<div x-show="!loading && currentView === 'tracking'" class="min-h-screen bg-gray-50 fade-up">
    <div class="bg-white border-b border-gray-100 shadow-sm sticky top-0 z-30">
        <div class="container mx-auto px-4 py-4">
            <h2 class="text-base font-semibold text-gray-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
                Suivi de commande
            </h2>
        </div>
    </div>

    <div class="container mx-auto px-4 py-5">
        <template x-if="activeOrder">
            <div class="space-y-4">
                <!-- Status Card -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <!-- Status Banner -->
                    <div class="p-6 text-center"
                         :class="{
                             'bg-amber-50': activeOrder.status === 'RECU',
                             'bg-blue-50': activeOrder.status === 'PREP',
                             'bg-green-50': activeOrder.status === 'PRET',
                             'bg-gray-50': activeOrder.status === 'SERVI'
                         }">
                        <div class="text-5xl mb-3" :class="{'animate-bounce': activeOrder.status === 'PREP'}">
                            <span x-text="getStatusIcon(activeOrder.status)"></span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800" x-text="getStatusLabel(activeOrder.status)"></h3>
                        <p class="text-gray-400 text-sm mt-1">
                            Commande #<span x-text="activeOrder.order_number || activeOrder.id"></span>
                        </p>
                    </div>

                    <!-- Progress Steps -->
                    <div class="p-6 border-t border-gray-50">
                        <div class="flex items-center justify-between relative">
                            <div class="absolute top-5 left-4 right-4 h-0.5 bg-gray-100"></div>
                            <div class="absolute top-5 left-4 h-0.5 bg-amber-400 transition-all duration-700"
                                 :style="{
                                     width: activeOrder.payment_status === 'PAID' ? 'calc(100% - 2rem)' :
                                            activeOrder.status === 'RECU' ? '0%' :
                                            activeOrder.status === 'PREP' ? '25%' :
                                            activeOrder.status === 'PRET' ? '50%' :
                                            activeOrder.status === 'SERVI' ? '75%' : '0%'
                                 }"></div>

                            <div class="relative z-10 flex flex-col items-center">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base transition-all duration-300"
                                     :class="isStepCompleted('RECU', activeOrder.status) ? 'bg-amber-400 text-slate-900 shadow-sm shadow-amber-400/30' : 'bg-gray-100 text-gray-400'">
                                    📋
                                </div>
                                <span class="text-xs mt-2 text-gray-500 font-medium">Reçue</span>
                            </div>

                            <div class="relative z-10 flex flex-col items-center">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base transition-all duration-300"
                                     :class="isStepCompleted('PREP', activeOrder.status) ? 'bg-amber-400 text-slate-900 shadow-sm shadow-amber-400/30' : 'bg-gray-100 text-gray-400'">
                                    <span :class="activeOrder.status === 'PREP' ? 'animate-pulse' : ''">👨‍🍳</span>
                                </div>
                                <span class="text-xs mt-2 text-gray-500 font-medium">Prépa.</span>
                            </div>

                            <div class="relative z-10 flex flex-col items-center">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base transition-all duration-300"
                                     :class="isStepCompleted('PRET', activeOrder.status) ? 'bg-amber-400 text-slate-900 shadow-sm shadow-amber-400/30' : 'bg-gray-100 text-gray-400'">
                                    ✅
                                </div>
                                <span class="text-xs mt-2 text-gray-500 font-medium">Prête</span>
                            </div>

                            <div class="relative z-10 flex flex-col items-center">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base transition-all duration-300"
                                     :class="isStepCompleted('SERVI', activeOrder.status) ? 'bg-amber-400 text-slate-900 shadow-sm shadow-amber-400/30' : 'bg-gray-100 text-gray-400'">
                                    🍽️
                                </div>
                                <span class="text-xs mt-2 text-gray-500 font-medium">Servie</span>
                            </div>

                            <div class="relative z-10 flex flex-col items-center">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base transition-all duration-300"
                                     :class="activeOrder.payment_status === 'PAID' ? 'bg-green-500 text-white shadow-sm shadow-green-400/30' : 'bg-gray-100 text-gray-400'">
                                    💳
                                </div>
                                <span class="text-xs mt-2 font-medium"
                                      :class="activeOrder.payment_status === 'PAID' ? 'text-green-600' : 'text-gray-500'">Payée</span>
                            </div>
                        </div>
                    </div>

                    <!-- Order Details -->
                    <div class="px-5 pb-5">
                        <div class="bg-gray-50 rounded-xl p-4">
                            <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Détails de la commande</h4>
                            <template x-for="item in activeOrder.items" :key="item.id">
                                <div class="flex justify-between items-center py-1.5 text-sm border-b border-gray-100 last:border-0">
                                    <span class="text-gray-600">
                                        <span class="font-medium text-amber-600" x-text="item.quantity"></span>
                                        <span class="mx-1 text-gray-300">×</span>
                                        <span x-text="item.dish?.name || 'Plat'"></span>
                                    </span>
                                    <span class="text-gray-600 font-medium" x-text="formatPrice(item.unit_price * item.quantity)"></span>
                                </div>
                            </template>
                            <div class="flex justify-between items-center pt-3 mt-1">
                                <span class="text-gray-500 text-sm">Total</span>
                                <span class="font-bold text-gray-800" x-text="formatPrice(activeOrder.total)"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Status -->
                    <div class="px-5 pb-5">
                        <div class="rounded-xl p-4 border"
                             :class="activeOrder.payment_status === 'PAID' ? 'bg-green-50 border-green-200' : 'bg-amber-50 border-amber-200'">
                            <div class="flex items-center">
                                <template x-if="activeOrder.payment_status === 'PAID'">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </template>
                                <template x-if="activeOrder.payment_status !== 'PAID'">
                                    <svg class="w-5 h-5 text-amber-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </template>
                                <span class="font-medium text-sm"
                                      :class="activeOrder.payment_status === 'PAID' ? 'text-green-700' : 'text-amber-700'"
                                      x-text="activeOrder.payment_status === 'PAID' ? 'Payé' : 'En attente de paiement'"></span>
                            </div>
                            <template x-if="activeOrder.payment_status === 'PAID'">
                                <p class="text-green-600 text-xs mt-2">
                                    Merci pour votre visite ! La page sera réinitialisée pour le prochain client.
                                </p>
                            </template>
                        </div>
                    </div>

                    <!-- Auto-refresh indicator -->
                    <div class="px-5 pb-5">
                        <div class="flex items-center justify-center text-gray-400 text-xs">
                            <svg class="w-3.5 h-3.5 mr-1.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Mise à jour automatique toutes les 5 secondes
                        </div>
                    </div>
                </div>

                <button @click="currentView = 'menu'"
                        class="w-full bg-white text-gray-600 py-3.5 rounded-xl font-medium border border-gray-200 hover:bg-gray-50 transition-colors flex items-center justify-center text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Retour au menu
                </button>
            </div>
        </template>

        <template x-if="!activeOrder">
            <div class="flex flex-col items-center justify-center py-20">
                <div class="w-16 h-16 bg-gray-100 rounded-xl flex items-center justify-center mb-5">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <p class="font-semibold text-gray-600 text-base mb-1">Pas de commande en cours</p>
                <p class="text-gray-400 text-sm mb-6">Passez une commande pour suivre son statut ici</p>
                <button @click="currentView = 'menu'"
                        class="bg-amber-400 text-slate-900 px-8 py-3 rounded-xl font-semibold hover:bg-amber-500 transition-colors text-sm shadow-sm shadow-amber-400/20">
                    Commander maintenant
                </button>
            </div>
        </template>
    </div>
</div>

<!-- ═══════════════════════════════════════
     REVIEW VIEW
═══════════════════════════════════════ -->
<div x-show="!loading && currentView === 'review'" class="min-h-screen fade-up">
    <div class="bg-white border-b border-gray-100 shadow-sm sticky top-0 z-30">
        <div class="container mx-auto px-4 py-4">
            <h2 class="text-base font-semibold text-gray-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                </svg>
                Donner votre avis
            </h2>
        </div>
    </div>

    <div class="container mx-auto px-4 py-5">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 max-w-lg mx-auto">
            <p class="font-semibold text-gray-800 text-base mb-5">Notez votre expérience</p>

            <div class="space-y-5 mb-6">
                <!-- Food Rating -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center">
                            <span class="text-base">🍽️</span>
                        </div>
                        <label class="text-sm font-medium text-gray-700">Cuisine</label>
                    </div>
                    <div class="flex items-center gap-0.5">
                        <template x-for="star in 5" :key="'food-' + star">
                            <button type="button" @click="reviewForm.food_rating = star" class="star-btn focus:outline-none p-0.5">
                                <svg class="w-7 h-7 transition-colors"
                                     :class="star <= reviewForm.food_rating ? 'text-amber-400' : 'text-gray-200'"
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            </button>
                        </template>
                        <span class="ml-1 text-xs text-gray-400 w-16" x-text="ratingLabels[reviewForm.food_rating - 1] || ''"></span>
                    </div>
                </div>

                <!-- Service Rating -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                            <span class="text-base">🤵</span>
                        </div>
                        <label class="text-sm font-medium text-gray-700">Service</label>
                    </div>
                    <div class="flex items-center gap-0.5">
                        <template x-for="star in 5" :key="'service-' + star">
                            <button type="button" @click="reviewForm.service_rating = star" class="star-btn focus:outline-none p-0.5">
                                <svg class="w-7 h-7 transition-colors"
                                     :class="star <= reviewForm.service_rating ? 'text-amber-400' : 'text-gray-200'"
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            </button>
                        </template>
                        <span class="ml-1 text-xs text-gray-400 w-16" x-text="ratingLabels[reviewForm.service_rating - 1] || ''"></span>
                    </div>
                </div>

                <!-- Ambiance Rating -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-purple-50 rounded-lg flex items-center justify-center">
                            <span class="text-base">✨</span>
                        </div>
                        <label class="text-sm font-medium text-gray-700">Ambiance</label>
                    </div>
                    <div class="flex items-center gap-0.5">
                        <template x-for="star in 5" :key="'ambiance-' + star">
                            <button type="button" @click="reviewForm.ambiance_rating = star" class="star-btn focus:outline-none p-0.5">
                                <svg class="w-7 h-7 transition-colors"
                                     :class="star <= reviewForm.ambiance_rating ? 'text-amber-400' : 'text-gray-200'"
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            </button>
                        </template>
                        <span class="ml-1 text-xs text-gray-400 w-16" x-text="ratingLabels[reviewForm.ambiance_rating - 1] || ''"></span>
                    </div>
                </div>
            </div>

            <!-- Comment -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Votre commentaire</label>
                <textarea x-model="reviewForm.comment"
                          rows="3"
                          placeholder="Décrivez votre expérience..."
                          class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-700 placeholder-gray-400 focus:ring-2 focus:ring-amber-400 focus:border-transparent resize-none outline-none transition-shadow"></textarea>
            </div>

            <!-- Customer Info -->
            <div class="space-y-3 mb-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Votre nom (optionnel)</label>
                    <input type="text" x-model="reviewForm.customer_name"
                           placeholder="Entrez votre nom"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-400 focus:ring-2 focus:ring-amber-400 focus:border-transparent outline-none transition-shadow">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Votre email (optionnel)</label>
                    <input type="email" x-model="reviewForm.customer_email"
                           placeholder="Pour recevoir une réponse"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 placeholder-gray-400 focus:ring-2 focus:ring-amber-400 focus:border-transparent outline-none transition-shadow">
                    <p class="mt-1 text-xs text-gray-400">Pour recevoir une notification si le restaurant répond</p>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="is_anonymous" x-model="reviewForm.is_anonymous"
                           class="h-4 w-4 text-amber-400 focus:ring-amber-400 border-gray-300 rounded accent-amber-400">
                    <span class="text-sm text-gray-600">Publier de manière anonyme</span>
                </label>
            </div>

            <!-- Error -->
            <div x-show="reviewError" x-cloak class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl">
                <p class="text-xs text-red-600" x-text="reviewError"></p>
            </div>

            <!-- Submit -->
            <button @click="submitReview()"
                    :disabled="reviewLoading || reviewSubmitted"
                    :class="!reviewLoading && !reviewSubmitted ? 'bg-amber-400 hover:bg-amber-500 text-slate-900 shadow-sm shadow-amber-400/20' : 'bg-gray-200 cursor-not-allowed text-gray-400'"
                    class="w-full py-3.5 rounded-xl font-semibold transition-all flex items-center justify-center text-sm">
                <template x-if="reviewLoading">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </template>
                <span x-text="reviewLoading ? 'Envoi en cours...' : 'Envoyer mon avis'"></span>
            </button>

            <!-- Success -->
            <div x-show="reviewSubmitted" class="mt-4 p-4 bg-green-50 border border-green-100 rounded-xl text-center">
                <svg class="w-7 h-7 mx-auto text-green-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-green-700 font-semibold text-sm">Merci pour votre avis !</p>
                <p class="text-green-600 text-xs mt-1">Il sera publié après modération.</p>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════
     FLOATING ACTION BUTTONS
═══════════════════════════════════════ -->
<!-- Call Waiter FAB -->
<button @click="showCallWaiterModal = true"
        x-show="!loading"
        class="fixed bottom-24 left-4 bg-amber-400 text-slate-900 p-3.5 rounded-xl shadow-lg shadow-amber-400/30 hover:bg-amber-500 hover:scale-105 transition-all z-40">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
    </svg>
</button>


<!-- ═══════════════════════════════════════
     BOTTOM NAVIGATION
═══════════════════════════════════════ -->
<nav x-show="!loading"
     class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 shadow-sm z-50"
     style="padding-bottom: env(safe-area-inset-bottom)">
    <div class="flex justify-around items-center h-16">
        <!-- Menu -->
        <button @click="currentView = 'menu'"
                class="flex flex-col items-center justify-center flex-1 h-full transition-all relative"
                :class="currentView === 'menu' ? 'text-amber-500' : 'text-gray-400 hover:text-gray-600'">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
            <span class="text-[10px] mt-1 font-medium">Menu</span>
            <div x-show="currentView === 'menu'" class="absolute bottom-1.5 left-1/2 -translate-x-1/2 w-8 h-0.5 bg-amber-400 rounded-full"></div>
        </button>

        <!-- Suivi -->
        <button @click="currentView = 'tracking'"
                class="flex flex-col items-center justify-center flex-1 h-full transition-all relative"
                :class="currentView === 'tracking' ? 'text-amber-500' : 'text-gray-400 hover:text-gray-600'">
            <div class="relative">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
                <span x-show="activeOrder && activeOrder.payment_status !== 'PAID'"
                      class="absolute -top-0.5 -right-0.5 w-2 h-2 bg-amber-400 rounded-full animate-pulse"></span>
            </div>
            <span class="text-[10px] mt-1 font-medium">Suivi</span>
            <div x-show="currentView === 'tracking'" class="absolute bottom-1.5 left-1/2 -translate-x-1/2 w-8 h-0.5 bg-amber-400 rounded-full"></div>
        </button>

        <!-- Panier -->
        <button @click="currentView = 'cart'"
                class="flex flex-col items-center justify-center flex-1 h-full transition-all relative"
                :class="currentView === 'cart' ? 'text-amber-500' : 'text-gray-400 hover:text-gray-600'">
            <div class="relative">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span x-show="cartCount > 0"
                      x-text="cartCount"
                      class="absolute -top-1.5 -right-2 bg-amber-400 text-slate-900 rounded-full min-w-[16px] h-4 text-[10px] flex items-center justify-center font-bold px-0.5"></span>
            </div>
            <span class="text-[10px] mt-1 font-medium">Panier</span>
            <div x-show="currentView === 'cart'" class="absolute bottom-1.5 left-1/2 -translate-x-1/2 w-8 h-0.5 bg-amber-400 rounded-full"></div>
        </button>

        <!-- Avis -->
        <button @click="currentView = 'review'"
                class="flex flex-col items-center justify-center flex-1 h-full transition-all relative"
                :class="currentView === 'review' ? 'text-amber-500' : 'text-gray-400 hover:text-gray-600'">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
            </svg>
            <span class="text-[10px] mt-1 font-medium">Avis</span>
            <div x-show="currentView === 'review'" class="absolute bottom-1.5 left-1/2 -translate-x-1/2 w-8 h-0.5 bg-amber-400 rounded-full"></div>
        </button>
    </div>
</nav>

<!-- ═══════════════════════════════════════
     RESTAURANT INFO MODAL
═══════════════════════════════════════ -->
<div x-show="showRestaurantInfo"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 drawer-backdrop flex items-end z-50"
     @click.self="showRestaurantInfo = false">
    <div x-show="showRestaurantInfo"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full"
         class="bg-gray-50 rounded-t-2xl w-full max-h-[80vh] overflow-y-auto">
        <div class="p-6">
            <div class="w-10 h-1 bg-gray-200 rounded-full mx-auto mb-6"></div>

            <div class="text-center mb-6">
                <template x-if="tenant?.logo_url">
                    <img :src="tenant.logo_url" alt="Logo" class="w-16 h-16 mx-auto rounded-xl border border-gray-100 shadow-sm object-cover mb-3">
                </template>
                <template x-if="!tenant?.logo_url">
                    <div class="w-16 h-16 mx-auto rounded-xl bg-amber-400 shadow-sm shadow-amber-400/30 flex items-center justify-center mb-3">
                        <span class="text-2xl font-bold text-slate-900" x-text="tenant?.name?.charAt(0) || 'R'"></span>
                    </div>
                </template>
                <h3 class="font-semibold text-lg text-gray-800" x-text="tenant?.name"></h3>
            </div>

            <div class="space-y-3">
                <template x-if="tenant?.address">
                    <a :href="'https://maps.google.com/?q=' + encodeURIComponent(tenant.address)"
                       target="_blank"
                       class="flex items-center p-4 bg-white rounded-xl border border-gray-100 hover:border-amber-300 hover:shadow-sm transition-all group">
                        <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center mr-4 group-hover:bg-amber-100 transition-colors flex-shrink-0">
                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Adresse</p>
                            <p class="text-sm font-medium text-gray-700" x-text="tenant.address"></p>
                        </div>
                    </a>
                </template>

                <template x-if="tenant?.phone">
                    <a :href="'tel:' + tenant.phone"
                       class="flex items-center p-4 bg-white rounded-xl border border-gray-100 hover:border-green-300 hover:shadow-sm transition-all group">
                        <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center mr-4 group-hover:bg-green-100 transition-colors flex-shrink-0">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Téléphone</p>
                            <p class="text-sm font-medium text-gray-700" x-text="tenant.phone"></p>
                        </div>
                    </a>
                </template>

                <template x-if="tenant?.email">
                    <a :href="'mailto:' + tenant.email"
                       class="flex items-center p-4 bg-white rounded-xl border border-gray-100 hover:border-blue-300 hover:shadow-sm transition-all group">
                        <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center mr-4 group-hover:bg-blue-100 transition-colors flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Email</p>
                            <p class="text-sm font-medium text-gray-700" x-text="tenant.email"></p>
                        </div>
                    </a>
                </template>
            </div>

            <button @click="showRestaurantInfo = false"
                    class="w-full mt-5 py-3 bg-white text-gray-600 rounded-xl text-sm font-medium border border-gray-100 hover:bg-gray-50 transition-colors">
                Fermer
            </button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════
     CUSTOMIZATION MODAL
═══════════════════════════════════════ -->
<div x-show="selectedDish"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 drawer-backdrop flex items-end md:items-center justify-center z-50 md:p-4"
     @click.self="selectedDish = null">
    <div x-show="selectedDish"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-y-full md:opacity-0 md:scale-95 md:translate-y-0"
         x-transition:enter-end="translate-y-0 md:opacity-100 md:scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-y-0 md:opacity-100 md:scale-100"
         x-transition:leave-end="translate-y-full md:opacity-0 md:scale-95 md:translate-y-0"
         class="bg-white rounded-t-2xl md:rounded-xl w-full md:max-w-md max-h-[90vh] overflow-y-auto shadow-2xl border-t border-gray-100">

        <!-- Image / Header -->
        <div class="relative flex-shrink-0">
            <template x-if="selectedDish?.photo_url">
                <img :src="selectedDish.photo_url" class="w-full h-52 object-cover">
            </template>
            <template x-if="!selectedDish?.photo_url">
                <div class="w-full h-16 bg-gradient-to-br from-amber-50 to-gray-100"></div>
            </template>
            <div class="absolute top-3 left-1/2 -translate-x-1/2 w-10 h-1 bg-white/60 rounded-full md:hidden"></div>
            <button @click="selectedDish = null"
                    class="absolute top-4 right-4 bg-white text-gray-600 p-2 rounded-xl hover:bg-gray-50 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="p-5">
            <h3 class="text-lg font-bold text-gray-800 mb-1" x-text="selectedDish?.name"></h3>
            <p class="text-gray-400 text-sm mb-5 leading-relaxed" x-text="selectedDish?.description"></p>

            <!-- Variants -->
            <div x-show="selectedDish?.variants?.length > 0" class="mb-5">
                <h4 class="text-sm font-semibold text-gray-600 mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                    </svg>
                    Choisir une taille
                </h4>
                <div class="space-y-2">
                    <template x-for="variant in selectedDish?.variants" :key="variant.id">
                        <label class="flex items-center p-3 border-2 rounded-xl cursor-pointer transition-all"
                               :class="customization.variant?.id === variant.id
                                   ? 'border-amber-400 bg-amber-50'
                                   : 'border-gray-100 hover:border-gray-200 bg-white'">
                            <input type="radio"
                                   :name="'variant_' + selectedDish?.id"
                                   :checked="customization.variant?.id === variant.id"
                                   @change="customization.variant = variant"
                                   class="w-4 h-4 text-amber-400 border-gray-300 mr-3 accent-amber-400">
                            <span class="flex-1 text-sm font-medium text-gray-700" x-text="variant.name"></span>
                            <span class="text-amber-600 text-sm font-semibold"
                                  x-text="variant.extra_price > 0 ? '+' + formatPrice(variant.extra_price) : 'Inclus'"></span>
                        </label>
                    </template>
                </div>
            </div>

            <!-- Options -->
            <div x-show="selectedDish?.options?.length > 0" class="mb-5">
                <h4 class="text-sm font-semibold text-gray-600 mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Options supplémentaires
                </h4>
                <div class="space-y-2">
                    <template x-for="option in selectedDish?.options" :key="option.id">
                        <label class="flex items-center p-3 border-2 rounded-xl cursor-pointer transition-all"
                               :class="customization.options.some(o => o.id === option.id)
                                   ? 'border-amber-400 bg-amber-50'
                                   : 'border-gray-100 hover:border-gray-200 bg-white'">
                            <input type="checkbox"
                                   :checked="customization.options.some(o => o.id === option.id)"
                                   @change="toggleOption(option)"
                                   class="w-4 h-4 text-amber-400 rounded border-gray-300 mr-3 accent-amber-400">
                            <span class="flex-1 text-sm font-medium text-gray-700" x-text="option.name"></span>
                            <span class="text-amber-600 text-sm font-semibold"
                                  x-text="option.extra_price > 0 ? '+' + formatPrice(option.extra_price) : 'Gratuit'"></span>
                        </label>
                    </template>
                </div>
            </div>

            <!-- Notes -->
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-600 mb-2">Notes spéciales</label>
                <textarea x-model="customization.notes"
                          placeholder="Ex: Sans gluten, moins salé, bien cuit..."
                          class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-700 placeholder-gray-400 focus:ring-2 focus:ring-amber-400 focus:border-transparent resize-none outline-none"
                          rows="2"></textarea>
            </div>

            <!-- Quantity -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-600 mb-3">Quantité</label>
                <div class="flex items-center justify-center gap-6">
                    <button @click="customization.quantity = Math.max(1, customization.quantity - 1)"
                            class="w-11 h-11 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center transition-colors active:scale-95">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                        </svg>
                    </button>
                    <span class="text-3xl font-bold text-gray-800 w-12 text-center" x-text="customization.quantity"></span>
                    <button @click="customization.quantity++"
                            class="w-11 h-11 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center transition-colors active:scale-95">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Price + Add -->
            <div class="flex items-center gap-4">
                <div>
                    <p class="text-xs text-gray-400">Prix total</p>
                    <p class="text-2xl font-bold text-gray-800" x-text="formatPrice(currentPrice)"></p>
                </div>
                <button @click="addToCart()"
                        class="flex-1 bg-amber-400 text-slate-900 py-4 rounded-xl font-semibold transition-all hover:bg-amber-500 active:scale-95 flex items-center justify-center text-sm shadow-sm shadow-amber-400/20">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Ajouter au panier
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════
     ORDER SUCCESS TOAST
═══════════════════════════════════════ -->
<div x-show="orderSuccess"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 -translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 -translate-y-4"
     class="fixed top-4 left-4 right-4 bg-gray-900 border border-gray-800 text-white px-5 py-4 rounded-xl shadow-xl z-[70]">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 bg-green-500 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <div>
            <p class="font-semibold text-sm">Commande envoyée !</p>
            <p class="text-xs text-gray-400 mt-0.5">Votre commande a été transmise à la cuisine.</p>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════
     CALL WAITER MODAL
═══════════════════════════════════════ -->
<div x-show="showCallWaiterModal"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 drawer-backdrop flex items-end z-50"
     @click.self="showCallWaiterModal = false">
    <div x-show="showCallWaiterModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full"
         class="bg-gray-50 rounded-t-2xl w-full max-h-[80vh] overflow-y-auto">
        <div class="p-6">
            <div class="w-10 h-1 bg-gray-200 rounded-full mx-auto mb-6"></div>

            <div class="text-center mb-6">
                <div class="w-14 h-14 bg-amber-400 rounded-xl shadow-sm shadow-amber-400/30 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-lg text-gray-800">Appeler un serveur</h3>
                <p class="text-gray-400 text-sm mt-1">Table <span x-text="table?.code || '...'"></span></p>
            </div>

            <div class="space-y-3 mb-6">
                <button @click="callWaiter('SERVICE')"
                        :disabled="waiterCallLoading"
                        class="w-full flex items-center p-4 bg-white border border-gray-100 rounded-xl hover:border-blue-200 hover:bg-blue-50/50 transition-all group">
                    <div class="w-11 h-11 bg-blue-500 rounded-xl flex items-center justify-center mr-4 group-hover:scale-105 transition-transform flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <div class="text-left">
                        <p class="font-semibold text-gray-800 text-sm">Demander un service</p>
                        <p class="text-xs text-gray-400 mt-0.5">Commander, addition, serviettes...</p>
                    </div>
                </button>

                <button @click="callWaiter('QUESTION')"
                        :disabled="waiterCallLoading"
                        class="w-full flex items-center p-4 bg-white border border-gray-100 rounded-xl hover:border-amber-200 hover:bg-amber-50/50 transition-all group">
                    <div class="w-11 h-11 bg-amber-400 rounded-xl flex items-center justify-center mr-4 group-hover:scale-105 transition-transform flex-shrink-0">
                        <svg class="w-5 h-5 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="text-left">
                        <p class="font-semibold text-gray-800 text-sm">J'ai une question</p>
                        <p class="text-xs text-gray-400 mt-0.5">Allergènes, ingrédients, conseils...</p>
                    </div>
                </button>

                <button @click="callWaiter('URGENCE')"
                        :disabled="waiterCallLoading"
                        class="w-full flex items-center p-4 bg-white border border-gray-100 rounded-xl hover:border-red-200 hover:bg-red-50/50 transition-all group">
                    <div class="w-11 h-11 bg-red-500 rounded-xl flex items-center justify-center mr-4 group-hover:scale-105 transition-transform flex-shrink-0 animate-pulse">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="text-left">
                        <p class="font-semibold text-gray-800 text-sm">Urgence</p>
                        <p class="text-xs text-gray-400 mt-0.5">Problème urgent, incident...</p>
                    </div>
                </button>
            </div>

            <div x-show="waiterCallLoading" class="text-center py-3">
                <div class="w-7 h-7 border-2 border-gray-200 border-t-amber-400 rounded-full animate-spin mx-auto mb-2"></div>
                <p class="text-gray-400 text-sm">Envoi en cours...</p>
            </div>

            <button @click="showCallWaiterModal = false"
                    :disabled="waiterCallLoading"
                    class="w-full py-3 bg-white text-gray-600 rounded-xl text-sm font-medium border border-gray-100 hover:bg-gray-50 transition-colors">
                Annuler
            </button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════
     WAITER CALL SUCCESS TOAST
═══════════════════════════════════════ -->
<div x-show="waiterCallSuccess"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 -translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 -translate-y-4"
     class="fixed top-4 left-4 right-4 bg-gray-900 border border-gray-800 text-white px-5 py-4 rounded-xl shadow-xl z-[70]">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 bg-amber-400 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
        </div>
        <div>
            <p class="font-semibold text-sm">Serveur appelé !</p>
            <p class="text-xs text-gray-400 mt-0.5">Un serveur arrive à votre table.</p>
        </div>
    </div>
</div>

</body>
</html>
