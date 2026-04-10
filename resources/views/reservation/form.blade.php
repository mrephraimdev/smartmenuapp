@extends('layouts.app')

@section('title', 'Réserver une table - ' . $tenant->name)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">

    {{-- Hero Header --}}
    <div class="pt-10 pb-6 px-4 text-center">
        @if($tenant->logo_url)
            <div class="w-20 h-20 mx-auto mb-4 rounded-2xl overflow-hidden bg-white p-1.5 shadow-xl">
                <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}" class="w-full h-full object-contain rounded-xl">
            </div>
        @endif
        <h1 class="text-3xl font-extrabold tracking-tight" style="color: #f0abfc; text-shadow: 0 0 20px rgba(240,171,252,0.3);">{{ $tenant->name }}</h1>
        <p class="mt-2 text-slate-400 text-base">Réservez votre table en quelques clics</p>
    </div>

    {{-- Form Card --}}
    <div class="max-w-lg mx-auto px-4 pb-12">
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">

            <form id="reservationForm" x-data="reservationForm()" @submit.prevent="submitForm">

                {{-- Section: Informations personnelles --}}
                <div class="p-6 pb-0">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">Vos informations</h3>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label for="customer_name" class="block text-sm font-semibold text-slate-700 mb-1.5">Nom complet *</label>
                            <input type="text" id="customer_name" x-model="form.customer_name" required placeholder="Votre nom"
                                   class="block w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-all">
                        </div>
                        <div>
                            <label for="customer_phone" class="block text-sm font-semibold text-slate-700 mb-1.5">Téléphone *</label>
                            <input type="tel" id="customer_phone" x-model="form.customer_phone" required placeholder="07 XX XX XX XX"
                                   class="block w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-all">
                        </div>
                        <div>
                            <label for="customer_email" class="block text-sm font-semibold text-slate-700 mb-1.5">Email <span class="text-slate-400 font-normal">(optionnel)</span></label>
                            <input type="email" id="customer_email" x-model="form.customer_email" placeholder="votre@email.com"
                                   class="block w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-all">
                        </div>
                    </div>
                </div>

                {{-- Separator --}}
                <div class="px-6 py-5"><div class="border-t border-slate-100"></div></div>

                {{-- Section: Détails de la réservation --}}
                <div class="px-6 pb-0">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">Votre réservation</h3>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="reservation_date" class="block text-sm font-semibold text-slate-700 mb-1.5">Date *</label>
                                <input type="date" id="reservation_date" x-model="form.reservation_date" required :min="minDate"
                                       class="block w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-all">
                            </div>
                            <div>
                                <label for="reservation_time" class="block text-sm font-semibold text-slate-700 mb-1.5">Heure *</label>
                                <select id="reservation_time" x-model="form.reservation_time" required
                                        class="block w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-all">
                                    <option value="">Choisir...</option>
                                    <template x-for="time in timeSlots" :key="time">
                                        <option :value="time" x-text="time"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="party_size" class="block text-sm font-semibold text-slate-700 mb-1.5">Nombre de personnes *</label>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="n in 10" :key="n">
                                    <button type="button" @click="form.party_size = n"
                                            :class="form.party_size === n ? 'bg-indigo-600 text-white border-indigo-600 shadow-lg shadow-indigo-500/30' : 'bg-white text-slate-700 border-slate-200 hover:border-indigo-300'"
                                            class="w-11 h-11 rounded-xl border-2 font-bold text-sm transition-all"
                                            x-text="n">
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div>
                            <label for="special_requests" class="block text-sm font-semibold text-slate-700 mb-1.5">Demandes spéciales <span class="text-slate-400 font-normal">(optionnel)</span></label>
                            <textarea id="special_requests" x-model="form.special_requests" rows="3"
                                      placeholder="Allergies, occasion spéciale, préférences..."
                                      class="block w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-all resize-none"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Error --}}
                <div x-show="error" x-cloak class="mx-6 mt-4 p-4 bg-red-50 border border-red-200 rounded-xl">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm font-medium text-red-700" x-text="error"></p>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="p-6">
                    <button type="submit" :disabled="loading"
                            class="w-full flex items-center justify-center gap-2 py-4 px-6 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-base rounded-2xl shadow-lg shadow-indigo-500/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <template x-if="loading">
                            <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <span x-text="loading ? 'Réservation en cours...' : 'Confirmer la réservation'"></span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Contact --}}
        @if($tenant->phone)
        <div class="mt-6 text-center">
            <p class="text-slate-500 text-sm">Des questions ? Appelez-nous</p>
            <a href="tel:{{ $tenant->phone }}" class="text-indigo-400 hover:text-indigo-300 font-semibold text-base transition-colors">{{ $tenant->phone }}</a>
        </div>
        @endif
    </div>
</div>

<script>
function reservationForm() {
    return {
        form: {
            customer_name: '',
            customer_phone: '',
            customer_email: '',
            reservation_date: '',
            reservation_time: '',
            party_size: 2,
            special_requests: ''
        },
        loading: false,
        error: '',
        minDate: new Date().toISOString().split('T')[0],
        timeSlots: [],

        init() {
            this.generateTimeSlots();
        },

        generateTimeSlots() {
            const slots = [];
            for (let h = 11; h <= 22; h++) {
                slots.push(`${h.toString().padStart(2, '0')}:00`);
                if (h < 22) slots.push(`${h.toString().padStart(2, '0')}:30`);
            }
            this.timeSlots = slots;
        },

        async submitForm() {
            this.loading = true;
            this.error = '';

            try {
                const response = await fetch('{{ route("reservation.store", $tenant->slug) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    this.error = data.message || 'Une erreur est survenue';
                }
            } catch (e) {
                this.error = 'Erreur de connexion. Veuillez réessayer.';
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endsection
