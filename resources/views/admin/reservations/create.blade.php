@extends('layouts.admin')

@section('title', 'Nouvelle Réservation')
@section('page-title', 'Nouvelle Réservation')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="hover:text-amber-500">Dashboard</a>
    <span class="mx-2">/</span>
    <a href="{{ route('admin.reservations.index', $tenant->slug) }}" class="hover:text-amber-500">Réservations</a>
    <span class="mx-2">/</span>
    <span>Nouvelle</span>
@endsection

@section('content')
<div x-data="reservationForm()" x-init="init()">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-800">Informations de la réservation</h2>
            </div>
            <div class="p-6 space-y-5">

                {{-- Feedback --}}
                <div x-show="feedback" x-cloak
                     :class="feedbackOk ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-700'"
                     class="px-4 py-3 rounded-xl border text-sm font-semibold" x-text="feedback"></div>

                {{-- Client --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Nom du client *</label>
                        <input type="text" x-model="form.customer_name" placeholder="Jean Dupont"
                               class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Téléphone *</label>
                        <input type="tel" x-model="form.customer_phone" placeholder="+225 07 00 00 00 00"
                               class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Email (optionnel)</label>
                    <input type="email" x-model="form.customer_email" placeholder="client@email.com"
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>

                {{-- Date & heure --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Date *</label>
                        <input type="date" x-model="form.reservation_date" :min="today"
                               class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Heure *</label>
                        <input type="time" x-model="form.reservation_time"
                               class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                </div>

                {{-- Personnes & durée --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Nombre de personnes *</label>
                        <input type="number" x-model.number="form.party_size" min="1" max="20" placeholder="2"
                               class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Durée (minutes)</label>
                        <select x-model.number="form.duration_minutes"
                                class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                            <option value="60">1h</option>
                            <option value="90">1h30</option>
                            <option value="120" selected>2h</option>
                            <option value="150">2h30</option>
                            <option value="180">3h</option>
                            <option value="240">4h</option>
                        </select>
                    </div>
                </div>

                {{-- Table --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Table *</label>
                    <select x-model.number="form.table_id"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                        <option value="">Sélectionner une table</option>
                        @foreach($tables as $table)
                            <option value="{{ $table->id }}">{{ $table->label }} ({{ $table->capacity }} pers.)</option>
                        @endforeach
                    </select>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Demandes spéciales</label>
                    <textarea x-model="form.special_requests" rows="3" placeholder="Allergies, préférences, occasion spéciale…"
                              class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 resize-none"></textarea>
                </div>

                {{-- Actions --}}
                <div class="flex gap-3 pt-2">
                    <a href="{{ route('admin.reservations.index', $tenant->slug) }}"
                       class="flex-1 text-center py-3 border border-gray-200 text-gray-600 font-semibold rounded-xl hover:bg-gray-50 transition text-sm">
                        Annuler
                    </a>
                    <button @click="submit()" :disabled="loading"
                            class="flex-1 py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl transition text-sm disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                        <template x-if="loading">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </template>
                        <span x-text="loading ? 'Enregistrement…' : 'Créer la réservation'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function reservationForm() {
    return {
        loading: false,
        feedback: '',
        feedbackOk: false,
        today: new Date().toISOString().split('T')[0],
        form: {
            customer_name: '',
            customer_phone: '',
            customer_email: '',
            reservation_date: '',
            reservation_time: '',
            party_size: 2,
            duration_minutes: 120,
            table_id: '',
            special_requests: '',
        },

        init() {
            this.form.reservation_date = this.today;
        },

        async submit() {
            this.loading = true;
            this.feedback = '';
            try {
                const res = await fetch('{{ route("admin.reservations.store", $tenant->slug) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.form),
                });
                const data = await res.json();
                this.feedbackOk = data.success;
                this.feedback = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Erreur');
                if (data.success) {
                    setTimeout(() => {
                        window.location.href = '{{ route("admin.reservations.index", $tenant->slug) }}';
                    }, 1200);
                }
            } catch (e) {
                this.feedbackOk = false;
                this.feedback = 'Erreur réseau. Veuillez réessayer.';
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
@endpush
@endsection
