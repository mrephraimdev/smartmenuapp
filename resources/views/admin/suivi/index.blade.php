@extends('layouts.admin')

@section('title', 'Suivi des Commandes')
@section('page-title', 'Suivi des Commandes')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="hover:text-amber-600">Dashboard</a>
    <span class="mx-2">/</span>
    <span>Suivi</span>
@endsection

@push('head')
<style>
    .order-card { transition: all 0.2s ease; }
    .order-card:hover { transform: translateY(-1px); box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
</style>
@endpush

@section('content')
<div
    x-data="suiviBoard()"
    x-init="init()"
    class="flex flex-col h-[calc(100vh-7.5rem)] gap-3"
>
    {{-- Barre de contrôle --}}
    <div class="flex items-center justify-between flex-shrink-0">
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
                <span class="text-sm text-gray-500 font-medium">Actualisation auto (20s)</span>
            </div>
            <span class="text-xs text-gray-400 bg-gray-100 px-2.5 py-1 rounded-lg" x-text="'Mis à jour ' + lastUpdate"></span>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500">
                <span class="font-bold text-gray-900" x-text="totalOrders"></span>
                <span x-text="totalOrders !== 1 ? ' commandes actives' : ' commande active'"></span>
            </span>
            <button @click="load()"
                    class="flex items-center gap-1.5 px-3 py-2 text-sm font-semibold bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl transition-colors">
                <svg class="w-4 h-4" :class="refreshing ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                </svg>
                Actualiser
            </button>
            <a href="{{ route('admin.comptoir.index', $tenant->slug) }}"
               class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold bg-amber-500 text-white hover:bg-amber-600 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Nouvelle commande
            </a>
        </div>
    </div>

    {{-- Board Kanban --}}
    <div class="flex gap-3 flex-1 overflow-hidden">

        {{-- ─── RECU ─── --}}
        <div class="flex-1 flex flex-col rounded-2xl shadow-sm border border-gray-100 bg-white overflow-hidden" style="border-top: 3px solid #3b82f6">
            <div class="px-3 py-2.5 border-b border-gray-100 flex items-center gap-2 flex-shrink-0">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                <span class="text-sm font-bold text-gray-900">Reçues</span>
                <span class="ml-auto text-xs font-bold bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full" x-text="orders.RECU.length"></span>
            </div>
            <div class="flex-1 overflow-y-auto p-2.5 space-y-2">
                <div x-show="orders.RECU.length === 0" class="text-center text-gray-300 py-10 text-sm">Aucune commande</div>
                <template x-for="order in orders.RECU" :key="order.id">
                    <div class="order-card bg-white border border-blue-100 rounded-xl p-3 shadow-sm">
                        {{-- Header carte --}}
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <span class="text-sm font-bold text-gray-900" x-text="'#' + order.order_number"></span>
                                <span class="ml-2 text-xs text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full font-semibold">REÇUE</span>
                            </div>
                            <span class="text-xs text-gray-400" x-text="order.created_raw"></span>
                        </div>
                        {{-- Table & info --}}
                        <div class="text-xs text-gray-500 mb-2">
                            <span x-show="order.table" x-text="'Table ' + (order.table ? order.table.label : '')"></span>
                            <span x-show="!order.table" class="italic">Comptoir</span>
                            <span class="mx-1">·</span>
                            <span x-text="order.items_count + ' article' + (order.items_count > 1 ? 's' : '')"></span>
                            <span class="mx-1">·</span>
                            <span class="font-semibold text-amber-600" x-text="formatPrice(order.total) + ' FCFA'"></span>
                        </div>
                        {{-- Articles --}}
                        <ul class="mb-2.5 space-y-0.5">
                            <template x-for="item in order.items" :key="item.name">
                                <li class="text-xs text-gray-600 flex gap-1">
                                    <span class="font-medium text-gray-800" x-text="item.quantity + '×'"></span>
                                    <span x-text="item.name"></span>
                                </li>
                            </template>
                        </ul>
                        {{-- Notes --}}
                        <div x-show="order.notes" class="text-xs text-gray-400 italic mb-2 line-clamp-2" x-text="'📝 ' + order.notes"></div>
                        {{-- Boutons --}}
                        <div class="flex gap-1.5 mt-2">
                            <button @click="progress(order.id)"
                                    class="flex-1 py-2 text-xs font-bold bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors">
                                Démarrer préparation →
                            </button>
                            <button @click="cancel(order.id)"
                                    class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Annuler">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- ─── PREP ─── --}}
        <div class="flex-1 flex flex-col rounded-2xl shadow-sm border border-gray-100 bg-white overflow-hidden" style="border-top: 3px solid #f59e0b">
            <div class="px-3 py-2.5 border-b border-gray-100 flex items-center gap-2 flex-shrink-0">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                <span class="text-sm font-bold text-gray-900">En préparation</span>
                <span class="ml-auto text-xs font-bold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full" x-text="orders.PREP.length"></span>
            </div>
            <div class="flex-1 overflow-y-auto p-2.5 space-y-2">
                <div x-show="orders.PREP.length === 0" class="text-center text-gray-300 py-10 text-sm">Aucune commande</div>
                <template x-for="order in orders.PREP" :key="order.id">
                    <div class="order-card bg-white border border-amber-100 rounded-xl p-3 shadow-sm">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <span class="text-sm font-bold text-gray-900" x-text="'#' + order.order_number"></span>
                                <span class="ml-2 text-xs text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full font-semibold">EN PRÉP.</span>
                            </div>
                            <span class="text-xs text-gray-400" x-text="order.created_raw"></span>
                        </div>
                        <div class="text-xs text-gray-500 mb-2">
                            <span x-show="order.table" x-text="'Table ' + (order.table ? order.table.label : '')"></span>
                            <span x-show="!order.table" class="italic">Comptoir</span>
                            <span class="mx-1">·</span>
                            <span x-text="order.items_count + ' article' + (order.items_count > 1 ? 's' : '')"></span>
                            <span class="mx-1">·</span>
                            <span class="font-semibold text-amber-600" x-text="formatPrice(order.total) + ' FCFA'"></span>
                        </div>
                        <ul class="mb-2.5 space-y-0.5">
                            <template x-for="item in order.items" :key="item.name">
                                <li class="text-xs text-gray-600 flex gap-1">
                                    <span class="font-medium text-gray-800" x-text="item.quantity + '×'"></span>
                                    <span x-text="item.name"></span>
                                </li>
                            </template>
                        </ul>
                        <div x-show="order.notes" class="text-xs text-gray-400 italic mb-2 line-clamp-2" x-text="'📝 ' + order.notes"></div>
                        <div class="flex gap-1.5 mt-2">
                            <button @click="progress(order.id)"
                                    class="flex-1 py-2 text-xs font-bold bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg transition-colors">
                                Marquer Prête →
                            </button>
                            <button @click="cancel(order.id)"
                                    class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Annuler">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- ─── PRET ─── --}}
        <div class="flex-1 flex flex-col rounded-2xl shadow-sm border border-gray-100 bg-white overflow-hidden" style="border-top: 3px solid #10b981">
            <div class="px-3 py-2.5 border-b border-gray-100 flex items-center gap-2 flex-shrink-0">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <span class="text-sm font-bold text-gray-900">Prêtes à servir</span>
                <span class="ml-auto text-xs font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full" x-text="orders.PRET.length"></span>
            </div>
            <div class="flex-1 overflow-y-auto p-2.5 space-y-2">
                <div x-show="orders.PRET.length === 0" class="text-center text-gray-300 py-10 text-sm">Aucune commande</div>
                <template x-for="order in orders.PRET" :key="order.id">
                    <div class="order-card bg-white border border-emerald-100 rounded-xl p-3 shadow-sm">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <span class="text-sm font-bold text-gray-900" x-text="'#' + order.order_number"></span>
                                <span class="ml-2 text-xs text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full font-semibold">PRÊTE</span>
                            </div>
                            <span class="text-xs text-gray-400" x-text="order.created_raw"></span>
                        </div>
                        <div class="text-xs text-gray-500 mb-2">
                            <span x-show="order.table" x-text="'Table ' + (order.table ? order.table.label : '')"></span>
                            <span x-show="!order.table" class="italic">Comptoir</span>
                            <span class="mx-1">·</span>
                            <span x-text="order.items_count + ' article' + (order.items_count > 1 ? 's' : '')"></span>
                            <span class="mx-1">·</span>
                            <span class="font-semibold text-amber-600" x-text="formatPrice(order.total) + ' FCFA'"></span>
                        </div>
                        <ul class="mb-2.5 space-y-0.5">
                            <template x-for="item in order.items" :key="item.name">
                                <li class="text-xs text-gray-600 flex gap-1">
                                    <span class="font-medium text-gray-800" x-text="item.quantity + '×'"></span>
                                    <span x-text="item.name"></span>
                                </li>
                            </template>
                        </ul>
                        <div x-show="order.notes" class="text-xs text-gray-400 italic mb-2 line-clamp-2" x-text="'📝 ' + order.notes"></div>
                        <div class="flex gap-1.5 mt-2">
                            <button @click="progress(order.id)"
                                    class="flex-1 py-2 text-xs font-bold bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                                Marquer Servie →
                            </button>
                            <button @click="cancel(order.id)"
                                    class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Annuler">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function suiviBoard() {
    return {
        orders: { RECU: [], PREP: [], PRET: [] },
        totalOrders: 0,
        lastUpdate: '—',
        refreshing: false,

        init() {
            this.load();
            setInterval(() => this.load(), 20000);
        },

        async load() {
            this.refreshing = true;
            try {
                const res = await fetch('{{ route("admin.suivi.data", $tenant->slug) }}');
                const data = await res.json();
                if (data.success) {
                    this.orders = data.data;
                    this.totalOrders = data.total;
                    const now = new Date();
                    this.lastUpdate = now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                }
            } catch (e) {
                console.error('Erreur chargement:', e);
            } finally {
                this.refreshing = false;
            }
        },

        async progress(orderId) {
            try {
                const res = await fetch(`{{ url('/admin/' . $tenant->slug . '/suivi') }}/${orderId}/progress`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await res.json();
                if (data.success) await this.load();
            } catch (e) {
                alert('Erreur lors de la mise à jour.');
            }
        },

        async cancel(orderId) {
            if (!confirm('Êtes-vous sûr de vouloir annuler cette commande ?')) return;
            try {
                const res = await fetch(`{{ url('/admin/' . $tenant->slug . '/suivi') }}/${orderId}/cancel`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ reason: 'Annulée manuellement' })
                });
                const data = await res.json();
                if (data.success) await this.load();
            } catch (e) {
                alert('Erreur lors de l\'annulation.');
            }
        },

        formatPrice(n) {
            return new Intl.NumberFormat('fr-FR').format(Math.round(n));
        }
    };
}
</script>
@endpush
