@extends('layouts.admin')

@section('title', 'Gestion des Commandes')

@php
    $user = auth()->user();
    $showWaiterCalls = $user && ($user->hasRole(\App\Enums\UserRole::SERVEUR) || $user->hasRole(\App\Enums\UserRole::CAISSIER));
    $tenantId = $tenant->id ?? 0;
@endphp

@section('content')
<div x-data="ordersManager('{{ $tenantSlug }}', {{ $tenantId }}, {{ $showWaiterCalls ? 'true' : 'false' }})" x-init="init()" class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Commandes</h1>
            <p class="text-gray-600">
                <span x-show="filters.date === '{{ now()->format('Y-m-d') }}'">Commandes du jour</span>
                <span x-show="filters.date !== '{{ now()->format('Y-m-d') }}'" x-text="'Commandes du ' + new Date(filters.date).toLocaleDateString('fr-FR')"></span>
            </p>
        </div>

        <div class="flex gap-3 items-center">
            <!-- Auto-refresh indicator -->
            <div class="flex items-center gap-2 bg-gray-100 px-3 py-2 rounded-lg">
                <span class="relative flex h-3 w-3">
                    <span x-show="autoRefresh" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span :class="autoRefresh ? 'bg-green-500' : 'bg-gray-400'" class="relative inline-flex rounded-full h-3 w-3"></span>
                </span>
                <button @click="autoRefresh = !autoRefresh" class="text-sm font-medium text-gray-700">
                    Auto: <span x-text="autoRefresh ? 'ON' : 'OFF'"></span>
                </button>
            </div>

            <button @click="refresh()"
                    :disabled="loading"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-lg transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" :class="loading && 'animate-spin'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </button>

            <a href="{{ route('admin.exports.orders.excel', $tenantSlug) }}"
               class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export
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

    <!-- Waiter Calls Panel (SERVEUR & CAISSIER only) -->
    @if($showWaiterCalls)
    <div x-show="waiterCalls.length > 0"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform -translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         class="mb-6 bg-gradient-to-r from-orange-500 to-red-500 rounded-xl p-4 shadow-lg">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
                <div class="bg-white/20 rounded-lg p-2 animate-pulse">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <div class="text-white">
                    <h3 class="font-bold">Appels Clients</h3>
                    <p class="text-sm text-white/80" x-text="waiterCalls.filter(c => c.status === 'PENDING').length + ' en attente'"></p>
                </div>
            </div>
            <button @click="showWaiterCallsPanel = !showWaiterCallsPanel"
                    class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                <span x-text="showWaiterCallsPanel ? 'Masquer' : 'Voir tout'"></span>
            </button>
        </div>

        <!-- Liste des appels -->
        <div x-show="showWaiterCallsPanel" x-collapse class="space-y-2">
            <template x-for="call in waiterCalls" :key="call.id">
                <div class="bg-white rounded-lg p-4 flex items-center justify-between shadow-sm"
                     :class="{
                         'ring-2 ring-red-400 animate-pulse': call.call_type === 'URGENCE' && call.status === 'PENDING',
                         'opacity-60': call.status === 'RESOLVED'
                     }">
                    <div class="flex items-center gap-4">
                        <div class="text-3xl">
                            <span x-show="call.call_type === 'SERVICE'">🔔</span>
                            <span x-show="call.call_type === 'QUESTION'">❓</span>
                            <span x-show="call.call_type === 'URGENCE'">🚨</span>
                        </div>
                        <div>
                            <div class="font-bold text-gray-900" x-text="'Table ' + call.table_code"></div>
                            <div class="text-sm" :class="{
                                'text-blue-600': call.call_type === 'SERVICE',
                                'text-yellow-600': call.call_type === 'QUESTION',
                                'text-red-600 font-semibold': call.call_type === 'URGENCE'
                            }" x-text="call.call_type_label"></div>
                            <div class="text-xs text-gray-500" x-text="call.time_ago"></div>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <template x-if="call.status !== 'RESOLVED'">
                            <button @click="resolveCallDirectly(call.id)"
                                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                                OK
                            </button>
                        </template>
                        <template x-if="call.status === 'RESOLVED'">
                            <span class="text-green-600 text-sm font-medium flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
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
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <select x-model="filters.status" class="border-gray-300 rounded-lg text-sm">
                <option value="">Tous les statuts</option>
                <option value="RECU">Reçu</option>
                <option value="PREP">En préparation</option>
                <option value="PRET">Prêt</option>
                <option value="SERVI">Servi</option>
            </select>
            <select x-model="filters.paymentStatus" class="border-gray-300 rounded-lg text-sm">
                <option value="">Tous les paiements</option>
                <option value="PENDING">Non payé</option>
                <option value="PAID">Payé</option>
            </select>
            <select x-model="filters.table" class="border-gray-300 rounded-lg text-sm">
                <option value="">Toutes les tables</option>
                @foreach($tables as $table)
                    <option value="{{ $table->id }}">{{ $table->label }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <input type="date" x-model="filters.date" @change="refresh()" class="border-gray-300 rounded-lg text-sm flex-1">
                <button @click="filters.date = '{{ now()->format('Y-m-d') }}'; refresh()"
                        :class="filters.date === '{{ now()->format('Y-m-d') }}' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                        class="px-3 rounded-lg text-sm font-medium whitespace-nowrap">
                    Aujourd'hui
                </button>
            </div>
            <button @click="filters = {status:'', paymentStatus:'', table:'', date:'{{ now()->format('Y-m-d') }}'}; refresh()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm">
                Réinitialiser
            </button>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
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
                                    <!-- Voir -->
                                    <a :href="`/admin/{{ $tenantSlug }}/orders/${order.id}`"
                                       class="p-1.5 text-indigo-600 hover:bg-indigo-50 rounded" title="Voir">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <!-- Encaisser (si non payé) -->
                                    <template x-if="order.payment_status !== 'PAID' && order.status !== 'ANNULE'">
                                        <button @click="openPaymentModal(order)"
                                                class="p-1.5 text-green-600 hover:bg-green-50 rounded" title="Encaisser">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </button>
                                    </template>
                                    <!-- Avancer statut -->
                                    <template x-if="order.status !== 'SERVI' && order.status !== 'ANNULE'">
                                        <button @click="progressOrder(order.id)"
                                                class="p-1.5 text-blue-600 hover:bg-blue-50 rounded" title="Avancer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                            </svg>
                                        </button>
                                    </template>
                                    <!-- Annuler -->
                                    <template x-if="order.status !== 'ANNULE'">
                                        <button @click="cancelOrder(order.id)"
                                                class="p-1.5 text-red-600 hover:bg-red-50 rounded" title="Annuler">
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

<!-- Son de notification puissant avec Web Audio API -->
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

    // Joue une séquence d'alerte puissante (répétée plusieurs fois)
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

                // Oscillateur 1 - Ton principal aigu (La5 = 880Hz)
                const osc1 = ctx.createOscillator();
                osc1.type = 'square';
                osc1.frequency.setValueAtTime(880, startTime);

                // Oscillateur 2 - Harmonique supérieure (La6 = 1760Hz)
                const osc2 = ctx.createOscillator();
                osc2.type = 'sawtooth';
                osc2.frequency.setValueAtTime(1320, startTime);

                // Oscillateur 3 - Sous-ton pour plus de puissance
                const osc3 = ctx.createOscillator();
                osc3.type = 'triangle';
                osc3.frequency.setValueAtTime(440, startTime);

                // Gain principal - VOLUME ÉLEVÉ
                const gainNode = ctx.createGain();
                gainNode.gain.setValueAtTime(0, startTime);
                gainNode.gain.linearRampToValueAtTime(0.9, startTime + 0.01); // Attaque rapide
                gainNode.gain.setValueAtTime(0.9, startTime + 0.08);
                gainNode.gain.exponentialRampToValueAtTime(0.01, startTime + 0.15);

                // Connexions
                osc1.connect(gainNode);
                osc2.connect(gainNode);
                osc3.connect(gainNode);
                gainNode.connect(ctx.destination);

                // Démarrage et arrêt
                osc1.start(startTime);
                osc1.stop(startTime + 0.15);
                osc2.start(startTime);
                osc2.stop(startTime + 0.15);
                osc3.start(startTime);
                osc3.stop(startTime + 0.15);
            }

            // Répéter après une pause
            if (currentRepeat < repeatCount) {
                setTimeout(playSequence, 800);
            } else {
                setTimeout(() => { this.isPlaying = false; }, 600);
            }
        };

        playSequence();
    }
}

// Instance globale
window.notificationSound = new PowerfulNotificationSound();

// Activer le contexte audio au premier clic (requis par les navigateurs)
document.addEventListener('click', () => {
    if (window.notificationSound) {
        window.notificationSound.init();
    }
}, { once: true });
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
                        this.playNotificationSound();
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

        playNotificationSound() {
            try {
                if (window.notificationSound) {
                    window.notificationSound.play(3); // 3 répétitions pour bien alerter
                }
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
                    // Retirer l'appel de la liste immédiatement
                    this.waiterCalls = this.waiterCalls.filter(c => c.id !== callId);
                }
            } catch (e) {
                console.error('Erreur resolve:', e);
            }
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
