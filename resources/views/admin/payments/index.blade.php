@extends('layouts.admin')

@section('title', 'Paiements')
@section('page-title', 'Paiements & Encaissements')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard', $tenantSlug) }}" class="hover:text-amber-600">Dashboard</a>
    <span class="mx-2">/</span>
    <span>Paiements</span>
@endsection

@section('content')
<div
    x-data="paymentsManager()"
    x-init="init()"
    class="space-y-6"
>

{{-- ═══════════════════════════════════════════════════════
     STATS DU JOUR
═══════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-4">
    <div class="bg-green-50 rounded-xl p-4 border-l-4 border-green-500">
        <p class="text-green-700 text-xs font-semibold uppercase tracking-wide mb-1">Encaissé (Jour)</p>
        <p class="text-2xl font-extrabold text-green-900">{{ number_format($stats['total'], 0, ',', ' ') }} F</p>
    </div>
    <div class="bg-blue-50 rounded-xl p-4 border-l-4 border-blue-500">
        <p class="text-blue-700 text-xs font-semibold uppercase tracking-wide mb-1">Transactions</p>
        <p class="text-2xl font-extrabold text-blue-900">{{ $stats['count'] }}</p>
    </div>
    <div class="bg-emerald-50 rounded-xl p-4 border-l-4 border-emerald-500">
        <p class="text-emerald-700 text-xs font-semibold uppercase tracking-wide mb-1">Espèces</p>
        <p class="text-2xl font-extrabold text-emerald-900">{{ number_format($stats['cash'], 0, ',', ' ') }} F</p>
    </div>
    <div class="bg-indigo-50 rounded-xl p-4 border-l-4 border-indigo-500">
        <p class="text-indigo-700 text-xs font-semibold uppercase tracking-wide mb-1">Carte</p>
        <p class="text-2xl font-extrabold text-indigo-900">{{ number_format($stats['card'], 0, ',', ' ') }} F</p>
    </div>
    <div class="bg-orange-50 rounded-xl p-4 border-l-4 border-orange-500">
        <p class="text-orange-700 text-xs font-semibold uppercase tracking-wide mb-1">Mobile Money</p>
        <p class="text-2xl font-extrabold text-orange-900">{{ number_format($stats['mobile'], 0, ',', ' ') }} F</p>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     COMMANDES IMPAYÉES — ENCAISSEMENT RAPIDE
═══════════════════════════════════════════════════════ --}}
@if($unpaidOrders->count() > 0)
<div class="bg-white rounded-2xl shadow-sm border border-red-100 overflow-hidden">
    <div class="px-5 py-3 bg-red-50 border-b border-red-100 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/>
                </svg>
            </div>
            <div>
                <p class="font-bold text-red-800">{{ $unpaidOrders->count() }} commande(s) à encaisser</p>
                <p class="text-sm text-red-600">Total impayé : <strong>{{ number_format($totalUnpaid, 0, ',', ' ') }} FCFA</strong></p>
            </div>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Commande</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Table</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Articles</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Reste à payer</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Statut</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @foreach($unpaidOrders as $unpaidOrder)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap">
                        <p class="text-sm font-bold text-gray-900">{{ $unpaidOrder->order_number }}</p>
                        <p class="text-xs text-gray-400">{{ $unpaidOrder->created_at->format('H:i') }}</p>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                        {{ $unpaidOrder->table->label ?? 'Comptoir' }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        {{ $unpaidOrder->items->sum('quantity') }} article(s)
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-semibold text-gray-900">
                        {{ number_format($unpaidOrder->total, 0, ',', ' ') }} F
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-bold text-red-600">
                        {{ number_format($unpaidOrder->getRemainingAmount(), 0, ',', ' ') }} F
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        @if($unpaidOrder->payment_status === 'PARTIAL')
                            <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-yellow-100 text-yellow-800">Partiel</span>
                        @else
                            <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-red-100 text-red-800">Impayé</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <button
                            @click="openPayModal({{ $unpaidOrder->id }}, '{{ $unpaidOrder->order_number }}', {{ $unpaidOrder->getRemainingAmount() }})"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
                            </svg>
                            Encaisser
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════
     FILTRES
═══════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[150px]">
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Mode</label>
            <select name="method" class="w-full border border-gray-200 rounded-xl text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400">
                <option value="">Tous les modes</option>
                @foreach($paymentMethods as $method)
                    <option value="{{ $method->value }}" {{ request('method') == $method->value ? 'selected' : '' }}>
                        {{ $method->label() }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[140px]">
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Statut</label>
            <select name="status" class="w-full border border-gray-200 rounded-xl text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400">
                <option value="">Tous</option>
                <option value="SUCCESS" {{ request('status') == 'SUCCESS' ? 'selected' : '' }}>Succès</option>
                <option value="REFUNDED" {{ request('status') == 'REFUNDED' ? 'selected' : '' }}>Remboursé</option>
            </select>
        </div>
        <div class="flex-1 min-w-[140px]">
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Date</label>
            <input type="date" name="date" value="{{ request('date') }}"
                   class="w-full border border-gray-200 rounded-xl text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400">
        </div>
        <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-sm font-semibold transition-colors">
            Filtrer
        </button>
        <a href="{{ route('admin.payments.index', $tenantSlug) }}"
           class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-semibold transition-colors">
            Réinitialiser
        </a>
    </form>
</div>

{{-- ═══════════════════════════════════════════════════════
     HISTORIQUE DES PAIEMENTS
═══════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-3">
        <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
        </svg>
        <h2 class="text-sm font-bold text-gray-900">Historique des paiements</h2>
        <span class="ml-auto text-xs text-gray-400">{{ $payments->total() }} enregistrement(s)</span>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Transaction</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Commande</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Table</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Montant</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Mode</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Statut</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Caissier</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date & Heure</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($payments as $payment)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 whitespace-nowrap">
                        <span class="text-xs font-mono text-gray-500">{{ $payment->transaction_id }}</span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <a href="{{ route('admin.orders.show', [$tenantSlug, $payment->order_id]) }}"
                           class="text-sm font-bold text-indigo-600 hover:text-indigo-800">
                            {{ $payment->order->order_number ?? '#'.$payment->order_id }}
                        </a>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                        {{ $payment->order->table->label ?? 'Comptoir' }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-right">
                        <p class="text-sm font-bold text-gray-900">{{ number_format($payment->amount, 0, ',', ' ') }} F</p>
                        @if($payment->change_given > 0)
                            <p class="text-xs text-amber-600">Rendu : {{ number_format($payment->change_given, 0, ',', ' ') }} F</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <span class="px-2 py-0.5 text-xs font-bold rounded-full {{ $payment->method_color ?? 'bg-gray-100 text-gray-700' }}">
                            {{ $payment->method_label ?? $payment->method }}
                        </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        @if($payment->status === 'SUCCESS')
                            <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-green-100 text-green-800">Succès</span>
                        @elseif($payment->status === 'REFUNDED')
                            <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-orange-100 text-orange-800">Remboursé</span>
                        @else
                            <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-gray-100 text-gray-700">{{ $payment->status }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                        {{ $payment->processedBy->name ?? 'Système' }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <p class="text-sm text-gray-900">{{ $payment->created_at->format('d/m/Y') }}</p>
                        <p class="text-xs text-gray-400">{{ $payment->created_at->format('H:i:s') }}</p>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <a href="{{ route('admin.payments.receipt', [$tenantSlug, $payment->id]) }}"
                           target="_blank"
                           title="Réimprimer le ticket"
                           class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/>
                            </svg>
                            Réimprimer
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-16 text-center">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
                        </svg>
                        <p class="text-gray-400 font-medium">Aucun paiement enregistré</p>
                        <p class="text-gray-300 text-sm mt-1">Les paiements apparaîtront ici une fois encaissés.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())
    <div class="px-5 py-3 bg-gray-50 border-t border-gray-100">
        {{ $payments->links() }}
    </div>
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════
     MODAL ENCAISSEMENT
═══════════════════════════════════════════════════════ --}}
<template x-teleport="body">
    <div
        x-show="showModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60"
        @click.self="closeModal()"
    >
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md" @click.stop>

            {{-- Header modal --}}
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-slate-900 rounded-t-2xl">
                <div>
                    <h3 class="text-base font-bold text-white">Encaissement</h3>
                    <p class="text-xs text-gray-400 mt-0.5" x-text="'Commande ' + modalOrderNumber"></p>
                </div>
                <button @click="closeModal()" class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-5 space-y-4">
                {{-- Montant --}}
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-center">
                    <p class="text-xs font-semibold text-amber-700 uppercase tracking-wide mb-1">Montant à encaisser</p>
                    <p class="text-3xl font-extrabold text-amber-900" x-text="new Intl.NumberFormat('fr-FR').format(modalAmount) + ' FCFA'"></p>
                </div>

                {{-- Mode de paiement --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Mode de paiement</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            @click="modalMethod = 'CASH'"
                            :class="modalMethod === 'CASH' ? 'ring-2 ring-amber-500 bg-green-100 font-bold' : 'bg-green-50 hover:bg-green-100'"
                            class="px-2 py-2.5 text-xs text-green-800 border border-green-200 rounded-xl transition-all text-center font-semibold">
                            💵 Espèces
                        </button>
                        <button
                            @click="modalMethod = 'CARD'"
                            :class="modalMethod === 'CARD' ? 'ring-2 ring-amber-500 bg-blue-100 font-bold' : 'bg-blue-50 hover:bg-blue-100'"
                            class="px-2 py-2.5 text-xs text-blue-800 border border-blue-200 rounded-xl transition-all text-center font-semibold">
                            💳 Carte
                        </button>
                        <button
                            @click="modalMethod = 'ORANGE_MONEY'"
                            :class="modalMethod === 'ORANGE_MONEY' ? 'ring-2 ring-amber-500 bg-orange-100 font-bold' : 'bg-orange-50 hover:bg-orange-100'"
                            class="px-2 py-2.5 text-xs text-orange-800 border border-orange-200 rounded-xl transition-all text-center font-semibold">
                            🟠 Orange
                        </button>
                        <button
                            @click="modalMethod = 'MTN_MOMO'"
                            :class="modalMethod === 'MTN_MOMO' ? 'ring-2 ring-amber-500 bg-yellow-100 font-bold' : 'bg-yellow-50 hover:bg-yellow-100'"
                            class="px-2 py-2.5 text-xs text-yellow-800 border border-yellow-200 rounded-xl transition-all text-center font-semibold">
                            🟡 MTN
                        </button>
                        <button
                            @click="modalMethod = 'WAVE'"
                            :class="modalMethod === 'WAVE' ? 'ring-2 ring-amber-500 bg-cyan-100 font-bold' : 'bg-cyan-50 hover:bg-cyan-100'"
                            class="px-2 py-2.5 text-xs text-cyan-800 border border-cyan-200 rounded-xl transition-all text-center font-semibold">
                            🌊 Wave
                        </button>
                        <button
                            @click="modalMethod = 'MOOV_MONEY'"
                            :class="modalMethod === 'MOOV_MONEY' ? 'ring-2 ring-amber-500 bg-indigo-100 font-bold' : 'bg-indigo-50 hover:bg-indigo-100'"
                            class="px-2 py-2.5 text-xs text-indigo-800 border border-indigo-200 rounded-xl transition-all text-center font-semibold">
                            🔵 Moov
                        </button>
                    </div>
                </div>

                {{-- Montant reçu (espèces) --}}
                <div x-show="modalMethod === 'CASH'" x-transition>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Montant reçu (FCFA)</label>
                    <input type="number" x-model.number="modalAmountReceived" :min="modalAmount"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-400 text-sm"
                           placeholder="Ex: 10000">
                    <div class="mt-2 flex justify-between text-sm font-bold" x-show="modalAmountReceived > 0">
                        <span class="text-gray-500">Monnaie à rendre :</span>
                        <span class="text-emerald-600" x-text="new Intl.NumberFormat('fr-FR').format(Math.max(0, modalAmountReceived - modalAmount)) + ' FCFA'"></span>
                    </div>
                </div>

                {{-- Référence transaction (mobile/carte) --}}
                <div x-show="modalMethod && modalMethod !== 'CASH'" x-transition>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Référence transaction (optionnel)</label>
                    <input type="text" x-model="modalTransactionId"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-400 text-sm"
                           placeholder="Ex: TXN123456">
                </div>

                {{-- Erreur --}}
                <div x-show="modalError" x-transition class="bg-red-50 border border-red-200 rounded-xl p-3 text-sm text-red-700 font-medium" x-text="modalError"></div>

                {{-- Boutons --}}
                <div class="flex gap-3 pt-1">
                    <button @click="closeModal()" class="flex-1 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-colors text-sm">
                        Annuler
                    </button>
                    <button
                        @click="submitPayment()"
                        :disabled="!modalMethod || modalLoading"
                        class="flex-1 px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 disabled:opacity-40 text-white font-bold rounded-xl transition-colors text-sm flex items-center justify-center gap-2">
                        <template x-if="modalLoading">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </template>
                        <template x-if="!modalLoading">
                            <span>Valider l'encaissement</span>
                        </template>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

</div>{{-- /x-data --}}
@endsection

@push('scripts')
<script>
function paymentsManager() {
    return {
        // Modal state
        showModal: false,
        modalOrderId: null,
        modalOrderNumber: '',
        modalAmount: 0,
        modalMethod: '',
        modalAmountReceived: 0,
        modalTransactionId: '',
        modalError: '',
        modalLoading: false,

        init() {},

        openPayModal(orderId, orderNumber, amount) {
            this.modalOrderId      = orderId;
            this.modalOrderNumber  = orderNumber;
            this.modalAmount       = amount;
            this.modalMethod       = '';
            this.modalAmountReceived = amount; // pré-remplir avec le montant exact
            this.modalTransactionId = '';
            this.modalError        = '';
            this.modalLoading      = false;
            this.showModal         = true;
        },

        closeModal() {
            if (this.modalLoading) return;
            this.showModal = false;
        },

        async submitPayment() {
            if (!this.modalMethod) {
                this.modalError = 'Veuillez sélectionner un mode de paiement.';
                return;
            }
            this.modalLoading = true;
            this.modalError   = '';

            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const payload   = {
                method:         this.modalMethod,
                amount_received: this.modalMethod === 'CASH' ? this.modalAmountReceived : null,
                transaction_id:  this.modalMethod !== 'CASH' ? (this.modalTransactionId || null) : null,
            };

            try {
                const url = `/admin/{{ $tenantSlug }}/payments/order/${this.modalOrderId}/pay`;
                const res = await fetch(url, {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(payload),
                });
                const data = await res.json();

                if (data.success) {
                    // Ouvrir le ticket de paiement
                    window.open(data.receipt_url, '_blank');
                    // Recharger la page pour mettre à jour les listes
                    window.location.reload();
                } else {
                    this.modalError   = data.error || 'Erreur lors du paiement.';
                    this.modalLoading = false;
                }
            } catch (e) {
                this.modalError   = 'Erreur réseau. Veuillez réessayer.';
                this.modalLoading = false;
            }
        },
    };
}
</script>
@endpush
