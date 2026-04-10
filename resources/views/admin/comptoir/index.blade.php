@extends('layouts.admin')

@section('title', 'Commande Comptoir')
@section('page-title', 'Prise de Commande au Comptoir')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="hover:text-amber-600">Dashboard</a>
    <span class="mx-2">/</span>
    <span>Comptoir</span>
@endsection

@push('head')
<style>
    .dish-card:hover { transform: translateY(-2px); }
    .dish-card { transition: all 0.15s ease; }
    .qty-btn { user-select: none; }
</style>
@endpush

@section('content')
<div
    x-data="comptoir()"
    x-init="init()"
    class="flex gap-4 h-[calc(100vh-8rem)] overflow-hidden"
>
    {{-- ═══════════════════════════════════════════════════════
         PANNEAU GAUCHE — Catalogue de plats
    ═══════════════════════════════════════════════════════ --}}
    <div class="flex-1 flex flex-col overflow-hidden bg-white rounded-2xl shadow-sm border border-gray-100">

        {{-- Header catalogue --}}
        <div class="px-4 py-3 border-b border-gray-100 flex-shrink-0">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-sm font-bold text-gray-900">Catalogue des plats</h2>
                <span class="text-xs text-gray-400">{{ $menus->sum(fn($m) => $m->categories->sum(fn($c) => $c->dishes->count())) }} plats</span>
            </div>
            {{-- Recherche --}}
            <input
                type="text"
                x-model="search"
                placeholder="Rechercher un plat..."
                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent"
            >
        </div>

        {{-- Onglets des catégories --}}
        <div class="flex-shrink-0 border-b border-gray-100 overflow-x-auto">
            <div class="flex gap-1 px-4 py-2">
                <button
                    @click="activeCategory = null"
                    :class="activeCategory === null ? 'bg-amber-500 text-white' : 'text-gray-500 hover:bg-gray-100'"
                    class="flex-shrink-0 px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors"
                >Tous</button>
                @foreach($menus as $menu)
                    @foreach($menu->categories as $category)
                        @if($category->dishes->count() > 0)
                        <button
                            @click="activeCategory = {{ $category->id }}"
                            :class="activeCategory === {{ $category->id }} ? 'bg-amber-500 text-white' : 'text-gray-500 hover:bg-gray-100'"
                            class="flex-shrink-0 px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors whitespace-nowrap"
                        >{{ $category->name }}</button>
                        @endif
                    @endforeach
                @endforeach
            </div>
        </div>

        {{-- Grille des plats --}}
        <div class="flex-1 overflow-y-auto p-3">
            @if($menus->isEmpty())
                <div class="flex flex-col items-center justify-center h-full text-gray-400">
                    <svg class="w-12 h-12 mb-3 opacity-40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                    </svg>
                    <p class="font-medium">Aucun menu actif</p>
                    <p class="text-sm mt-1">Créez d'abord un menu avec des plats.</p>
                </div>
            @else
                <div class="grid grid-cols-2 xl:grid-cols-3 gap-2">
                    @foreach($menus as $menu)
                        @foreach($menu->categories as $category)
                            @foreach($category->dishes as $dish)
                            <div
                                class="dish-card bg-gray-50 rounded-xl border border-gray-100 p-2.5 cursor-pointer hover:border-amber-300 hover:shadow-sm"
                                x-show="(activeCategory === null || activeCategory === {{ $category->id }}) && (search === '' || '{{ strtolower($dish->name) }}'.includes(search.toLowerCase()))"
                                @click="addToCart({{ $dish->id }}, '{{ addslashes($dish->name) }}', {{ $dish->price_base }})"
                            >
                                @if($dish->photo_url)
                                <img src="{{ $dish->photo_url }}" alt="{{ $dish->name }}"
                                     class="w-full h-20 object-cover rounded-lg mb-2">
                                @else
                                <div class="w-full h-20 bg-amber-50 rounded-lg mb-2 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-amber-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.87c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513m-3-4.87v-1.5m-6 1.5v-1.5m12 9.75l-1.5.75a3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-1.5-.75m16.5 0l-1.5.75a3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-1.5-.75"/>
                                    </svg>
                                </div>
                                @endif
                                <p class="text-sm font-semibold text-gray-900 leading-tight line-clamp-2">{{ $dish->name }}</p>
                                <p class="text-xs text-amber-600 font-bold mt-1">{{ number_format($dish->price_base, 0, ',', ' ') }} FCFA</p>

                                {{-- Indicateur quantité dans le panier --}}
                                <template x-if="getQty({{ $dish->id }}) > 0">
                                    <div class="mt-2 flex items-center justify-between">
                                        <button class="qty-btn w-7 h-7 rounded-lg bg-gray-200 hover:bg-red-100 text-gray-600 hover:text-red-600 flex items-center justify-center font-bold text-sm transition-colors"
                                                @click.stop="decrementCart({{ $dish->id }})">−</button>
                                        <span class="text-sm font-bold text-amber-600 min-w-[1.5rem] text-center" x-text="getQty({{ $dish->id }})"></span>
                                        <button class="qty-btn w-7 h-7 rounded-lg bg-amber-500 hover:bg-amber-600 text-white flex items-center justify-center font-bold text-sm transition-colors"
                                                @click.stop="addToCart({{ $dish->id }}, '{{ addslashes($dish->name) }}', {{ $dish->price_base }})">+</button>
                                    </div>
                                </template>
                                <template x-if="getQty({{ $dish->id }}) === 0">
                                    <div class="mt-2">
                                        <button class="w-full bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold py-1.5 rounded-lg transition-colors"
                                                @click.stop="addToCart({{ $dish->id }}, '{{ addslashes($dish->name) }}', {{ $dish->price_base }})">
                                            + Ajouter
                                        </button>
                                    </div>
                                </template>
                            </div>
                            @endforeach
                        @endforeach
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         PANNEAU DROIT — Panier, Paiement & Validation
    ═══════════════════════════════════════════════════════ --}}
    <div class="w-80 flex flex-col bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex-shrink-0">

        {{-- Header --}}
        <div class="px-4 py-3 border-b border-gray-100 flex-shrink-0 bg-slate-900">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                    </svg>
                    Panier
                </h2>
                <span class="text-xs font-bold bg-amber-500 text-white px-2 py-0.5 rounded-full" x-text="cart.length + ' article' + (cart.length > 1 ? 's' : '')"></span>
            </div>
        </div>

        {{-- Infos commande --}}
        <div class="px-4 py-2.5 border-b border-gray-100 space-y-2 flex-shrink-0 bg-gray-50">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Table (optionnel)</label>
                <select x-model="tableId"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white">
                    <option value="">— Comptoir / Emporter</option>
                    @foreach($tables as $table)
                        <option value="{{ $table->id }}">{{ $table->label }} ({{ $table->code }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Nom client (optionnel)</label>
                <input type="text" x-model="customerName" placeholder="Ex: Jean Dupont"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white">
            </div>
        </div>

        {{-- Articles --}}
        <div class="flex-1 overflow-y-auto px-4 py-2.5">
            <template x-if="cart.length === 0">
                <div class="flex flex-col items-center justify-center h-32 text-gray-300">
                    <svg class="w-10 h-10 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                    </svg>
                    <p class="text-sm">Panier vide</p>
                </div>
            </template>
            <div class="space-y-2">
                <template x-for="item in cart" :key="item.dish_id">
                    <div class="flex items-start gap-3 p-2.5 rounded-xl bg-gray-50 border border-gray-100">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900" x-text="item.name"></p>
                            <p class="text-xs text-amber-600 font-medium" x-text="formatPrice(item.unit_price) + ' × ' + item.quantity"></p>
                            <input type="text" x-model="item.notes" placeholder="Note (ex: sans sauce)"
                                   class="mt-1.5 w-full text-xs px-2 py-1 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-amber-400 bg-white">
                        </div>
                        <div class="flex flex-col items-center gap-1 flex-shrink-0">
                            <button @click="incrementCart(item.dish_id)" class="w-6 h-6 bg-amber-500 hover:bg-amber-600 text-white rounded-md flex items-center justify-center text-xs font-bold">+</button>
                            <span class="text-sm font-bold text-gray-700 min-w-[1.2rem] text-center" x-text="item.quantity"></span>
                            <button @click="decrementCart(item.dish_id)" class="w-6 h-6 bg-gray-200 hover:bg-red-100 text-gray-600 hover:text-red-600 rounded-md flex items-center justify-center text-xs font-bold">−</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Zone bas : paiement + validation --}}
        <div class="px-4 py-3 border-t border-gray-100 flex-shrink-0 space-y-2.5 bg-gray-50">

            {{-- Note commande --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Note commande</label>
                <textarea x-model="orderNotes" rows="1" placeholder="Allergies, instructions..."
                          class="w-full px-3 py-1.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-400 resize-none bg-white"></textarea>
            </div>

            {{-- PAIEMENT --}}
            <div class="border border-gray-200 rounded-xl overflow-hidden bg-white">
                <div class="px-3 py-2 bg-slate-800 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
                    </svg>
                    <span class="text-xs font-bold text-white uppercase tracking-wider">Mode de paiement</span>
                </div>
                <div class="p-2.5 space-y-2">
                    {{-- Boutons modes de paiement --}}
                    <div class="grid grid-cols-3 gap-1.5">
                        @foreach([['CASH','Espèces','text-green-700 bg-green-50 border-green-200'],['CARD','Carte','text-blue-700 bg-blue-50 border-blue-200'],['ORANGE_MONEY','Orange','text-orange-700 bg-orange-50 border-orange-200'],['MTN_MOMO','MTN','text-yellow-700 bg-yellow-50 border-yellow-200'],['WAVE','Wave','text-cyan-700 bg-cyan-50 border-cyan-200'],['MOOV_MONEY','Moov','text-indigo-700 bg-indigo-50 border-indigo-200']] as [$val,$label,$cls])
                        <button
                            @click="paymentMethod = '{{ $val }}'"
                            :class="paymentMethod === '{{ $val }}' ? 'ring-2 ring-amber-500 font-bold' : ''"
                            class="px-1 py-1.5 text-xs rounded-lg border {{ $cls }} transition-all text-center">
                            {{ $label }}
                        </button>
                        @endforeach
                    </div>
                    {{-- Sans paiement --}}
                    <button @click="paymentMethod = ''"
                            :class="paymentMethod === '' ? 'ring-2 ring-gray-400 font-bold' : ''"
                            class="w-full px-2 py-1.5 text-xs rounded-lg border border-gray-200 bg-gray-50 text-gray-600 transition-all">
                        Sans paiement (à encaisser plus tard)
                    </button>
                    {{-- Montant reçu (espèces) --}}
                    <template x-if="paymentMethod === 'CASH'">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Montant reçu (FCFA)</label>
                            <input type="number" x-model.number="amountReceived" :min="total()"
                                   class="w-full px-3 py-1.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white"
                                   placeholder="Ex: 5000">
                            <div class="flex justify-between text-xs font-bold" x-show="amountReceived > 0">
                                <span class="text-gray-500">Monnaie à rendre :</span>
                                <span class="text-emerald-600" x-text="formatPrice(Math.max(0, amountReceived - total())) + ' FCFA'"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Total --}}
            <div class="flex items-center justify-between py-1 border-t border-gray-200">
                <span class="text-sm font-bold text-gray-700">Total</span>
                <span class="text-lg font-extrabold text-amber-600" x-text="formatPrice(total()) + ' FCFA'"></span>
            </div>

            {{-- Boutons action --}}
            <div class="grid grid-cols-2 gap-2">
                <button @click="clearCart()" :disabled="cart.length === 0"
                        class="px-3 py-2.5 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors disabled:opacity-40">
                    Vider
                </button>
                <button @click="submitOrder()" :disabled="cart.length === 0 || loading"
                        :class="paymentMethod ? 'bg-emerald-500 hover:bg-emerald-600' : 'bg-amber-500 hover:bg-amber-600'"
                        class="px-3 py-2.5 text-sm font-semibold text-white rounded-xl transition-colors disabled:opacity-40 flex items-center justify-center gap-2">
                    <template x-if="loading">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </template>
                    <template x-if="!loading">
                        <span x-text="paymentMethod ? 'Valider & Encaisser' : 'Valider la commande'"></span>
                    </template>
                </button>
            </div>

            {{-- Feedback --}}
            <template x-if="feedback">
                <div :class="feedbackOk ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800'"
                     class="flex items-start gap-2 p-3 rounded-xl border text-sm font-medium">
                    <span x-text="feedback"></span>
                </div>
            </template>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function comptoir() {
    return {
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
                this.cart.push({ dish_id: dishId, name, unit_price: price, quantity: 1, notes: '' });
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
            if (this.cart[idx].quantity <= 0) {
                this.cart.splice(idx, 1);
            }
        },

        getQty(dishId) {
            const item = this.cart.find(i => i.dish_id === dishId);
            return item ? item.quantity : 0;
        },

        clearCart() {
            this.cart = [];
            this.feedback = '';
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
                    // Afficher la monnaie à rendre si espèces
                    if (data.change > 0) {
                        this.feedback += ` — Monnaie à rendre : ${new Intl.NumberFormat('fr-FR').format(data.change)} FCFA`;
                    }
                    // Ouvrir le reçu (paiement ou commande) dans un nouvel onglet
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
