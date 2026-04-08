@extends('layouts.admin')

@section('title', 'Point de Vente (POS)')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Point de Vente (POS)</h1>
                <p class="text-gray-600">Gérez vos sessions de caisse et transactions</p>
            </div>
            <a href="{{ route('admin.pos.sessions', $tenantSlug) }}"
               class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Historique des sessions
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
            {{ session('error') }}
        </div>
    @endif

    @if($currentSession)
        <!-- Active Session -->
        <div class="bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-300 rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <div class="bg-green-500 text-white rounded-full p-3 mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Session Active</h2>
                        <p class="text-gray-600">{{ $currentSession->session_number }}</p>
                        <p class="text-sm text-gray-500">Ouverte le {{ $currentSession->opened_at->format('d/m/Y à H:i') }}</p>
                    </div>
                </div>
                <span class="bg-green-500 text-white px-4 py-2 rounded-full font-semibold text-sm">
                    OUVERTE
                </span>
            </div>

            <!-- Session Info Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg p-4 shadow">
                    <div class="text-sm text-gray-600 mb-1">Fond de caisse</div>
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($currentSession->opening_float, 0, ',', ' ') }} FCFA</div>
                </div>
                <div class="bg-white rounded-lg p-4 shadow">
                    <div class="text-sm text-gray-600 mb-1">Durée</div>
                    <div class="text-2xl font-bold text-gray-900" id="sessionDuration">
                        {{ $currentSession->getDurationInMinutes() }} min
                    </div>
                </div>
                <div class="bg-white rounded-lg p-4 shadow">
                    <div class="text-sm text-gray-600 mb-1">Commandes</div>
                    <div class="text-2xl font-bold text-gray-900" id="orderCount">
                        {{ $currentSession->orders()->count() }}
                    </div>
                </div>
                <div class="bg-white rounded-lg p-4 shadow">
                    <div class="text-sm text-gray-600 mb-1">Ventes</div>
                    <div class="text-2xl font-bold text-green-600" id="totalSales">
                        {{ number_format($currentSession->orders()->whereNotIn('status', ['ANNULE'])->sum('total'), 0, ',', ' ') }} FCFA
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3">
                <a href="{{ route('admin.pos.x-report', [$tenantSlug, $currentSession->id]) }}"
                   class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition-colors text-center">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Rapport X (Intermédiaire)
                </a>
                <button onclick="openCloseSessionModal()"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-3 px-4 rounded-lg transition-colors">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Fermer la Session
                </button>
            </div>

            @if($currentSession->opening_notes)
                <div class="mt-4 bg-white rounded-lg p-4">
                    <p class="text-sm text-gray-600 font-semibold mb-1">Notes d'ouverture:</p>
                    <p class="text-gray-700">{{ $currentSession->opening_notes }}</p>
                </div>
            @endif
        </div>
    @else
        <!-- No Active Session -->
        <div class="bg-white border-2 border-dashed border-gray-300 rounded-lg p-12 text-center">
            <div class="bg-gray-100 rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Aucune session active</h3>
            <p class="text-gray-600 mb-6">Ouvrez une nouvelle session de caisse pour commencer</p>
            <button onclick="openNewSessionModal()"
                    class="bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-6 rounded-lg transition-colors inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Ouvrir une Session
            </button>
        </div>
    @endif
</div>

<!-- Open Session Modal -->
<div id="newSessionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <h3 class="text-2xl font-bold text-gray-900 mb-6">Nouvelle Session</h3>
        <form id="newSessionForm" onsubmit="openSession(event)">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Fond de caisse initial (FCFA)</label>
                <input type="number" name="opening_float" step="0.01" min="0" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes (optionnel)</label>
                <textarea name="opening_notes" rows="3"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                          placeholder="Notes sur l'ouverture de la session..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeNewSessionModal()"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-3 px-4 rounded-lg transition-colors">
                    Annuler
                </button>
                <button type="submit"
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-lg transition-colors">
                    Ouvrir
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Close Session Modal -->
<div id="closeSessionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <h3 class="text-2xl font-bold text-gray-900 mb-6">Fermer la Session</h3>
        <form id="closeSessionForm" onsubmit="closeSession(event)">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Montant en caisse (FCFA)</label>
                <input type="number" name="actual_cash" step="0.01" min="0" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                <p class="text-sm text-gray-500 mt-1">Comptez l'argent dans la caisse et entrez le montant total</p>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes (optionnel)</label>
                <textarea name="closing_notes" rows="3"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                          placeholder="Notes sur la fermeture de la session..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeCloseSessionModal()"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-3 px-4 rounded-lg transition-colors">
                    Annuler
                </button>
                <button type="submit"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-3 px-4 rounded-lg transition-colors">
                    Fermer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const tenantSlug = '{{ $tenantSlug }}';
const currentSessionId = {{ $currentSession ? $currentSession->id : 'null' }};

function openNewSessionModal() {
    document.getElementById('newSessionModal').classList.remove('hidden');
    document.getElementById('newSessionModal').classList.add('flex');
}

function closeNewSessionModal() {
    document.getElementById('newSessionModal').classList.add('hidden');
    document.getElementById('newSessionModal').classList.remove('flex');
}

function openCloseSessionModal() {
    document.getElementById('closeSessionModal').classList.remove('hidden');
    document.getElementById('closeSessionModal').classList.add('flex');
}

function closeCloseSessionModal() {
    document.getElementById('closeSessionModal').classList.add('hidden');
    document.getElementById('closeSessionModal').classList.remove('flex');
}

async function openSession(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    try {
        const response = await fetch(`/admin/${tenantSlug}/pos/sessions/open`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                opening_float: parseFloat(formData.get('opening_float')),
                opening_notes: formData.get('opening_notes')
            })
        });

        const data = await response.json();

        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Erreur lors de l\'ouverture de la session');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Erreur lors de l\'ouverture de la session');
    }
}

async function closeSession(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    if (!confirm('Êtes-vous sûr de vouloir fermer cette session ? Cette action est irréversible.')) {
        return;
    }

    try {
        const response = await fetch(`/admin/${tenantSlug}/pos/sessions/${currentSessionId}/close`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                actual_cash: parseFloat(formData.get('actual_cash')),
                closing_notes: formData.get('closing_notes')
            })
        });

        const data = await response.json();

        if (data.success) {
            // Redirect to Z report
            window.location.href = `/admin/${tenantSlug}/pos/sessions/${currentSessionId}/z-report`;
        } else {
            alert(data.message || 'Erreur lors de la fermeture de la session');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Erreur lors de la fermeture de la session');
    }
}

// Update session duration every minute
@if($currentSession)
setInterval(() => {
    const openedAt = new Date('{{ $currentSession->opened_at->toIso8601String() }}');
    const now = new Date();
    const diffMinutes = Math.floor((now - openedAt) / 1000 / 60);
    document.getElementById('sessionDuration').textContent = diffMinutes + ' min';
}, 60000);
@endif
</script>
@endsection
