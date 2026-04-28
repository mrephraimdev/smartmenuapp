@extends('layouts.admin')

@section('title', 'Gestion des Commandes')

@php
    $user = auth()->user();
    $isAdmin = $user && ($user->hasRole(\App\Enums\UserRole::ADMIN) || $user->hasRole(\App\Enums\UserRole::SUPER_ADMIN));
    $notifTargets = $tenant->branding['notification_targets'] ?? ['SERVEUR', 'CAISSIER', 'ADMIN'];
    $showWaiterCalls = $user && (
        ($user->hasRole(\App\Enums\UserRole::SERVEUR)  && in_array('SERVEUR',  $notifTargets)) ||
        ($user->hasRole(\App\Enums\UserRole::CAISSIER) && in_array('CAISSIER', $notifTargets)) ||
        ($user->hasRole(\App\Enums\UserRole::ADMIN)    && in_array('ADMIN',    $notifTargets)) ||
        $user->hasRole(\App\Enums\UserRole::SUPER_ADMIN)
    );
    $tenantId = $tenant->id ?? 0;
@endphp

@section('content')
<div x-data="ordersManager('{{ $tenantSlug }}', {{ $tenantId }}, {{ $showWaiterCalls ? 'true' : 'false' }})" x-init="init()" class="container mx-auto">
    <!-- Header -->
    <div class="mb-5 flex flex-wrap gap-3 justify-between items-start">
        <div>
            <h1 class="text-xl sm:text-3xl font-bold text-gray-900">Commandes</h1>
            <p class="text-gray-500 text-sm mt-0.5">
                <span x-show="filters.date === '{{ now()->format('Y-m-d') }}'">Commandes du jour</span>
                <span x-show="filters.date !== '{{ now()->format('Y-m-d') }}'" x-text="'Commandes du ' + new Date(filters.date).toLocaleDateString('fr-FR')"></span>
            </p>
        </div>

        <div class="flex gap-2 items-center flex-shrink-0">
            <!-- Auto-refresh indicator -->
            <div class="flex items-center gap-1.5 bg-gray-100 px-2.5 py-1.5 rounded-lg">
                <span class="relative flex h-2.5 w-2.5">
                    <span x-show="autoRefresh" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span :class="autoRefresh ? 'bg-green-500' : 'bg-gray-400'" class="relative inline-flex rounded-full h-2.5 w-2.5"></span>
                </span>
                <button @click="autoRefresh = !autoRefresh" class="text-xs font-medium text-gray-700">
                    <span x-text="autoRefresh ? 'Auto ON' : 'Auto OFF'"></span>
                </button>
            </div>

            <button @click="refresh()"
                    :disabled="loading"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium p-2 rounded-lg transition-colors">
                <svg class="w-4 h-4" :class="loading && 'animate-spin'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </button>

            <a href="{{ route('admin.exports.orders.excel', $tenantSlug) }}"
               class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-3 rounded-lg transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="hidden sm:inline text-sm">Export</span>
            </a>
        </div>
    </div>

    <!-- New Order Notification -->
    <div x-show="newOrderAlert"
         x-transition
         class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg flex items-center justify-between">
        <div class="flex items-center gap-3">
            <svg class="w-6 h-6 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span class="font-medium">Nouvelle(s) commande(s) reçue(s)!</span>
        </div>
        <button @click="newOrderAlert = false" class="text-green-800 hover:text-green-900">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Waiter Calls Panel -->
    @if($showWaiterCalls)
    <div x-show="waiterCalls.length > 0"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="mb-6 bg-white border-2 rounded-xl shadow-sm overflow-hidden"
         :class="waiterCalls.some(c => c.call_type === 'URGENCE' && c.status === 'PENDING')
                    ? 'border-red-400'
                    : 'border-amber-300'">
        {{-- Header --}}
        <div class="px-4 py-3 flex items-center justify-between"
             :class="waiterCalls.some(c => c.call_type === 'URGENCE' && c.status === 'PENDING')
                        ? 'bg-red-50'
                        : 'bg-amber-50'">
            <div class="flex items-center gap-3">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
                          :class="waiterCalls.some(c => c.call_type === 'URGENCE' && c.status === 'PENDING') ? 'bg-red-400' : 'bg-amber-400'"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3"
                          :class="waiterCalls.some(c => c.call_type === 'URGENCE' && c.status === 'PENDING') ? 'bg-red-500' : 'bg-amber-500'"></span>
                </span>
                <span class="font-bold text-gray-900">
                    Appels clients
                </span>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold"
                      :class="waiterCalls.some(c => c.call_type === 'URGENCE' && c.status === 'PENDING')
                                 ? 'bg-red-500 text-white'
                                 : 'bg-amber-500 text-white'"
                      x-text="waiterCalls.filter(c => c.status === 'PENDING').length + ' en attente'">
                </span>
                <template x-if="waiterCalls.some(c => c.call_type === 'URGENCE' && c.status === 'PENDING')">
                    <span class="px-2 py-0.5 bg-red-600 text-white text-xs font-bold rounded-full animate-pulse">
                        🚨 URGENCE
                    </span>
                </template>
            </div>
            <div class="flex items-center gap-2">
                <button @click="showWaiterCallsPanel = !showWaiterCallsPanel"
                        class="px-3 py-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-lg text-xs font-semibold transition-colors">
                    <span x-text="showWaiterCallsPanel ? 'Réduire' : 'Afficher'"></span>
                </button>
                @if($isAdmin)
                <button @click="showNotifSettings = true"
                        class="p-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 rounded-lg transition-colors" title="Paramètres notifications">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </svg>
                </button>
                @endif
            </div>
        </div>

        {{-- Liste des appels --}}
        <div x-show="showWaiterCallsPanel" class="divide-y divide-gray-100">
            <template x-for="call in waiterCalls" :key="call.id">
                <div class="px-4 py-3 flex items-center justify-between"
                     :class="{
                         'bg-red-50': call.call_type === 'URGENCE' && call.status === 'PENDING',
                         'bg-white': call.call_type !== 'URGENCE',
                         'opacity-50': call.status === 'RESOLVED'
                     }">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 text-lg"
                             :class="{
                                 'bg-blue-100': call.call_type === 'SERVICE',
                                 'bg-yellow-100': call.call_type === 'QUESTION',
                                 'bg-red-100': call.call_type === 'URGENCE'
                             }">
                            <span x-show="call.call_type === 'SERVICE'">🔔</span>
                            <span x-show="call.call_type === 'QUESTION'">❓</span>
                            <span x-show="call.call_type === 'URGENCE'" class="animate-bounce">🚨</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-gray-900 text-sm" x-text="'Table ' + call.table_code"></span>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                                      :class="{
                                          'bg-blue-100 text-blue-700': call.call_type === 'SERVICE',
                                          'bg-yellow-100 text-yellow-700': call.call_type === 'QUESTION',
                                          'bg-red-100 text-red-700': call.call_type === 'URGENCE'
                                      }"
                                      x-text="call.call_type_label"></span>
                            </div>
                            <div class="text-xs text-gray-400 mt-0.5" x-text="call.time_ago"></div>
                        </div>
                    </div>
                    <template x-if="call.status !== 'RESOLVED'">
                        <button @click="resolveCallDirectly(call.id)"
                                class="px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold rounded-lg transition-colors">
                            ✓ Traité
                        </button>
                    </template>
                    <template x-if="call.status === 'RESOLVED'">
                        <span class="text-emerald-600 text-xs font-semibold">✓ Traité</span>
                    </template>
                </div>
            </template>
        </div>
    </div>
    @endif

    @if($isAdmin)
    {{-- Modal paramètres notifications (admin uniquement) --}}
    <div x-show="showNotifSettings"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
         @click.self="showNotifSettings = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm" @click.stop>
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-bold text-gray-900">Paramètres notifications</h3>
                <button @click="showNotifSettings = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-5 space-y-4">
                <p class="text-sm text-gray-500">Choisissez quels rôles reçoivent les notifications d'appels clients :</p>
                <div class="space-y-3">
                    @foreach(['SERVEUR' => 'Serveur', 'CAISSIER' => 'Caissier', 'ADMIN' => 'Administrateur'] as $role => $label)
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors">
                        <input type="checkbox"
                               value="{{ $role }}"
                               x-model="notifTargets"
                               class="w-4 h-4 text-amber-500 rounded border-gray-300 focus:ring-amber-400">
                        <span class="text-sm font-medium text-gray-800">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
                <button @click="saveNotifSettings()"
                        :disabled="savingNotif"
                        class="w-full py-2.5 bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white font-bold rounded-xl transition-colors text-sm">
                    <span x-text="savingNotif ? 'Enregistrement...' : 'Enregistrer'"></span>
                </button>
                <p x-show="notifSaved" class="text-center text-sm text-emerald-600 font-semibold">✓ Paramètres sauvegardés</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 mb-5">
        <div class="bg-blue-50 rounded-lg p-4 border-l-4 border-blue-500">
            <p class="text-blue-700 text-xs font-medium mb-1">Total</p>
            <p class="text-2xl font-bold text-blue-900" x-text="stats.total"></p>
        </div>
        <div class="bg-yellow-50 rounded-lg p-4 border-l-4 border-yellow-500">
            <p class="text-yellow-700 text-xs font-medium mb-1">En cours</p>
            <p class="text-2xl font-bold text-yellow-900" x-text="stats.pending"></p>
        </div>
        <div class="bg-green-50 rounded-lg p-4 border-l-4 border-green-500">
            <p class="text-green-700 text-xs font-medium mb-1">Complétées</p>
            <p class="text-2xl font-bold text-green-900" x-text="stats.completed"></p>
        </div>
        <div class="bg-red-50 rounded-lg p-4 border-l-4 border-red-500">
            <p class="text-red-700 text-xs font-medium mb-1">Impayées</p>
            <p class="text-2xl font-bold text-red-900" x-text="stats.unpaid"></p>
        </div>
        <div class="bg-purple-50 rounded-lg p-4 border-l-4 border-purple-500">
            <p class="text-purple-700 text-xs font-medium mb-1">Revenus</p>
            <p class="text-xl font-bold text-purple-900" x-text="formatCurrency(stats.revenue)"></p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-3 sm:p-4 mb-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            <select x-model="filters.status" class="border border-gray-300 rounded-lg text-sm px-2 py-2 bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">Tous les statuts</option>
                <option value="RECU">Reçu</option>
                <option value="PREP">En préparation</option>
                <option value="PRET">Prêt</option>
                <option value="SERVI">Servi</option>
            </select>
            <select x-model="filters.paymentStatus" class="border border-gray-300 rounded-lg text-sm px-2 py-2 bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">Tous les paiements</option>
                <option value="PENDING">Non payé</option>
                <option value="PAID">Payé</option>
            </select>
            <select x-model="filters.table" class="border border-gray-300 rounded-lg text-sm px-2 py-2 bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">Toutes les tables</option>
                @foreach($tables as $table)
                    <option value="{{ $table->id }}">{{ $table->label }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <input type="date" x-model="filters.date" @change="refresh()" class="border border-gray-300 rounded-lg text-sm px-2 py-2 bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400 flex-1">
                <button @click="filters.date = '{{ now()->format('Y-m-d') }}'; refresh()"
                        :class="filters.date === '{{ now()->format('Y-m-d') }}' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                        class="px-3 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition-colors">
                    Aujourd'hui
                </button>
            </div>
            <button @click="filters = {status:'', paymentStatus:'', table:'', date:'{{ now()->format('Y-m-d') }}'}; refresh()"
                    class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold transition-colors">
                Réinitialiser
            </button>
        </div>
    </div>

    <!-- Orders List -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">

        {{-- ── Vue CARTES (mobile uniquement) ───────────────────── --}}
        <div class="sm:hidden divide-y divide-gray-100">
            <template x-for="order in filteredOrders" :key="'m'+order.id">
                <div class="p-4" :class="order.isNew && 'bg-green-50'">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <p class="font-bold text-gray-900 text-sm" x-text="order.order_number || '#' + order.id"></p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                <span x-text="order.table?.label || 'Comptoir'"></span>
                                <span class="mx-1">·</span>
                                <span x-text="(order.items?.length || 0) + ' art.'"></span>
                                <span class="mx-1">·</span>
                                <span x-text="formatTime(order.created_at)"></span>
                            </p>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full flex-shrink-0"
                              :class="getStatusClass(order.status)"
                              x-text="getStatusLabel(order.status)"></span>
                    </div>
                    <div class="flex items-center justify-between mt-2">
                        <div class="flex items-center gap-2">
                            <span class="text-base font-bold text-gray-900" x-text="formatCurrency(order.total)"></span>
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full"
                                  :class="getPaymentStatusClass(order.payment_status)"
                                  x-text="getPaymentStatusLabel(order.payment_status)"></span>
                        </div>
                        <div class="flex gap-1.5">
                            <a :href="`/admin/{{ $tenantSlug }}/orders/${order.id}`"
                               class="p-2 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded-lg" title="Voir">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <template x-if="order.payment_status !== 'PAID' && order.status !== 'ANNULE'">
                                <button @click="openPaymentModal(order)"
                                        class="p-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 rounded-lg" title="Encaisser">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </button>
                            </template>
                            <template x-if="order.status !== 'SERVI' && order.status !== 'ANNULE'">
                                <button @click="progressOrder(order.id)"
                                        class="p-2 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg" title="Avancer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    </svg>
                                </button>
                            </template>
                            <template x-if="order.status !== 'ANNULE'">
                                <button @click="cancelOrder(order.id)"
                                        class="p-2 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg" title="Annuler">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
            <div x-show="filteredOrders.length === 0" class="py-10 text-center text-gray-400 text-sm">
                Aucune commande
            </div>
        </div>

        {{-- ── Vue TABLEAU (sm et plus) ─────────────────────────── --}}
        <div class="hidden sm:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Commande</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Table</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paiement</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="order in filteredOrders" :key="order.id">
                        <tr class="hover:bg-gray-50" :class="order.isNew && 'bg-green-50 animate-pulse'">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900" x-text="order.order_number || '#' + order.id"></div>
                                <div class="text-xs text-gray-500" x-text="order.items?.length + ' articles'"></div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm text-gray-900" x-text="order.table?.label || 'N/A'"></div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900" x-text="formatCurrency(order.total)"></div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                      :class="getStatusClass(order.status)"
                                      x-text="getStatusLabel(order.status)">
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                      :class="getPaymentStatusClass(order.payment_status)"
                                      x-text="getPaymentStatusLabel(order.payment_status)">
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm text-gray-900" x-text="formatTime(order.created_at)"></div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                                <div class="flex gap-1">
                                    <a :href="`/admin/{{ $tenantSlug }}/orders/${order.id}`"
                                       class="p-1.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded-lg" title="Voir">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <template x-if="order.payment_status !== 'PAID' && order.status !== 'ANNULE'">
                                        <button @click="openPaymentModal(order)"
                                                class="p-1.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 rounded-lg" title="Encaisser">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </button>
                                    </template>
                                    <template x-if="order.status !== 'SERVI' && order.status !== 'ANNULE'">
                                        <button @click="progressOrder(order.id)"
                                                class="p-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg" title="Avancer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                            </svg>
                                        </button>
                                    </template>
                                    <template x-if="order.status !== 'ANNULE'">
                                        <button @click="cancelOrder(order.id)"
                                                class="p-1.5 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg" title="Annuler">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filteredOrders.length === 0">
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <p>Aucune commande</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payment Modal -->
    <div x-show="paymentModal.show"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
         @click.self="paymentModal.show = false">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md" @click.stop>
            <!-- Header -->
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 text-white p-4 rounded-t-xl">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-bold">Encaissement</h3>
                    <button @click="paymentModal.show = false" class="text-white/80 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mt-2">
                    <span class="text-white/80 text-sm">Commande</span>
                    <span class="font-bold" x-text="paymentModal.order?.order_number || '#' + paymentModal.order?.id"></span>
                    <span class="text-white/80 text-sm ml-2">-</span>
                    <span class="text-white/80 text-sm" x-text="paymentModal.order?.table?.label"></span>
                </div>
            </div>

            <!-- Body -->
            <div class="p-4 space-y-4">
                <!-- Montant à payer -->
                <div class="bg-gray-50 rounded-lg p-4 text-center">
                    <p class="text-sm text-gray-500 mb-1">Montant à payer</p>
                    <p class="text-3xl font-bold text-gray-900" x-text="formatCurrency(paymentModal.remaining)"></p>
                </div>

                <!-- Méthode de paiement -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Méthode de paiement</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button @click="paymentModal.method = 'CASH'"
                                :class="paymentModal.method === 'CASH' ? 'ring-2 ring-green-500 bg-green-50' : 'bg-gray-100'"
                                class="p-3 rounded-lg text-center hover:bg-gray-200 transition">
                            <svg class="w-6 h-6 mx-auto mb-1 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span class="text-xs font-medium">Espèces</span>
                        </button>
                        <button @click="paymentModal.method = 'CARD'"
                                :class="paymentModal.method === 'CARD' ? 'ring-2 ring-blue-500 bg-blue-50' : 'bg-gray-100'"
                                class="p-3 rounded-lg text-center hover:bg-gray-200 transition">
                            <svg class="w-6 h-6 mx-auto mb-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            <span class="text-xs font-medium">Carte</span>
                        </button>
                        <button @click="paymentModal.method = 'ORANGE_MONEY'"
                                :class="paymentModal.method === 'ORANGE_MONEY' ? 'ring-2 ring-orange-500 bg-orange-50' : 'bg-gray-100'"
                                class="p-3 rounded-lg text-center hover:bg-gray-200 transition">
                            <svg class="w-6 h-6 mx-auto mb-1 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span class="text-xs font-medium">Orange</span>
                        </button>
                        <button @click="paymentModal.method = 'MTN_MOMO'"
                                :class="paymentModal.method === 'MTN_MOMO' ? 'ring-2 ring-yellow-500 bg-yellow-50' : 'bg-gray-100'"
                                class="p-3 rounded-lg text-center hover:bg-gray-200 transition">
                            <svg class="w-6 h-6 mx-auto mb-1 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span class="text-xs font-medium">MTN</span>
                        </button>
                        <button @click="paymentModal.method = 'MOOV_MONEY'"
                                :class="paymentModal.method === 'MOOV_MONEY' ? 'ring-2 ring-blue-500 bg-blue-50' : 'bg-gray-100'"
                                class="p-3 rounded-lg text-center hover:bg-gray-200 transition">
                            <svg class="w-6 h-6 mx-auto mb-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span class="text-xs font-medium">Moov</span>
                        </button>
                        <button @click="paymentModal.method = 'WAVE'"
                                :class="paymentModal.method === 'WAVE' ? 'ring-2 ring-cyan-500 bg-cyan-50' : 'bg-gray-100'"
                                class="p-3 rounded-lg text-center hover:bg-gray-200 transition">
                            <svg class="w-6 h-6 mx-auto mb-1 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span class="text-xs font-medium">Wave</span>
                        </button>
                    </div>
                </div>

                <!-- Montant reçu (pour cash) -->
                <div x-show="paymentModal.method === 'CASH'">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Montant reçu</label>
                    <input type="number"
                           x-model.number="paymentModal.amountReceived"
                           @input="calculateChange()"
                           class="w-full border-gray-300 rounded-lg text-lg font-bold text-center"
                           :min="paymentModal.remaining"
                           placeholder="0">
                    <!-- Raccourcis montants -->
                    <div class="flex gap-2 mt-2">
                        <button @click="paymentModal.amountReceived = paymentModal.remaining; calculateChange()"
                                class="flex-1 py-1 text-sm bg-gray-100 rounded hover:bg-gray-200">
                            Exact
                        </button>
                        <button @click="paymentModal.amountReceived = Math.ceil(paymentModal.remaining / 1000) * 1000; calculateChange()"
                                class="flex-1 py-1 text-sm bg-gray-100 rounded hover:bg-gray-200"
                                x-text="formatCurrency(Math.ceil(paymentModal.remaining / 1000) * 1000)">
                        </button>
                        <button @click="paymentModal.amountReceived = Math.ceil(paymentModal.remaining / 5000) * 5000; calculateChange()"
                                class="flex-1 py-1 text-sm bg-gray-100 rounded hover:bg-gray-200"
                                x-text="formatCurrency(Math.ceil(paymentModal.remaining / 5000) * 5000)">
                        </button>
                    </div>
                </div>

                <!-- Rendu monnaie -->
                <div x-show="paymentModal.method === 'CASH' && paymentModal.change > 0"
                     class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-center">
                    <p class="text-sm text-yellow-700 mb-1">Rendu monnaie</p>
                    <p class="text-2xl font-bold text-yellow-800" x-text="formatCurrency(paymentModal.change)"></p>
                </div>

                <!-- Référence transaction (mobile money) -->
                <div x-show="['ORANGE_MONEY', 'MTN_MOMO', 'MOOV_MONEY', 'WAVE'].includes(paymentModal.method)">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Référence transaction (optionnel)</label>
                    <input type="text"
                           x-model="paymentModal.transactionId"
                           class="w-full border-gray-300 rounded-lg"
                           placeholder="Ex: TXN123456789">
                </div>
            </div>

            <!-- Footer -->
            <div class="p-4 bg-gray-50 rounded-b-xl flex gap-3">
                <button @click="paymentModal.show = false"
                        class="flex-1 py-3 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300">
                    Annuler
                </button>
                <button @click="processPayment()"
                        :disabled="paymentModal.processing || (paymentModal.method === 'CASH' && paymentModal.amountReceived < paymentModal.remaining)"
                        class="flex-1 py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <svg x-show="paymentModal.processing" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="paymentModal.processing ? 'Traitement...' : 'Valider le paiement'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div x-show="receiptModal.show"
         x-transition
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
         @click.self="receiptModal.show = false">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm text-center p-6">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Paiement enregistré!</h3>
            <p class="text-gray-500 mb-4" x-text="'Montant: ' + formatCurrency(receiptModal.amount)"></p>
            <p x-show="receiptModal.change > 0" class="text-yellow-600 font-medium mb-4" x-text="'Rendu: ' + formatCurrency(receiptModal.change)"></p>
            <div class="flex gap-3">
                <button @click="receiptModal.show = false" class="flex-1 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    Fermer
                </button>
                <button @click="printReceipt()" class="flex-1 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 flex items-center justify-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Imprimer
                </button>
            </div>
        </div>
    </div>

    <!-- Hidden iframe for printing -->
    <iframe id="printFrame" style="display:none;"></iframe>
</div>

<!-- Alarme sonore + notifications push pour la page Commandes -->
<script>
// Réutilise l'instance globale _orderAlarm créée dans le layout si disponible,
// sinon en crée une locale (cas où la page est ouverte sans le layout).
if (!window._orderAlarm) {
    class OrderAlarm {
        constructor() { this.ctx = null; this.comp = null; this.playing = false; }
        _init() {
            if (!this.ctx) {
                this.ctx  = new (window.AudioContext || window.webkitAudioContext)();
                this.comp = this.ctx.createDynamicsCompressor();
                this.comp.threshold.value = -6; this.comp.knee.value = 0;
                this.comp.ratio.value = 20; this.comp.attack.value = 0.001;
                this.comp.release.value = 0.05;
                this.comp.connect(this.ctx.destination);
            }
            if (this.ctx.state === 'suspended') this.ctx.resume();
        }
        _tone(freq, start, dur) {
            const osc = this.ctx.createOscillator(), gain = this.ctx.createGain();
            osc.type = 'square'; osc.frequency.value = freq;
            gain.gain.setValueAtTime(1.0, start);
            gain.gain.exponentialRampToValueAtTime(0.001, start + dur);
            osc.connect(gain); gain.connect(this.comp);
            osc.start(start); osc.stop(start + dur);
        }
        play(rings = 5) {
            this._init(); if (this.playing) return; this.playing = true;
            const now = this.ctx.currentTime;
            for (let r = 0; r < rings; r++) {
                const b = now + r * 0.75;
                this._tone(1400, b+0.00, 0.10); this._tone(700, b+0.15, 0.10);
                this._tone(1400, b+0.30, 0.10); this._tone(700, b+0.45, 0.10);
            }
            setTimeout(() => { this.playing = false; }, rings * 750 + 300);
        }
    }
    window._orderAlarm = new OrderAlarm();
    document.addEventListener('click', () => window._orderAlarm._init(), { once: true });
}

function _showOrderPushNotif(count) {
    if (!('Notification' in window) || Notification.permission !== 'granted') return;
    const n = new Notification('🛎️ Nouvelle commande !', {
        body: `${count} commande(s) en attente`,
        icon: '/favicon.ico', tag: 'new-order', requireInteraction: true,
    });
    n.onclick = () => { window.focus(); n.close(); };
}
</script>

<script>
function ordersManager(tenantSlug, tenantId, showWaiterCalls) {
    return {
        tenantSlug: tenantSlug,
        tenantId: tenantId,
        showWaiterCallsEnabled: showWaiterCalls,
        orders: [],
        loading: false,
        autoRefresh: true,
        interval: null,
        newOrderAlert: false,
        filters: {
            status: '',
            paymentStatus: '',
            table: '',
            date: '{{ $filterDate }}'
        },
        stats: {
            total: {{ $statistics['total'] }},
            pending: {{ $statistics['pending'] }},
            completed: {{ $statistics['completed'] }},
            revenue: {{ $statistics['revenue'] }},
            unpaid: 0
        },
        paymentModal: {
            show: false,
            order: null,
            remaining: 0,
            method: 'CASH',
            amountReceived: 0,
            change: 0,
            transactionId: '',
            processing: false
        },
        receiptModal: {
            show: false,
            amount: 0,
            change: 0,
            url: ''
        },

        // Waiter Calls
        waiterCalls: [],
        showWaiterCallsPanel: true,
        knownCallIds: new Set(),
        showNotifSettings: false,
        notifTargets: @json($notifTargets),
        savingNotif: false,
        notifSaved: false,

        init() {
            this.orders = @json($orders->items());
            this.updateStats();
            this.startAutoRefresh();
            if (this.showWaiterCallsEnabled) {
                this.refreshWaiterCalls();
            }
        },

        async refresh() {
            if (this.loading) return;
            this.loading = true;

            try {
                const params = new URLSearchParams();
                if (this.filters.status) params.append('status', this.filters.status);
                if (this.filters.table) params.append('table', this.filters.table);
                // Toujours envoyer la date (par défaut aujourd'hui)
                params.append('date', this.filters.date || '{{ now()->format('Y-m-d') }}');

                const res = await fetch(`/admin/${this.tenantSlug}/orders?${params.toString()}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'include'
                });

                if (res.ok) {
                    const data = await res.json();
                    const newOrders = data.filter(order => !this.orders.some(o => o.id === order.id));

                    if (newOrders.length > 0) {
                        newOrders.forEach(order => order.isNew = true);
                        this.playNotificationSound(newOrders.length);
                        this.newOrderAlert = true;
                        setTimeout(() => this.orders.forEach(order => order.isNew = false), 5000);
                    }

                    this.orders = data;
                    this.updateStats();
                }
            } catch (e) {
                console.error('Erreur refresh:', e);
            }
            this.loading = false;
        },

        startAutoRefresh() {
            this.interval = setInterval(() => {
                if (this.autoRefresh) {
                    this.refresh();
                    if (this.showWaiterCallsEnabled) {
                        this.refreshWaiterCalls();
                    }
                }
            }, 5000);
        },

        playNotificationSound(newCount) {
            try {
                window._orderAlarm?.play(5);
                _showOrderPushNotif(newCount || this.stats.pending);
            } catch (e) {
                console.error('Erreur son notification:', e);
            }
        },

        updateStats() {
            this.stats.total = this.orders.length;
            this.stats.pending = this.orders.filter(o => ['RECU', 'PREP', 'PRET'].includes(o.status)).length;
            this.stats.completed = this.orders.filter(o => o.status === 'SERVI').length;
            this.stats.unpaid = this.orders.filter(o => o.payment_status !== 'PAID' && o.status !== 'ANNULE').length;
            this.stats.revenue = this.orders.filter(o => o.payment_status === 'PAID').reduce((sum, o) => sum + (parseFloat(o.total) || 0), 0);
        },

        get filteredOrders() {
            let result = this.orders;
            // Filtrage local pour status, paymentStatus et table (la date est gérée par l'API)
            if (this.filters.status) result = result.filter(o => o.status === this.filters.status);
            if (this.filters.paymentStatus) result = result.filter(o => o.payment_status === this.filters.paymentStatus);
            if (this.filters.table) result = result.filter(o => o.table_id == this.filters.table);
            return result;
        },

        openPaymentModal(order) {
            this.paymentModal.order = order;
            this.paymentModal.remaining = parseFloat(order.total) - parseFloat(order.paid_amount || 0);
            this.paymentModal.method = 'CASH';
            this.paymentModal.amountReceived = this.paymentModal.remaining;
            this.paymentModal.change = 0;
            this.paymentModal.transactionId = '';
            this.paymentModal.processing = false;
            this.paymentModal.show = true;
        },

        calculateChange() {
            this.paymentModal.change = Math.max(0, this.paymentModal.amountReceived - this.paymentModal.remaining);
        },

        async processPayment() {
            if (this.paymentModal.processing) return;
            this.paymentModal.processing = true;

            try {
                const res = await fetch(`/admin/${this.tenantSlug}/payments/order/${this.paymentModal.order.id}/pay`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                        method: this.paymentModal.method,
                        amount_received: this.paymentModal.amountReceived,
                        transaction_id: this.paymentModal.transactionId || null
                    })
                });

                const data = await res.json();

                if (data.success) {
                    // Update local order
                    const order = this.orders.find(o => o.id === this.paymentModal.order.id);
                    if (order) {
                        order.payment_status = data.order.payment_status;
                        order.paid_amount = data.order.paid_amount;
                    }
                    this.updateStats();
                    this.paymentModal.show = false;

                    // Show receipt modal
                    this.receiptModal.amount = this.paymentModal.remaining;
                    this.receiptModal.change = data.change || 0;
                    this.receiptModal.url = data.receipt_url;
                    this.receiptModal.show = true;
                } else {
                    alert('Erreur: ' + (data.error || 'Erreur inconnue'));
                }
            } catch (e) {
                console.error('Erreur:', e);
                alert('Erreur lors du paiement');
            }
            this.paymentModal.processing = false;
        },

        async progressOrder(orderId) {
            if (!confirm('Avancer cette commande?')) return;
            try {
                const res = await fetch(`/admin/${this.tenantSlug}/orders/${orderId}/progress`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    credentials: 'include'
                });
                const data = await res.json();
                if (data.success) {
                    const order = this.orders.find(o => o.id === orderId);
                    if (order) {
                        const flow = ['RECU', 'PREP', 'PRET', 'SERVI'];
                        const idx = flow.indexOf(order.status);
                        if (idx < flow.length - 1) order.status = flow[idx + 1];
                    }
                    this.updateStats();
                }
            } catch (e) { console.error(e); }
        },

        async cancelOrder(orderId) {
            if (!confirm('Annuler cette commande?')) return;
            try {
                const res = await fetch(`/admin/${this.tenantSlug}/orders/${orderId}/cancel`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    credentials: 'include'
                });
                const data = await res.json();
                if (data.success) {
                    const order = this.orders.find(o => o.id === orderId);
                    if (order) order.status = 'ANNULE';
                    this.updateStats();
                }
            } catch (e) { console.error(e); }
        },

        // Waiter Calls Methods
        async refreshWaiterCalls() {
            if (!this.tenantId || !this.showWaiterCallsEnabled) return;

            try {
                const res = await fetch(`/api/waiter-calls?tenant_id=${this.tenantId}`, {
                    credentials: 'include',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (res.ok) {
                    const data = await res.json();
                    if (data.success) {
                        // Filtrer pour n'afficher que les appels non résolus
                        const activeCalls = data.calls.filter(call => call.status !== 'RESOLVED');

                        // Détecter les nouveaux appels
                        const newPendingCalls = activeCalls.filter(call =>
                            call.status === 'PENDING' && !this.knownCallIds.has(call.id)
                        );

                        // Jouer le son si nouveaux appels (et ce n'est pas le premier chargement)
                        if (newPendingCalls.length > 0 && this.knownCallIds.size > 0) {
                            this.playWaiterCallSound(newPendingCalls.some(c => c.is_urgent));
                        }

                        // Mettre à jour les IDs connus
                        data.calls.forEach(call => this.knownCallIds.add(call.id));

                        this.waiterCalls = activeCalls;
                    }
                }
            } catch (e) {
                console.error('Erreur waiter calls:', e);
            }
        },

        playWaiterCallSound(isUrgent = false) {
            try {
                if (window.notificationSound) {
                    window.notificationSound.play(isUrgent ? 5 : 2);
                }
            } catch (e) {
                console.error('Erreur son appel:', e);
            }
        },

        async resolveCallDirectly(callId) {
            try {
                const res = await fetch(`/api/waiter-calls/${callId}/resolve`, {
                    method: 'PATCH',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (res.ok) {
                    this.waiterCalls = this.waiterCalls.filter(c => c.id !== callId);
                }
            } catch (e) {
                console.error('Erreur resolve:', e);
            }
        },

        async saveNotifSettings() {
            this.savingNotif = true;
            this.notifSaved = false;
            try {
                const res = await fetch(`/admin/${this.tenantSlug}/settings/notifications`, {
                    method: 'PATCH',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ targets: this.notifTargets }),
                });
                if (res.ok) {
                    this.notifSaved = true;
                    setTimeout(() => { this.notifSaved = false; this.showNotifSettings = false; }, 1500);
                }
            } catch (e) {
                console.error('Erreur save notif settings:', e);
            }
            this.savingNotif = false;
        },

        printReceipt() {
            if (!this.receiptModal.url) return;
            this.receiptModal.show = false;
            window.open(this.receiptModal.url, 'receipt', 'width=350,height=600,scrollbars=yes');
        },

        // Helpers
        formatCurrency(amount) {
            return new Intl.NumberFormat('fr-FR').format(amount || 0) + ' F';
        },
        formatTime(dateStr) {
            if (!dateStr) return '';
            return new Date(dateStr).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        },
        getStatusClass(status) {
            return { 'RECU': 'bg-blue-100 text-blue-800', 'PREP': 'bg-yellow-100 text-yellow-800', 'PRET': 'bg-green-100 text-green-800', 'SERVI': 'bg-gray-100 text-gray-800', 'ANNULE': 'bg-red-100 text-red-800' }[status] || 'bg-gray-100';
        },
        getStatusLabel(status) {
            return { 'RECU': 'Reçu', 'PREP': 'Préparation', 'PRET': 'Prêt', 'SERVI': 'Servi', 'ANNULE': 'Annulé' }[status] || status;
        },
        getPaymentStatusClass(status) {
            return { 'PENDING': 'bg-red-100 text-red-800', 'PAID': 'bg-green-100 text-green-800', 'PARTIAL': 'bg-orange-100 text-orange-800' }[status] || 'bg-gray-100';
        },
        getPaymentStatusLabel(status) {
            return { 'PENDING': 'Non payé', 'PAID': 'Payé', 'PARTIAL': 'Partiel' }[status] || 'Non payé';
        }
    };
}
</script>
@endsection
