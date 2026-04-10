@extends('layouts.admin')

@section('title', 'Rapport X - ' . $session->session_number)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('admin.pos.index', $tenantSlug) }}"
                       class="text-gray-600 hover:text-gray-900 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900">RAPPORT X</h1>
                    <span class="bg-blue-500 text-white px-3 py-1 rounded-full font-semibold text-sm">INTERMÉDIAIRE</span>
                </div>
                <p class="text-gray-600">{{ $session->session_number }} - Généré le {{ now()->format('d/m/Y à H:i') }}</p>
            </div>
            <div class="flex gap-3">
                <button onclick="window.location.reload()"
                        class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Actualiser
                </button>
                <button onclick="window.print()"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Imprimer
                </button>
            </div>
        </div>
    </div>

    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
        <p class="text-blue-700">
            <strong>ℹ️ RAPPORT X - Rapport intermédiaire</strong> - Ce rapport affiche les ventes en cours. La session reste ouverte.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Current Totals -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Ventes en cours</h2>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="text-sm text-blue-700 mb-1">Fond de caisse initial</div>
                        <div class="text-2xl font-bold text-blue-900">{{ number_format($session->opening_float, 0, ',', ' ') }} FCFA</div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="text-sm text-green-700 mb-1">Total ventes</div>
                        <div class="text-2xl font-bold text-green-900">{{ number_format($current_totals['total_sales'], 0, ',', ' ') }} FCFA</div>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="text-sm text-purple-700 mb-1">Caisse attendue</div>
                        <div class="text-2xl font-bold text-purple-900">{{ number_format($expected_cash, 0, ',', ' ') }} FCFA</div>
                    </div>
                    <div class="bg-orange-50 rounded-lg p-4">
                        <div class="text-sm text-orange-700 mb-1">Durée session</div>
                        <div class="text-2xl font-bold text-orange-900">{{ $summary['duration_minutes'] }} min</div>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="pt-4 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Répartition des paiements</h3>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="text-center bg-gray-50 rounded-lg p-4">
                            <div class="text-sm text-gray-600 mb-1">Espèces</div>
                            <div class="text-xl font-bold text-gray-900">{{ number_format($current_totals['cash_sales'], 0, ',', ' ') }}</div>
                            <div class="text-xs text-gray-500">FCFA</div>
                        </div>
                        <div class="text-center bg-gray-50 rounded-lg p-4">
                            <div class="text-sm text-gray-600 mb-1">Carte</div>
                            <div class="text-xl font-bold text-gray-900">{{ number_format($current_totals['card_sales'], 0, ',', ' ') }}</div>
                            <div class="text-xs text-gray-500">FCFA</div>
                        </div>
                        <div class="text-center bg-gray-50 rounded-lg p-4">
                            <div class="text-sm text-gray-600 mb-1">Mobile Money</div>
                            <div class="text-xl font-bold text-gray-900">{{ number_format($current_totals['mobile_sales'], 0, ',', ' ') }}</div>
                            <div class="text-xs text-gray-500">FCFA</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Statistics -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Statistiques des commandes</h2>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="text-center bg-blue-50 rounded-lg p-4">
                        <div class="text-3xl font-bold text-blue-600">{{ $current_totals['total_orders'] }}</div>
                        <div class="text-sm text-gray-600">Commandes</div>
                    </div>
                    <div class="text-center bg-green-50 rounded-lg p-4">
                        <div class="text-3xl font-bold text-green-600">{{ $current_totals['total_items'] }}</div>
                        <div class="text-sm text-gray-600">Articles vendus</div>
                    </div>
                    <div class="text-center bg-red-50 rounded-lg p-4">
                        <div class="text-3xl font-bold text-red-600">{{ $current_totals['cancelled_orders'] }}</div>
                        <div class="text-sm text-gray-600">Annulées</div>
                    </div>
                    <div class="text-center bg-purple-50 rounded-lg p-4">
                        <div class="text-3xl font-bold text-purple-600">{{ number_format($summary['average_order_value'], 0) }}</div>
                        <div class="text-sm text-gray-600">Panier moyen</div>
                    </div>
                </div>

                <!-- Orders by Status -->
                @if(!empty($orders_by_status))
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Commandes par statut</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            @foreach($orders_by_status as $status => $count)
                                <div class="bg-gray-50 rounded p-3">
                                    <div class="font-semibold text-gray-900">{{ $count }}</div>
                                    <div class="text-xs text-gray-600">{{ $status }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Top Dishes -->
            @if($top_dishes->count() > 0)
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Top 10 Plats Vendus</h2>
                    <div class="space-y-3">
                        @foreach($top_dishes as $index => $dish)
                            <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-gray-400 w-8">#{{ $index + 1 }}</div>
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-900">{{ $dish->name }}</div>
                                    <div class="text-sm text-gray-600">{{ $dish->total_quantity }} unités vendues</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-green-600">{{ number_format($dish->total_revenue, 0, ',', ' ') }} FCFA</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Session Info -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Informations Session</h2>
                <div class="space-y-3">
                    <div>
                        <div class="text-sm text-gray-600">Session</div>
                        <div class="font-medium text-gray-900">{{ $session->session_number }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600">Caissier</div>
                        <div class="font-medium text-gray-900">{{ $session->user->name ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600">Ouverture</div>
                        <div class="font-medium text-gray-900">{{ $session->opened_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600">Statut</div>
                        <span class="inline-block bg-green-500 text-white px-3 py-1 rounded-full font-semibold text-xs">OUVERTE</span>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            @if($session->opening_notes)
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Notes d'ouverture</h2>
                    <div class="text-gray-900 bg-gray-50 rounded p-3 text-sm">{{ $session->opening_notes }}</div>
                </div>
            @endif

            <!-- Refunds Info -->
            @if($current_totals['refunds_total'] > 0)
                <div class="bg-red-50 rounded-lg p-6 border-l-4 border-red-500">
                    <h3 class="text-lg font-semibold text-red-900 mb-2">Remboursements</h3>
                    <div class="text-2xl font-bold text-red-600">{{ number_format($current_totals['refunds_total'], 0, ',', ' ') }} FCFA</div>
                    <p class="text-sm text-red-700 mt-1">Total des remboursements effectués</p>
                </div>
            @endif

            <!-- Generation Time -->
            <div class="bg-gray-50 rounded-lg p-6 text-center">
                <p class="text-sm text-gray-600 mb-2">Rapport généré le</p>
                <p class="font-medium text-gray-900">{{ now()->format('d/m/Y à H:i:s') }}</p>
                <p class="text-xs text-gray-500 mt-3">Ce rapport peut être actualisé en temps réel</p>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .container {
        max-width: 100%;
    }
    button {
        display: none;
    }
    a {
        display: none;
    }
}
</style>
@endsection
