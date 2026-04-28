@extends('layouts.admin')

@section('title', 'Réservation — ' . $reservation->customer_name)
@section('page-title', 'Détail de la réservation')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="hover:text-amber-500">Dashboard</a>
    <span class="mx-2">/</span>
    <a href="{{ route('admin.reservations.index', $tenant->slug) }}" class="hover:text-amber-500">Réservations</a>
    <span class="mx-2">/</span>
    <span>{{ $reservation->customer_name }}</span>
@endsection

@section('content')
<div x-data="showReservation()" x-init="init()">
    <div class="max-w-2xl mx-auto space-y-5">

        {{-- Feedback --}}
        <div x-show="feedback" x-cloak
             :class="feedbackOk ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-700'"
             class="px-4 py-3 rounded-xl border text-sm font-semibold" x-text="feedback"></div>

        {{-- Status card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold
                        @if($reservation->status === 'PENDING') bg-yellow-100 text-yellow-800
                        @elseif($reservation->status === 'CONFIRMED') bg-blue-100 text-blue-800
                        @elseif($reservation->status === 'SEATED') bg-green-100 text-green-800
                        @elseif($reservation->status === 'COMPLETED') bg-gray-100 text-gray-800
                        @elseif($reservation->status === 'CANCELLED') bg-red-100 text-red-800
                        @elseif($reservation->status === 'NO_SHOW') bg-orange-100 text-orange-800
                        @endif">
                        {{ $reservation->status_label }}
                    </span>
                    <span class="text-xs font-mono text-gray-400">{{ $reservation->confirmation_code }}</span>
                </div>
                <a href="{{ route('admin.reservations.edit', [$tenant->slug, $reservation->id]) }}"
                   class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-lg transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/>
                    </svg>
                    Modifier
                </a>
            </div>

            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Client</p>
                    <p class="text-base font-semibold text-gray-900">{{ $reservation->customer_name }}</p>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $reservation->customer_phone }}</p>
                    @if($reservation->customer_email)
                    <p class="text-sm text-gray-400 mt-0.5">{{ $reservation->customer_email }}</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Date & Heure</p>
                    <p class="text-base font-semibold text-gray-900">{{ $reservation->reservation_date->translatedFormat('d MMMM Y') }}</p>
                    <p class="text-sm text-gray-500 mt-0.5">à {{ $reservation->reservation_time->format('H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Table</p>
                    <p class="text-base font-semibold text-gray-900">{{ $reservation->table->label ?? $reservation->table->code }}</p>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $reservation->party_size }} personne(s)</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Durée</p>
                    <p class="text-base font-semibold text-gray-900">{{ $reservation->duration_minutes ?? 120 }} min</p>
                    @if($reservation->special_requests)
                    <p class="text-sm text-gray-500 mt-1 italic">{{ $reservation->special_requests }}</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Actions --}}
        @if(!in_array($reservation->status, ['COMPLETED', 'CANCELLED', 'NO_SHOW']))
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-base font-semibold text-gray-800">Actions</h3>
            </div>
            <div class="p-6 flex flex-wrap gap-3">
                @if($reservation->status === 'PENDING')
                <button @click="action('confirm')"
                        class="flex-1 min-w-[120px] py-3 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-xl transition text-sm">
                    Confirmer
                </button>
                @endif
                @if($reservation->status === 'CONFIRMED')
                <button @click="action('seat')"
                        class="flex-1 min-w-[120px] py-3 bg-green-500 hover:bg-green-600 text-white font-bold rounded-xl transition text-sm">
                    Installer le client
                </button>
                @endif
                @if($reservation->status === 'SEATED')
                <button @click="action('complete')"
                        class="flex-1 min-w-[120px] py-3 bg-gray-700 hover:bg-gray-800 text-white font-bold rounded-xl transition text-sm">
                    Terminer
                </button>
                @endif
                @if(in_array($reservation->status, ['PENDING', 'CONFIRMED']))
                <button @click="action('noshow')"
                        class="flex-1 min-w-[120px] py-3 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl transition text-sm">
                    Absent
                </button>
                @endif
                <button @click="action('cancel')"
                        class="flex-1 min-w-[120px] py-3 bg-red-100 hover:bg-red-200 text-red-700 font-bold rounded-xl transition text-sm">
                    Annuler
                </button>
            </div>
        </div>
        @endif

        {{-- Back --}}
        <div>
            <a href="{{ route('admin.reservations.index', $tenant->slug) }}"
               class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                </svg>
                Retour à la liste
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showReservation() {
    return {
        feedback: '',
        feedbackOk: false,
        urls: {
            confirm: '{{ route("admin.reservations.confirm", [$tenant->slug, $reservation->id]) }}',
            seat:    '{{ route("admin.reservations.complete", [$tenant->slug, $reservation->id]) }}',
            complete:'{{ route("admin.reservations.complete", [$tenant->slug, $reservation->id]) }}',
            cancel:  '{{ route("admin.reservations.cancel", [$tenant->slug, $reservation->id]) }}',
            noshow:  '{{ route("admin.reservations.noShow", [$tenant->slug, $reservation->id]) }}',
        },

        init() {},

        async action(type) {
            if (type === 'cancel' && !confirm('Annuler cette réservation ?')) return;
            if (type === 'noshow' && !confirm('Marquer le client comme absent ?')) return;
            try {
                const res = await fetch(this.urls[type], {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: type === 'cancel' ? JSON.stringify({ reason: '' }) : undefined,
                });
                const data = await res.json();
                this.feedbackOk = data.success;
                this.feedback = data.message || 'Mise à jour effectuée';
                if (data.success) setTimeout(() => window.location.reload(), 1500);
            } catch (e) {
                this.feedbackOk = false;
                this.feedback = 'Erreur réseau.';
            }
        }
    };
}
</script>
@endpush
@endsection
