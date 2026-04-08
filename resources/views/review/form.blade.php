@extends('layouts.app')

@section('title', 'Donnez votre avis - ' . $tenant->name)

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
        <p class="mt-2 text-slate-400 text-base">Partagez votre experience avec nous</p>
    </div>

    {{-- Form Card --}}
    <div class="max-w-lg mx-auto px-4 pb-12">
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">

            <form id="reviewForm" x-data="reviewForm()" @submit.prevent="submitForm">

                {{-- Section: Notations --}}
                <div class="p-6 pb-0">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">Notez votre experience</h3>
                    </div>

                    <div class="space-y-3">
                        @foreach(['food' => ['label' => 'Cuisine', 'emoji' => '🍽'], 'service' => ['label' => 'Service', 'emoji' => '🤝'], 'ambiance' => ['label' => 'Ambiance', 'emoji' => '✨']] as $key => $cat)
                        <div class="flex items-center justify-between bg-slate-50 rounded-xl p-4">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">{{ $cat['emoji'] }}</span>
                                <span class="font-semibold text-slate-800">{{ $cat['label'] }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <template x-for="star in 5" :key="'{{ $key }}-' + star">
                                    <button type="button" @click="form.{{ $key }}_rating = star" class="focus:outline-none transition-transform hover:scale-110">
                                        <svg class="w-8 h-8 transition-colors" :class="star <= form.{{ $key }}_rating ? 'text-amber-400' : 'text-slate-200'" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </button>
                                </template>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Separator --}}
                <div class="px-6 py-5"><div class="border-t border-slate-100"></div></div>

                {{-- Section: Commentaire + Infos --}}
                <div class="px-6 pb-0 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Votre commentaire</label>
                        <textarea x-model="form.comment" rows="4" placeholder="Décrivez votre experience..."
                                  class="block w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-all resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Votre nom *</label>
                        <input type="text" x-model="form.customer_name" required placeholder="Votre nom"
                               class="block w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email <span class="text-slate-400 font-normal">(optionnel)</span></label>
                        <input type="email" x-model="form.customer_email" placeholder="votre@email.com"
                               class="block w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-all">
                    </div>
                    <label class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl cursor-pointer">
                        <input type="checkbox" x-model="form.is_anonymous" class="w-5 h-5 text-indigo-600 rounded-md border-slate-300 focus:ring-indigo-500">
                        <span class="text-sm font-medium text-slate-700">Publier de maniere anonyme</span>
                    </label>
                </div>

                {{-- Error --}}
                <div x-show="error" x-cloak class="mx-6 mt-4 p-4 bg-red-50 border border-red-200 rounded-xl">
                    <p class="text-sm font-medium text-red-700 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span x-text="error"></span>
                    </p>
                </div>

                {{-- Success --}}
                <div x-show="success" x-cloak class="mx-6 mt-4 p-5 bg-green-50 border border-green-200 rounded-xl text-center">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <p class="font-bold text-green-800">Merci pour votre avis !</p>
                    <p class="text-sm text-green-600 mt-1">Il sera publie apres moderation.</p>
                </div>

                {{-- Submit --}}
                <div class="p-6">
                    <button type="submit" :disabled="loading || success"
                            class="w-full flex items-center justify-center gap-2 py-4 px-6 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-base rounded-2xl shadow-lg shadow-indigo-500/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <template x-if="loading">
                            <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <span x-text="loading ? 'Envoi en cours...' : 'Envoyer mon avis'"></span>
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-6 text-center">
            <a href="{{ route('reviews.public', $tenant->slug) }}" class="text-indigo-400 hover:text-indigo-300 font-semibold text-sm transition-colors">Voir tous les avis →</a>
        </div>
    </div>
</div>

<script>
function reviewForm() {
    return {
        form: { customer_name: '', customer_email: '', food_rating: 0, service_rating: 0, ambiance_rating: 0, comment: '', is_anonymous: false },
        loading: false, error: '', success: false,

        async submitForm() {
            if (this.form.food_rating === 0 || this.form.service_rating === 0 || this.form.ambiance_rating === 0) {
                this.error = 'Veuillez donner une note pour chaque categorie';
                return;
            }
            this.loading = true;
            this.error = '';
            try {
                const res = await fetch('{{ route("review.store", $tenant->slug) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify(this.form)
                });
                const data = await res.json();
                if (data.success) {
                    this.success = true;
                    this.form = { customer_name: '', customer_email: '', food_rating: 0, service_rating: 0, ambiance_rating: 0, comment: '', is_anonymous: false };
                } else {
                    this.error = data.message || 'Une erreur est survenue';
                }
            } catch (e) { this.error = 'Erreur de connexion. Veuillez reessayer.'; }
            finally { this.loading = false; }
        }
    }
}
</script>
@endsection
