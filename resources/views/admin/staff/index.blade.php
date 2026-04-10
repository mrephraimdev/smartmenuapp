@extends('layouts.admin')

@section('title', 'Personnel')
@section('page-title', 'Gestion du Personnel')
@section('breadcrumb')
    <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="hover:text-amber-500">Dashboard</a>
    <span class="mx-2">/</span>
    <span>Personnel</span>
@endsection

@section('content')
    {{-- Messages --}}
    @if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 flex items-center">
        <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" />
        <p class="text-green-800">{{ session('success') }}</p>
    </div>
    @endif

    @if($errors->any())
    <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4">
        <div class="flex items-center mb-2">
            <x-heroicon-o-exclamation-circle class="w-5 h-5 text-red-500 mr-3 flex-shrink-0" />
            <p class="text-red-800 font-medium">Erreur</p>
        </div>
        <ul class="list-disc list-inside text-red-700 text-sm">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Header action --}}
    <div class="flex items-center justify-between mb-6">
        <span class="text-sm text-gray-500">{{ $staff->total() }} membre(s)</span>
        <a href="{{ route('admin.staff.create', $tenant->slug) }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-500 text-white text-sm font-semibold rounded-xl hover:bg-amber-600 transition shadow-sm">
            <x-heroicon-o-plus class="w-4 h-4" />
            Ajouter
        </a>
    </div>

    {{-- Staff list --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-800 flex items-center">
                <x-heroicon-o-users class="w-5 h-5 mr-2 text-amber-500" />
                Personnel du Restaurant
            </h2>
        </div>

        @if($staff->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nom</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Identifiant</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Cree le</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($staff as $member)
                    <tr class="hover:bg-amber-50/40 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gradient-to-br from-amber-400 to-amber-600 rounded-full flex items-center justify-center text-white font-bold mr-3">
                                    {{ strtoupper(substr($member->name, 0, 1)) }}
                                </div>
                                <div class="text-sm font-medium text-gray-900">{{ $member->name }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500 font-mono">{{ $member->username ?? '—' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php $roleEnum = \App\Enums\UserRole::tryFrom($member->role) @endphp
                            <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full {{ $roleEnum ? $roleEnum->badgeClass() : 'bg-gray-100 text-gray-800' }}">
                                {{ $roleEnum ? $roleEnum->label() : $member->role }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $member->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.staff.edit', [$tenant->slug, $member]) }}"
                                   class="text-amber-500 hover:text-amber-700 transition-colors" title="Modifier">
                                    <x-heroicon-o-pencil class="w-5 h-5" />
                                </a>
                                <form method="POST" action="{{ route('admin.staff.destroy', [$tenant->slug, $member]) }}"
                                      class="inline-block"
                                      onsubmit="return confirm('Etes-vous sur de vouloir supprimer ce membre ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-600 transition-colors" title="Supprimer">
                                        <x-heroicon-o-trash class="w-5 h-5" />
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($staff->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $staff->links() }}
        </div>
        @endif
        @else
        <div class="p-12 text-center">
            <x-heroicon-o-users class="w-16 h-16 mx-auto text-gray-300 mb-4" />
            <h3 class="text-lg font-medium text-gray-800 mb-2">Aucun personnel</h3>
            <p class="text-gray-500 mb-6">Vous n'avez pas encore ajoute de membres du personnel.</p>
            <a href="{{ route('admin.staff.create', $tenant->slug) }}"
               class="inline-flex items-center px-5 py-2.5 bg-amber-500 text-white rounded-xl hover:bg-amber-600 transition font-semibold text-sm">
                <x-heroicon-o-plus class="w-5 h-5 mr-2" />
                Ajouter un membre
            </a>
        </div>
        @endif
    </div>

    {{-- Role legend --}}
    <div class="mt-8 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-base font-semibold text-gray-800 mb-4 flex items-center">
            <x-heroicon-o-information-circle class="w-5 h-5 mr-2 text-amber-500" />
            Roles disponibles
        </h3>
        <div class="grid md:grid-cols-3 gap-4">
            <div class="p-4 bg-green-50 rounded-xl border border-green-200">
                <div class="flex items-center mb-2">
                    <x-heroicon-o-banknotes class="w-5 h-5 text-green-600 mr-2" />
                    <span class="font-medium text-green-800">Caissier(e)</span>
                </div>
                <p class="text-sm text-green-700">Acces a la caisse, paiements et commandes</p>
            </div>
            <div class="p-4 bg-orange-50 rounded-xl border border-orange-200">
                <div class="flex items-center mb-2">
                    <x-heroicon-o-fire class="w-5 h-5 text-orange-600 mr-2" />
                    <span class="font-medium text-orange-800">Chef Cuisinier</span>
                </div>
                <p class="text-sm text-orange-700">Acces a l'ecran cuisine (KDS) et preparation</p>
            </div>
            <div class="p-4 bg-blue-50 rounded-xl border border-blue-200">
                <div class="flex items-center mb-2">
                    <x-heroicon-o-user-group class="w-5 h-5 text-blue-600 mr-2" />
                    <span class="font-medium text-blue-800">Serveur</span>
                </div>
                <p class="text-sm text-blue-700">Acces aux commandes et service en salle</p>
            </div>
        </div>
    </div>
@endsection
