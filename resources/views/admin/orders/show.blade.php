@extends('layouts.admin')

@section('title', 'Détails Commande #' . ($order->order_number ?? $order->id))

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <a href="{{ route('admin.orders.index', $tenantSlug) }}" class="text-indigo-600 hover:text-indigo-800 flex items-center gap-1 mb-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Retour aux commandes
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Commande #{{ $order->order_number ?? $order->id }}</h1>
            <p class="text-gray-600">Créée le {{ $order->created_at->format('d/m/Y à H:i') }}</p>
        </div>

        <div class="flex gap-3">
            @if($order->status !== 'SERVI' && $order->status !== 'ANNULE')
                <button onclick="progressOrder({{ $order->id }})"
                        class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                    Avancer
                </button>
            @endif
            @if($order->status !== 'ANNULE')
                <button onclick="cancelOrder({{ $order->id }})"
                        class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Annuler
                </button>
            @endif
            <a href="{{ route('admin.print.receipt', [$tenantSlug, $order->id]) }}"
               target="_blank"
               class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimer
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Order Info -->
        <div class="lg:col-span-2">
            <!-- Status & Info Card -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">Informations</h2>
                    @php
                        $statusColors = [
                            'RECU' => 'bg-blue-100 text-blue-800 border-blue-200',
                            'PREP' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                            'PRET' => 'bg-green-100 text-green-800 border-green-200',
                            'SERVI' => 'bg-gray-100 text-gray-800 border-gray-200',
                            'ANNULE' => 'bg-red-100 text-red-800 border-red-200'
                        ];
                        $statusLabels = [
                            'RECU' => 'Recu',
                            'PREP' => 'En préparation',
                            'PRET' => 'Pret',
                            'SERVI' => 'Servi',
                            'ANNULE' => 'Annule'
                        ];
                    @endphp
                    <span class="px-4 py-2 rounded-full text-sm font-semibold border {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ $statusLabels[$order->status] ?? $order->status }}
                    </span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-500 mb-1">Table</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $order->table->label ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-500">{{ $order->table->code ?? '' }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-500 mb-1">Articles</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $order->items->count() }}</p>
                        <p class="text-xs text-gray-500">{{ $order->items->sum('quantity') }} unités</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-500 mb-1">Total</p>
                        <p class="text-lg font-semibold text-gray-900">{{ number_format($order->total, 0, ',', ' ') }} FCFA</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-500 mb-1">Heure</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $order->created_at->format('H:i') }}</p>
                        <p class="text-xs text-gray-500">{{ $order->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Articles commandés</h2>
                </div>
                <div class="divide-y divide-gray-200">
                    @foreach($order->items as $item)
                        <div class="p-6 flex items-start gap-4">
                            @if($item->dish && $item->dish->image_url)
                                <img src="{{ $item->dish->image_url }}" alt="{{ $item->dish->name }}" class="w-20 h-20 object-cover rounded-lg">
                            @else
                                <div class="w-20 h-20 bg-gray-200 rounded-lg flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                            <div class="flex-1">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-semibold text-gray-900">{{ $item->dish->name ?? 'Plat supprimé' }}</h3>
                                        @if($item->variant)
                                            <p class="text-sm text-indigo-600">{{ $item->variant->name }}</p>
                                        @endif
                                        @php
                                            $options = $item->options;
                                            if (is_string($options)) {
                                                $options = json_decode($options, true) ?? [];
                                            }
                                        @endphp
                                        @if($options && is_array($options) && count($options) > 0)
                                            <p class="text-sm text-gray-500 mt-1">
                                                Options: {{ implode(', ', $options) }}
                                            </p>
                                        @elseif($item->options && is_string($item->options) && !empty($item->options))
                                            <p class="text-sm text-gray-500 mt-1">
                                                Options: {{ $item->options }}
                                            </p>
                                        @endif
                                        @if($item->notes)
                                            <p class="text-sm text-gray-500 mt-1 italic">"{{ $item->notes }}"</p>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold text-gray-900">{{ number_format($item->subtotal, 0, ',', ' ') }} FCFA</p>
                                        <p class="text-sm text-gray-500">{{ $item->quantity }} x {{ number_format($item->unit_price, 0, ',', ' ') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Total Card -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Récapitulatif</h2>
                <div class="space-y-3">
                    <div class="flex justify-between text-gray-600">
                        <span>Sous-total</span>
                        <span>{{ number_format($order->subtotal ?? $order->total, 0, ',', ' ') }} FCFA</span>
                    </div>
                    @if(isset($order->tax) && $order->tax > 0)
                        <div class="flex justify-between text-gray-600">
                            <span>TVA</span>
                            <span>{{ number_format($order->tax, 0, ',', ' ') }} FCFA</span>
                        </div>
                    @endif
                    @if(isset($order->discount) && $order->discount > 0)
                        <div class="flex justify-between text-green-600">
                            <span>Remise</span>
                            <span>-{{ number_format($order->discount, 0, ',', ' ') }} FCFA</span>
                        </div>
                    @endif
                    <div class="border-t pt-3">
                        <div class="flex justify-between text-xl font-bold text-gray-900">
                            <span>Total</span>
                            <span>{{ number_format($order->total, 0, ',', ' ') }} FCFA</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Timeline -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Historique</h2>
                <div class="space-y-4">
                    @php
                        $statusFlow = ['RECU', 'PREP', 'PRET', 'SERVI'];
                        $currentIndex = array_search($order->status, $statusFlow);
                        if ($order->status === 'ANNULE') $currentIndex = -1;
                    @endphp

                    @foreach($statusFlow as $index => $status)
                        @php
                            $isCompleted = $currentIndex !== false && $index <= $currentIndex;
                            $isCurrent = $order->status === $status;
                        @endphp
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center {{ $isCompleted ? 'bg-green-500' : 'bg-gray-200' }}">
                                @if($isCompleted)
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                @else
                                    <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
                                @endif
                            </div>
                            <div class="{{ $isCurrent ? 'font-semibold text-gray-900' : ($isCompleted ? 'text-gray-600' : 'text-gray-400') }}">
                                {{ $statusLabels[$status] }}
                            </div>
                        </div>
                    @endforeach

                    @if($order->status === 'ANNULE')
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center bg-red-500">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <div class="font-semibold text-red-600">Annulée</div>
                        </div>
                    @endif
                </div>
            </div>

            @if($order->notes)
                <!-- Notes -->
                <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Notes</h2>
                    <p class="text-gray-600 italic">"{{ $order->notes }}"</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function progressOrder(orderId) {
    if (!confirm('Voulez-vous faire avancer cette commande au statut suivant?')) {
        return;
    }

    fetch(`/admin/{{ $tenantSlug }}/orders/${orderId}/progress`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur: ' + data.message);
        }
    });
}

function cancelOrder(orderId) {
    if (!confirm('Voulez-vous vraiment annuler cette commande?')) {
        return;
    }

    fetch(`/admin/{{ $tenantSlug }}/orders/${orderId}/cancel`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur: ' + data.message);
        }
    });
}
</script>
@endsection
