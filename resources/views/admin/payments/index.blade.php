@extends('layouts.admin')

@section('title', 'Gestion des Paiements')

@section('content')
<div x-data="paymentsManager()" x-init="init()" class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Paiements</h1>
            <p class="text-gray-600">Historique et suivi des encaissements</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.orders.index', $tenantSlug) }}"
               class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-lg transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Commandes
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-green-50 rounded-lg p-4 border-l-4 border-green-500">
            <p class="text-green-700 text-xs font-medium mb-1">Encaissé (Jour)</p>
            <p class="text-2xl font-bold text-green-900">{{ number_format($stats['total'], 0, ',', ' ') }} F</p>
        </div>
        <div class="bg-blue-50 rounded-lg p-4 border-l-4 border-blue-500">
            <p class="text-blue-700 text-xs font-medium mb-1">Transactions</p>
            <p class="text-2xl font-bold text-blue-900">{{ $stats['count'] }}</p>
        </div>
        <div class="bg-emerald-50 rounded-lg p-4 border-l-4 border-emerald-500">
            <p class="text-emerald-700 text-xs font-medium mb-1">Espèces</p>
            <p class="text-2xl font-bold text-emerald-900">{{ number_format($stats['cash'], 0, ',', ' ') }} F</p>
        </div>
        <div class="bg-indigo-50 rounded-lg p-4 border-l-4 border-indigo-500">
            <p class="text-indigo-700 text-xs font-medium mb-1">Carte</p>
            <p class="text-2xl font-bold text-indigo-900">{{ number_format($stats['card'], 0, ',', ' ') }} F</p>
        </div>
        <div class="bg-orange-50 rounded-lg p-4 border-l-4 border-orange-500">
            <p class="text-orange-700 text-xs font-medium mb-1">Mobile Money</p>
            <p class="text-2xl font-bold text-orange-900">{{ number_format($stats['mobile'], 0, ',', ' ') }} F</p>
        </div>
    </div>

    <!-- Unpaid Orders Alert -->
    @if($unpaidOrders->count() > 0)
    <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <p class="font-semibold text-red-800">{{ $unpaidOrders->count() }} commande(s) non payée(s)</p>
                    <p class="text-sm text-red-600">Total impayé: {{ number_format($totalUnpaid, 0, ',', ' ') }} FCFA</p>
                </div>
            </div>
            <a href="{{ route('admin.orders.index', $tenantSlug) }}?paymentStatus=PENDING"
               class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                Voir les commandes
            </a>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <form method="GET" class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <select name="method" class="border-gray-300 rounded-lg text-sm">
                <option value="">Tous les modes</option>
                @foreach($paymentMethods as $method)
                    <option value="{{ $method->value }}" {{ request('method') == $method->value ? 'selected' : '' }}>
                        {{ $method->label() }}
                    </option>
                @endforeach
            </select>
            <select name="status" class="border-gray-300 rounded-lg text-sm">
                <option value="">Tous les statuts</option>
                <option value="SUCCESS" {{ request('status') == 'SUCCESS' ? 'selected' : '' }}>Succès</option>
                <option value="REFUNDED" {{ request('status') == 'REFUNDED' ? 'selected' : '' }}>Remboursé</option>
            </select>
            <input type="date" name="date" value="{{ request('date') }}" class="border-gray-300 rounded-lg text-sm">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">
                Filtrer
            </button>
            <a href="{{ route('admin.payments.index', $tenantSlug) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium flex items-center justify-center">
                Réinitialiser
            </a>
        </form>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transaction</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Commande</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Table</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mode</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Caissier</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm font-mono text-gray-900">{{ $payment->transaction_id }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <a href="{{ route('admin.orders.show', [$tenantSlug, $payment->order_id]) }}"
                                   class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                    {{ $payment->order->order_number ?? '#' . $payment->order_id }}
                                </a>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $payment->order->table->label ?? 'N/A' }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">{{ number_format($payment->amount, 0, ',', ' ') }} F</div>
                                @if($payment->change_given > 0)
                                    <div class="text-xs text-yellow-600">Rendu: {{ number_format($payment->change_given, 0, ',', ' ') }} F</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $payment->method_color }}">
                                    {{ $payment->method_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($payment->status === 'SUCCESS')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Succès</span>
                                @elseif($payment->status === 'REFUNDED')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">Remboursé</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ $payment->status }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $payment->processedBy->name ?? 'Système' }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $payment->created_at->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $payment->created_at->format('H:i') }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                                <div class="flex gap-1">
                                    <!-- Imprimer ticket -->
                                    <a href="{{ route('admin.payments.receipt', [$tenantSlug, $payment->id]) }}"
                                       target="_blank"
                                       class="p-1.5 text-indigo-600 hover:bg-indigo-50 rounded" title="Imprimer ticket">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <p>Aucun paiement enregistré</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($payments->hasPages())
            <div class="px-4 py-3 bg-gray-50 border-t">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function paymentsManager() {
    return {
        init() {
            // Future: auto-refresh, real-time updates
        }
    };
}
</script>
@endsection
