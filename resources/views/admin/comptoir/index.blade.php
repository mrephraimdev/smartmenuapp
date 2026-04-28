@extends('layouts.admin')

@section('title', 'Prise de Commande')
@section('page-title', 'Prise de Commande')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="hover:text-amber-600">Dashboard</a>
    <span class="mx-2">/</span>
    <span>Comptoir</span>
@endsection

@push('head')
<style>
    .dish-card { transition: transform 0.1s ease, box-shadow 0.1s ease; }
    .dish-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,0.1); }
    .dish-card:active { transform: scale(0.97); }
    .cart-item-enter { animation: slideIn 0.2s ease; }
    @keyframes slideIn { from { opacity: 0; transform: translateX(8px); } to { opacity: 1; transform: translateX(0); } }
    .qty-badge { min-width: 1.4rem; }
    @media (min-width: 1024px) {
        .comptoir-catalog, .comptoir-cart { display: flex !important; }
    }
</style>
@endpush

@section('content')
<div
    x-data="comptoir()"
    x-init="init()"
    class="flex flex-col gap-3 h-[calc(100vh-7.5rem)]"
>

{{-- Tab bar mobile --}}
<div class="lg:hidden flex flex-shrink-0 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <button @click="showCart = false"
            :class="!showCart ? 'bg-amber-500 text-white' : 'text-gray-600 hover:bg-gray-50'"
            class="flex-1 py-3 text-sm font-bold transition-colors">
        Catalogue
    </button>
    <button @click="showCart = true"
            :class="showCart ? 'bg-amber-500 text-white' : 'text-gray-600 hover:bg-gray-50'"
            class="flex-1 py-3 text-sm font-bold transition-colors flex items-center justify-center gap-2">
        Panier
        <span x-show="cart.length > 0"
              class="inline-flex items-center justify-center w-5 h-5 bg-white text-amber-600 text-xs font-extrabold rounded-full"
              x-text="cart.length"></span>
    </button>
</div>

{{-- Panneaux --}}
<div class="flex gap-3 flex-1 overflow-hidden">

{{-- ═══════════════════════════════════════════════════════
     COLONNE GAUCHE — Catalogue
═══════════════════════════════════════════════════════ --}}
<div class="comptoir-catalog flex-1 flex flex-col min-w-0 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
     x-show="!showCart">

    {{-- Header catalogue --}}
    <div class="px-4 pt-3 pb-2 border-b border-gray-100 flex-shrink-0 bg-gray-50">
        <div class="flex items-center gap-2 mb-2">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                </svg>
                <input type="text" x-model="search" placeholder="Rechercher un plat…"
                       class="w-full pl-9 pr-3 py-2 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent">
                <button x-show="search" @click="search=''" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <span class="text-xs text-gray-400 whitespace-nowrap">
                {{ $menus->sum(fn($m) => $m->categories->sum(fn($c) => $c->dishes->count())) }} plats
            </span>
        </div>

        {{-- Onglets catégories --}}
        <div class="flex gap-1.5 overflow-x-auto pb-1 scrollbar-none">
            <button @click="activeCategory = null"
                    :class="activeCategory === null
                        ? 'bg-amber-500 text-white shadow-sm'
                        : 'bg-white text-gray-600 border border-gray-200 hover:border-amber-300 hover:text-amber-600'"
                    class="flex-shrink-0 px-3 py-1.5 text-xs font-semibold rounded-lg transition-all">
                Tous
            </button>
            @foreach($menus as $menu)
                @foreach($menu->categories as $category)
                    @if($category->dishes->count() > 0)
                    <button @click="activeCategory = {{ $category->id }}"
                            :class="activeCategory === {{ $category->id }}
                                ? 'bg-amber-500 text-white shadow-sm'
                                : 'bg-white text-gray-600 border border-gray-200 hover:border-amber-300 hover:text-amber-600'"
                            class="flex-shrink-0 px-3 py-1.5 text-xs font-semibold rounded-lg transition-all whitespace-nowrap">
                        {{ $category->name }}
                        <span class="ml-1 opacity-70">({{ $category->dishes->count() }})</span>
                    </button>
                    @endif
                @endforeach
            @endforeach
        </div>
    </div>

    {{-- Grille des plats --}}
    <div class="flex-1 overflow-y-auto p-3">
        @if($menus->isEmpty())
            <div class="flex flex-col items-center justify-center h-full text-gray-400 py-12">
                <svg class="w-14 h-14 mb-4 opacity-30" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.87c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513m-3-4.87v-1.5m-6 1.5v-1.5m12 9.75l-1.5.75a3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-1.5-.75m16.5 0l-1.5.75a3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-1.5-.75"/>
                </svg>
                <p class="font-semibold text-lg">Aucun menu actif</p>
                <p class="text-sm mt-1">Créez un menu avec des plats pour commencer.</p>
            </div>
        @else
            <div class="grid grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-2.5">
                @foreach($menus as $menu)
                    @foreach($menu->categories as $category)
                        @foreach($category->dishes as $dish)
                        <div
                            class="dish-card relative bg-white rounded-xl border border-gray-200 overflow-hidden cursor-pointer select-none"
                            x-show="(activeCategory === null || activeCategory === {{ $category->id }})
                                  && (search === '' || '{{ mb_strtolower($dish->name) }}'.includes(search.toLowerCase()))"
                            @click="addToCart({{ $dish->id }}, '{{ addslashes($dish->name) }}', {{ $dish->price_base }})"
                        >
                            {{-- Image ou placeholder --}}
                            @if($dish->photo_url)
                            <img src="{{ $dish->photo_url }}" alt="{{ $dish->name }}" class="w-full h-24 object-cover">
                            @else
                            <div class="w-full h-24 bg-gradient-to-br from-amber-50 to-orange-100 flex items-center justify-center">
                                <svg class="w-9 h-9 text-amber-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.87c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513m-3-4.87v-1.5m-6 1.5v-1.5m12 9.75l-1.5.75a3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-1.5-.75m16.5 0l-1.5.75a3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-1.5-.75"/>
                                </svg>
                            </div>
                            @endif

                            {{-- Badge quantité --}}
                            <template x-if="getQty({{ $dish->id }}) > 0">
                                <div class="absolute top-1.5 right-1.5 w-6 h-6 bg-amber-500 rounded-full flex items-center justify-center shadow-md">
                                    <span class="text-white text-xs font-extrabold" x-text="getQty({{ $dish->id }})"></span>
                                </div>
                            </template>

                            {{-- Infos --}}
                            <div class="p-2.5">
                                <p class="text-sm font-semibold text-gray-900 line-clamp-2 leading-tight mb-1">{{ $dish->name }}</p>
                                <div class="flex items-center justify-between">
                                    <span class="text-amber-600 font-bold text-sm">{{ number_format($dish->price_base, 0, ',', ' ') }} F</span>
                                    <template x-if="getQty({{ $dish->id }}) === 0">
                                        <span class="w-6 h-6 bg-amber-500 rounded-full flex items-center justify-center flex-shrink-0">
                                            <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                            </svg>
                                        </span>
                                    </template>
                                    <template x-if="getQty({{ $dish->id }}) > 0">
                                        <div class="flex items-center gap-1" @click.stop>
                                            <button @click.stop="decrementCart({{ $dish->id }})"
                                                    class="w-6 h-6 bg-red-100 hover:bg-red-200 text-red-600 rounded-full flex items-center justify-center font-bold text-sm leading-none transition-colors">
                                                −
                                            </button>
                                            <span class="text-xs font-bold text-amber-600 qty-badge text-center" x-text="getQty({{ $dish->id }})"></span>
                                            <button @click.stop="addToCart({{ $dish->id }}, '{{ addslashes($dish->name) }}', {{ $dish->price_base }})"
                                                    class="w-6 h-6 bg-amber-500 hover:bg-amber-600 text-white rounded-full flex items-center justify-center font-bold text-sm leading-none transition-colors">
                                                +
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @endforeach
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     COLONNE DROITE — Commande & Paiement
═══════════════════════════════════════════════════════ --}}
<div class="comptoir-cart w-full lg:w-[320px] flex flex-col bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex-shrink-0"
     x-show="showCart">

    {{-- Header panier --}}
    <div class="px-4 py-3 bg-slate-900 flex items-center justify-between flex-shrink-0">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
            </svg>
            <span class="text-white font-bold text-sm">Commande</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="bg-amber-500 text-white text-xs font-extrabold px-2 py-0.5 rounded-full"
                  x-text="cart.length + ' art.'"></span>
            <button @click="clearCart()" x-show="cart.length > 0"
                    class="p-1 text-gray-400 hover:text-red-400 transition-colors" title="Vider">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Infos commande --}}
    <div class="px-3 py-2.5 border-b border-gray-100 flex-shrink-0 bg-gray-50 space-y-2">
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Table</label>
                <select x-model="tableId"
                        class="w-full px-2.5 py-1.5 text-sm bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 text-gray-700">
                    <option value="">Comptoir</option>
                    @foreach($tables as $table)
                        <option value="{{ $table->id }}">{{ $table->label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Client</label>
                <input type="text" x-model="customerName" placeholder="Nom (optionnel)"
                       class="w-full px-2.5 py-1.5 text-sm bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 text-gray-700">
            </div>
        </div>
    </div>

    {{-- Articles --}}
    <div class="flex-1 overflow-y-auto px-3 py-2">
        <template x-if="cart.length === 0">
            <div class="flex flex-col items-center justify-center h-32 text-gray-300">
                <svg class="w-10 h-10 mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                </svg>
                <p class="text-sm font-medium">Panier vide</p>
                <p class="text-xs mt-0.5">Cliquez sur un plat pour l'ajouter</p>
            </div>
        </template>

        <div class="space-y-1.5">
            <template x-for="item in cart" :key="item.dish_id">
                <div class="flex items-center gap-2 px-2.5 py-2 bg-gray-50 rounded-xl border border-gray-100">
                    {{-- Qty controls --}}
                    <div class="flex items-center gap-1 flex-shrink-0">
                        <button @click="decrementCart(item.dish_id)"
                                class="w-6 h-6 bg-red-100 hover:bg-red-200 text-red-600 rounded-full flex items-center justify-center text-sm font-bold leading-none transition-colors">−</button>
                        <span class="text-sm font-extrabold text-amber-600 w-5 text-center" x-text="item.quantity"></span>
                        <button @click="incrementCart(item.dish_id)"
                                class="w-6 h-6 bg-amber-500 hover:bg-amber-600 text-white rounded-full flex items-center justify-center text-sm font-bold leading-none transition-colors">+</button>
                    </div>
                    {{-- Nom + note + prix --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-gray-900 truncate" x-text="item.name"></p>
                        <div class="flex items-center justify-between mt-0.5">
                            <span class="text-xs text-gray-400" x-text="formatPrice(item.unit_price) + ' F × ' + item.quantity"></span>
                            <span class="text-xs font-bold text-amber-700" x-text="formatPrice(item.unit_price * item.quantity) + ' F'"></span>
                        </div>
                    </div>
                    {{-- Supprimer --}}
                    <button @click="removeFromCart(item.dish_id)"
                            class="flex-shrink-0 w-5 h-5 text-gray-300 hover:text-red-500 transition-colors">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>
        </div>
    </div>

    {{-- Zone paiement & validation --}}
    <div class="px-3 py-3 border-t border-gray-100 flex-shrink-0 bg-gray-50 space-y-2.5">

        {{-- Note commande (compacte) --}}
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Note</label>
            <input type="text" x-model="orderNotes" placeholder="Allergies, instructions spéciales…"
                   class="w-full px-2.5 py-1.5 text-sm bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 text-gray-700">
        </div>

        {{-- Mode de paiement --}}
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Mode de paiement</label>
            <div class="grid grid-cols-3 gap-1.5 mb-1.5">
                @foreach([
                    ['CASH','💵 Espèces','bg-green-50 text-green-700 border-green-200'],
                    ['CARD','💳 Carte','bg-blue-50 text-blue-700 border-blue-200'],
                    ['ORANGE_MONEY','🟠 Orange','bg-orange-50 text-orange-700 border-orange-200'],
                    ['MTN_MOMO','🟡 MTN','bg-yellow-50 text-yellow-700 border-yellow-200'],
                    ['WAVE','🌊 Wave','bg-cyan-50 text-cyan-700 border-cyan-200'],
                    ['MOOV_MONEY','🔵 Moov','bg-indigo-50 text-indigo-700 border-indigo-200'],
                ] as [$val,$label,$cls])
                <button @click="paymentMethod = '{{ $val }}'"
                        :class="paymentMethod === '{{ $val }}' ? 'ring-2 ring-amber-500 font-extrabold scale-105' : ''"
                        class="px-1.5 py-2 text-xs rounded-xl border {{ $cls }} transition-all text-center font-semibold">
                    {{ $label }}
                </button>
                @endforeach
            </div>
            <button @click="paymentMethod = ''"
                    :class="paymentMethod === '' ? 'ring-2 ring-gray-400 font-bold' : ''"
                    class="w-full px-2 py-1.5 text-xs rounded-xl border border-gray-200 bg-white text-gray-600 transition-all font-medium">
                Sans paiement (encaissement différé)
            </button>
        </div>

        {{-- Montant reçu (espèces) --}}
        <template x-if="paymentMethod === 'CASH'">
            <div class="bg-white border border-gray-200 rounded-xl p-3 space-y-2">
                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide">Montant reçu (FCFA)</label>
                <input type="number" x-model.number="amountReceived" :min="total()" placeholder="Ex: 5000"
                       class="w-full px-3 py-2 text-base font-bold bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 text-gray-900 text-center">
                {{-- Raccourcis montants --}}
                <div class="grid grid-cols-3 gap-1.5">
                    <button @click="amountReceived = total()"
                            class="py-1.5 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors">
                        Exact
                    </button>
                    <button @click="amountReceived = Math.ceil(total()/1000)*1000"
                            class="py-1.5 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors"
                            x-text="formatPrice(Math.ceil(total()/1000)*1000) + ' F'">
                    </button>
                    <button @click="amountReceived = Math.ceil(total()/5000)*5000"
                            class="py-1.5 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors"
                            x-text="formatPrice(Math.ceil(total()/5000)*5000) + ' F'">
                    </button>
                </div>
                <div x-show="amountReceived > 0" class="flex items-center justify-between text-sm font-bold">
                    <span class="text-gray-500">Monnaie à rendre :</span>
                    <span :class="amountReceived >= total() ? 'text-emerald-600' : 'text-red-600'"
                          x-text="formatPrice(Math.max(0, amountReceived - total())) + ' FCFA'"></span>
                </div>
            </div>
        </template>

        {{-- Total + Validation --}}
        <div class="bg-white border border-gray-200 rounded-xl p-3">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-bold text-gray-700">Total commande</span>
                <span class="text-2xl font-extrabold text-amber-600" x-text="formatPrice(total()) + ' F'"></span>
            </div>
            <button @click="submitOrder()"
                    :disabled="cart.length === 0 || loading"
                    :class="paymentMethod
                        ? 'bg-emerald-500 hover:bg-emerald-600'
                        : 'bg-amber-500 hover:bg-amber-600'"
                    class="w-full py-3 text-white font-extrabold rounded-xl transition-colors disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2 text-sm">
                <template x-if="loading">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </template>
                <template x-if="!loading">
                    <span>
                        <span x-show="!paymentMethod">Valider la commande</span>
                        <span x-show="paymentMethod">Valider &amp; Encaisser</span>
                    </span>
                </template>
            </button>
        </div>

        {{-- Feedback --}}
        <template x-if="feedback">
            <div :class="feedbackOk ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-700'"
                 class="px-3 py-2.5 rounded-xl border text-sm font-semibold flex items-center gap-2">
                <span x-show="feedbackOk">✓</span>
                <span x-show="!feedbackOk">✗</span>
                <span x-text="feedback"></span>
            </div>
        </template>
    </div>
</div>

</div>{{-- fin panneaux --}}
</div>
@endsection

@push('scripts')
<script>
function comptoir() {
    return {
        showCart: false,
        cart: [],
        search: '',
        activeCategory: null,
        tableId: '',
        customerName: '',
        orderNotes: '',
        paymentMethod: '',
        amountReceived: 0,
        loading: false,
        feedback: '',
        feedbackOk: false,

        init() {},

        addToCart(dishId, name, price) {
            const existing = this.cart.find(i => i.dish_id === dishId);
            if (existing) {
                existing.quantity++;
            } else {
                this.cart.unshift({ dish_id: dishId, name, unit_price: price, quantity: 1, notes: '' });
            }
        },

        incrementCart(dishId) {
            const item = this.cart.find(i => i.dish_id === dishId);
            if (item) item.quantity++;
        },

        decrementCart(dishId) {
            const idx = this.cart.findIndex(i => i.dish_id === dishId);
            if (idx === -1) return;
            this.cart[idx].quantity--;
            if (this.cart[idx].quantity <= 0) this.cart.splice(idx, 1);
        },

        removeFromCart(dishId) {
            this.cart = this.cart.filter(i => i.dish_id !== dishId);
        },

        getQty(dishId) {
            const item = this.cart.find(i => i.dish_id === dishId);
            return item ? item.quantity : 0;
        },

        clearCart() {
            if (this.cart.length === 0) return;
            this.cart = [];
            this.orderNotes = '';
            this.feedback = '';
            this.amountReceived = 0;
        },

        total() {
            return this.cart.reduce((sum, i) => sum + (i.unit_price * i.quantity), 0);
        },

        formatPrice(n) {
            return new Intl.NumberFormat('fr-FR').format(Math.round(n));
        },

        async submitOrder() {
            if (this.cart.length === 0) return;
            this.loading = true;
            this.feedback = '';

            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const payload = {
                table_id:        this.tableId || null,
                customer_name:   this.customerName || null,
                notes:           this.orderNotes || null,
                payment_method:  this.paymentMethod || null,
                amount_received: this.paymentMethod === 'CASH' ? (this.amountReceived || null) : null,
                items: this.cart.map(i => ({
                    dish_id:  i.dish_id,
                    quantity: i.quantity,
                    notes:    i.notes || null,
                })),
            };

            try {
                const res = await fetch('{{ route("admin.comptoir.store", $tenant->slug) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(payload),
                });
                const data = await res.json();

                this.feedbackOk = data.success;
                this.feedback = data.message;

                if (data.success) {
                    if (data.change > 0) {
                        this.feedback += ` — Monnaie : ${new Intl.NumberFormat('fr-FR').format(data.change)} FCFA`;
                    }
                    window.open(data.receipt_url, '_blank');
                    this.cart = [];
                    this.customerName = '';
                    this.orderNotes = '';
                    this.tableId = '';
                    this.paymentMethod = '';
                    this.amountReceived = 0;
                    setTimeout(() => this.feedback = '', 6000);
                }
            } catch (e) {
                this.feedbackOk = false;
                this.feedback = 'Erreur réseau. Veuillez réessayer.';
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
@endpush
