@extends('layouts.admin')

@section('title', 'Réservations')

@section('content')
<div x-data="reservationsManager()" x-init="init()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Réservations</h1>
            <p class="mt-1 text-sm text-gray-500">Gérez les réservations de votre établissement</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <a href="{{ route('admin.reservations.calendar', $tenant->slug) }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Vue calendrier
            </a>
            <a href="{{ route('admin.reservations.create', $tenant->slug) }}"
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nouvelle réservation
            </a>
        </div>
    </div>

    <!-- Today's Reservations -->
    @if($todayReservations->isNotEmpty())
    <div class="mb-8">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Aujourd'hui ({{ $todayReservations->count() }})</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($todayReservations as $reservation)
                <div class="bg-white rounded-lg shadow p-4 border-l-4
                    @if($reservation->status === 'PENDING') border-yellow-400
                    @elseif($reservation->status === 'CONFIRMED') border-blue-400
                    @elseif($reservation->status === 'SEATED') border-green-400
                    @else border-gray-400 @endif">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-medium text-gray-900">{{ $reservation->customer_name }}</p>
                            <p class="text-sm text-gray-500">{{ $reservation->reservation_time->format('H:i') }} - {{ $reservation->party_size }} pers.</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($reservation->status === 'PENDING') bg-yellow-100 text-yellow-800
                            @elseif($reservation->status === 'CONFIRMED') bg-blue-100 text-blue-800
                            @elseif($reservation->status === 'SEATED') bg-green-100 text-green-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $reservation->status_label }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 mt-2">Table: {{ $reservation->table->label ?? $reservation->table->code }}</p>
                    <div class="mt-3 flex space-x-2">
                        @if($reservation->status === 'PENDING')
                            <button @click="confirmReservation({{ $reservation->id }})"
                                    class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200">
                                Confirmer
                            </button>
                        @endif
                        @if($reservation->status === 'CONFIRMED')
                            <button @click="seatReservation({{ $reservation->id }})"
                                    class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200">
                                Installer
                            </button>
                        @endif
                        <button @click="cancelReservation({{ $reservation->id }})"
                                class="text-xs px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200">
                            Annuler
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Upcoming Reservations Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Réservations à venir</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Heure</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Personnes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Table</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($reservations as $reservation)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $reservation->customer_name }}</div>
                                <div class="text-sm text-gray-500">{{ $reservation->customer_phone }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $reservation->reservation_date->format('d/m/Y') }}</div>
                                <div class="text-sm text-gray-500">{{ $reservation->reservation_time->format('H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $reservation->party_size }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $reservation->table->label ?? $reservation->table->code }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($reservation->status === 'PENDING') bg-yellow-100 text-yellow-800
                                    @elseif($reservation->status === 'CONFIRMED') bg-blue-100 text-blue-800
                                    @elseif($reservation->status === 'SEATED') bg-green-100 text-green-800
                                    @elseif($reservation->status === 'COMPLETED') bg-gray-100 text-gray-800
                                    @elseif($reservation->status === 'CANCELLED') bg-red-100 text-red-800
                                    @else bg-orange-100 text-orange-800 @endif">
                                    {{ $reservation->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.reservations.show', [$tenant->slug, $reservation->id]) }}"
                                   class="text-primary-600 hover:text-primary-900 mr-3">Voir</a>
                                <a href="{{ route('admin.reservations.edit', [$tenant->slug, $reservation->id]) }}"
                                   class="text-primary-600 hover:text-primary-900">Modifier</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                Aucune réservation à venir
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function reservationsManager() {
    return {
        init() {},

        async confirmReservation(id) {
            await this.updateStatus(id, 'confirm');
        },

        async seatReservation(id) {
            await this.updateStatus(id, 'complete');
        },

        async cancelReservation(id) {
            if (!confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')) return;
            await this.updateStatus(id, 'cancel');
        },

        async updateStatus(id, action) {
            try {
                const response = await fetch(`{{ url('/admin/' . $tenant->slug . '/reservations') }}/${id}/${action}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Une erreur est survenue');
                }
            } catch (e) {
                alert('Erreur de connexion');
            }
        }
    }
}
</script>
@endsection
