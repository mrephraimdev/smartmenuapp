@extends('layouts.admin')

@section('title', 'Détails Session POS - ' . $session->session_number)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('admin.pos.sessions', $tenantSlug) }}"
                       class="text-gray-600 hover:text-gray-900 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $session->session_number }}</h1>
                    @if($session->status === 'OPEN')
                        <span class="bg-green-500 text-white px-3 py-1 rounded-full font-semibold text-sm">OUVERTE</span>
                    @else
                        <span class="bg-gray-500 text-white px-3 py-1 rounded-full font-semibold text-sm">FERMÉE</span>
                    @endif
                </div>
                <p class="text-gray-600">Détails de la session de caisse</p>
            </div>
            @if($session->status === 'CLOSED')
                <a href="{{ route('admin.pos.z-report', [$tenantSlug, $session->id]) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Voir Rapport Z
                </a>
            @endif
        </div>
    </div>

    <!-- Session Info -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Financial Summary -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Résumé Financier</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <div class="text-sm text-gray-600 mb-1">Fond de caisse</div>
                        <div class="text-xl font-bold text-gray-900">{{ number_format($session->opening_float, 0, ',', ' ') }} FCFA</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600 mb-1">Total ventes</div>
                        <div class="text-xl font-bold text-green-600">{{ number_format($session->total_sales, 0, ',', ' ') }} FCFA</div>
                    </div>
                    @if($session->status === 'CLOSED')
                        <div>
                            <div class="text-sm text-gray-600 mb-1">Caisse réelle</div>
                            <div class="text-xl font-bold text-gray-900">{{ number_format($session->actual_cash, 0, ',', ' ') }} FCFA</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600 mb-1">Écart</div>
                            <div class="text-xl font-bold {{ $session->cash_difference >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $session->cash_difference >= 0 ? '+' : '' }}{{ number_format($session->cash_difference, 0, ',', ' ') }} FCFA
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Payment Methods Breakdown -->
                @if($session->status === 'CLOSED')
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Répartition des paiements</h3>
                        <div class="grid grid-cols-3 gap-4">
                            <div class="bg-blue-50 rounded-lg p-3">
                                <div class="text-xs text-blue-700 mb-1">Espèces</div>
                                <div class="text-lg font-bold text-blue-900">{{ number_format($session->cash_sales, 0, ',', ' ') }} FCFA</div>
                            </div>
                            <div class="bg-purple-50 rounded-lg p-3">
                                <div class="text-xs text-purple-700 mb-1">Carte</div>
                                <div class="text-lg font-bold text-purple-900">{{ number_format($session->card_sales, 0, ',', ' ') }} FCFA</div>
                            </div>
                            <div class="bg-orange-50 rounded-lg p-3">
                                <div class="text-xs text-orange-700 mb-1">Mobile</div>
                                <div class="text-lg font-bold text-orange-900">{{ number_format($session->mobile_sales, 0, ',', ' ') }} FCFA</div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Statistics -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Statistiques</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-sm text-gray-600 mb-1">Commandes</div>
                        <div class="text-2xl font-bold text-gray-900">{{ $session->total_orders }}</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-sm text-gray-600 mb-1">Articles vendus</div>
                        <div class="text-2xl font-bold text-gray-900">{{ $session->total_items }}</div>
                    </div>
                    @if($session->status === 'CLOSED')
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="text-sm text-gray-600 mb-1">Annulées</div>
                            <div class="text-2xl font-bold text-red-600">{{ $session->cancelled_orders }}</div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="text-sm text-gray-600 mb-1">Remboursements</div>
                            <div class="text-2xl font-bold text-red-600">{{ number_format($session->refunds_total, 0, ',', ' ') }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Orders List -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Commandes ({{ $session->orders->count() }})</h2>

                @if($session->orders->count() > 0)
                    <div class="space-y-3">
                        @foreach($session->orders as $order)
                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-3">
                                        <span class="font-semibold text-gray-900">{{ $order->order_number }}</span>
                                        <span class="text-sm text-gray-600">Table {{ $order->table->label ?? $order->table->code }}</span>
                                        @if($order->status === 'ANNULE')
                                            <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-medium">Annulée</span>
                                        @else
                                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-medium">{{ $order->getStatusLabel() }}</span>
                                        @endif
                                    </div>
                                    <span class="font-bold text-gray-900">{{ number_format($order->total, 0, ',', ' ') }} FCFA</span>
                                </div>
                                <div class="text-sm text-gray-600">
                                    <div class="flex items-center gap-4">
                                        <span>{{ $order->items->count() }} article(s)</span>
                                        <span>{{ $order->created_at->format('H:i') }}</span>
                                    </div>
                                </div>
                                @if($order->items->count() > 0)
                                    <div class="mt-3 pt-3 border-t border-gray-100 space-y-1">
                                        @foreach($order->items as $item)
                                            <div class="text-sm text-gray-700 flex justify-between">
                                                <span>{{ $item->quantity }}x {{ $item->dish->name ?? 'Article supprimé' }}</span>
                                                <span class="text-gray-600">{{ number_format($item->unit_price * $item->quantity, 0, ',', ' ') }} FCFA</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p>Aucune commande dans cette session</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <!-- Session Details -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Informations</h2>
                <div class="space-y-3">
                    <div>
                        <div class="text-sm text-gray-600">Ouvert par</div>
                        <div class="font-medium text-gray-900">{{ $session->user->name ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600">Date d'ouverture</div>
                        <div class="font-medium text-gray-900">{{ $session->opened_at->format('d/m/Y à H:i') }}</div>
                    </div>
                    @if($session->status === 'CLOSED' && $session->closed_at)
                        <div>
                            <div class="text-sm text-gray-600">Date de fermeture</div>
                            <div class="font-medium text-gray-900">{{ $session->closed_at->format('d/m/Y à H:i') }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600">Durée totale</div>
                            <div class="font-medium text-gray-900">{{ $session->getDurationFormatted() }}</div>
                        </div>
                    @else
                        <div>
                            <div class="text-sm text-gray-600">Durée</div>
                            <div class="font-medium text-gray-900">{{ $session->getDurationInMinutes() }} minutes</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Notes -->
            @if($session->opening_notes || $session->closing_notes)
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Notes</h2>
                    <div class="space-y-4">
                        @if($session->opening_notes)
                            <div>
                                <div class="text-sm font-medium text-gray-600 mb-1">Ouverture</div>
                                <div class="text-gray-900 bg-gray-50 rounded p-3">{{ $session->opening_notes }}</div>
                            </div>
                        @endif
                        @if($session->closing_notes)
                            <div>
                                <div class="text-sm font-medium text-gray-600 mb-1">Fermeture</div>
                                <div class="text-gray-900 bg-gray-50 rounded p-3">{{ $session->closing_notes }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
