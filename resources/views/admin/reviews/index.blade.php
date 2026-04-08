@extends('layouts.admin')

@section('title', 'Avis clients')

@section('content')
<div x-data="reviewsManager()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Avis clients</h1>
            <p class="mt-1 text-sm text-gray-500">Gérez les avis et répondez à vos clients</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('review.form', $tenant->slug) }}" target="_blank"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                </svg>
                Voir le formulaire public
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="p-3 rounded-full bg-yellow-100">
                        <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Note moyenne</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($statistics['overall_average'] ?? 0, 1) }}/5</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="p-3 rounded-full bg-blue-100">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total avis</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $statistics['total'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="p-3 rounded-full bg-yellow-100">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">En attente</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $statistics['pending'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="p-3 rounded-full bg-green-100">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Publiés</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $statistics['published'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews List -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Tous les avis</h3>
        </div>

        @if($reviews->isEmpty())
            <div class="p-12 text-center text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">Aucun avis</h3>
                <p class="mt-2 text-gray-500">Partagez le lien du formulaire d'avis avec vos clients</p>
            </div>
        @else
            <div class="divide-y divide-gray-200">
                @foreach($reviews as $review)
                    <div class="p-6" x-data="{ showResponse: false }">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center">
                                        <span class="text-primary-600 font-medium">
                                            {{ strtoupper(substr($review->display_name, 0, 1)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="flex items-center">
                                        <p class="text-sm font-medium text-gray-900">{{ $review->display_name }}</p>
                                        @if($review->is_anonymous)
                                            <span class="ml-2 text-xs text-gray-500">({{ $review->customer_name }})</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500">{{ $review->created_at->format('d/m/Y à H:i') }}</p>

                                    <!-- Ratings -->
                                    <div class="mt-2 flex items-center space-x-4">
                                        <div class="flex items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-4 h-4 {{ $i <= $review->overall_rating ? 'text-yellow-400' : 'text-gray-300' }}"
                                                     fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                </svg>
                                            @endfor
                                        </div>
                                        <span class="text-xs text-gray-500">
                                            C: {{ $review->food_rating }} | S: {{ $review->service_rating }} | A: {{ $review->ambiance_rating }}
                                        </span>
                                    </div>

                                    @if($review->comment)
                                        <p class="mt-2 text-sm text-gray-700">{{ $review->comment }}</p>
                                    @endif

                                    @if($review->response)
                                        <div class="mt-3 bg-gray-50 rounded-md p-3">
                                            <p class="text-xs font-medium text-gray-500">Votre réponse:</p>
                                            <p class="text-sm text-gray-700">{{ $review->response }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center space-x-2">
                                <!-- Status Badge -->
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($review->status === 'PENDING') bg-yellow-100 text-yellow-800
                                    @elseif($review->status === 'PUBLISHED') bg-green-100 text-green-800
                                    @else bg-red-100 text-red-800 @endif">
                                    @if($review->status === 'PENDING') En attente
                                    @elseif($review->status === 'PUBLISHED') Publié
                                    @else Rejeté @endif
                                </span>

                                @if($review->is_featured)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                                        Mis en avant
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-4 flex items-center space-x-3">
                            @if($review->status === 'PENDING')
                                <button @click="approveReview({{ $review->id }})"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Approuver
                                </button>
                                <button @click="rejectReview({{ $review->id }})"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Rejeter
                                </button>
                            @endif

                            @if(!$review->response)
                                <button @click="showResponse = !showResponse"
                                        class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                    </svg>
                                    Répondre
                                </button>
                            @endif

                            <button @click="deleteReview({{ $review->id }})"
                                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded text-red-700 bg-white hover:bg-red-50">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Supprimer
                            </button>
                        </div>

                        <!-- Response Form -->
                        <div x-show="showResponse" x-cloak class="mt-4">
                            <form @submit.prevent="submitResponse({{ $review->id }}, $refs.response{{ $review->id }}.value)">
                                <textarea x-ref="response{{ $review->id }}" rows="3"
                                          placeholder="Écrivez votre réponse..."
                                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"></textarea>
                                <div class="mt-2 flex justify-end space-x-2">
                                    <button type="button" @click="showResponse = false"
                                            class="px-3 py-1.5 text-xs font-medium text-gray-700 hover:text-gray-900">
                                        Annuler
                                    </button>
                                    <button type="submit"
                                            class="px-3 py-1.5 bg-primary-600 text-white text-xs font-medium rounded hover:bg-primary-700">
                                        Envoyer
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function reviewsManager() {
    return {
        async approveReview(id) {
            await this.moderateReview(id, 'approve');
        },

        async rejectReview(id) {
            await this.moderateReview(id, 'reject');
        },

        async moderateReview(id, action) {
            try {
                const response = await fetch(`{{ url('/admin/' . $tenant->slug . '/reviews') }}/${id}/${action}`, {
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
        },

        async submitResponse(id, response) {
            if (!response.trim()) {
                alert('Veuillez écrire une réponse');
                return;
            }

            try {
                const res = await fetch(`{{ url('/admin/' . $tenant->slug . '/reviews') }}/${id}/respond`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ response })
                });

                const data = await res.json();

                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Une erreur est survenue');
                }
            } catch (e) {
                alert('Erreur de connexion');
            }
        },

        async deleteReview(id) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet avis ?')) return;

            try {
                const response = await fetch(`{{ url('/admin/' . $tenant->slug . '/reviews') }}/${id}`, {
                    method: 'DELETE',
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
