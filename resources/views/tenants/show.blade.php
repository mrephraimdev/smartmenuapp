@extends('layouts.superadmin')

@section('title', $tenant->name)
@section('page-title', $tenant->name)
@section('breadcrumb')
    <a href="{{ route('superadmin.tenants.index') }}" class="text-violet-600 hover:underline">Restaurants</a>
    <span class="mx-1 text-gray-300">/</span>
    <span class="text-gray-500">{{ $tenant->name }}</span>
@endsection

@section('content')
    {{-- Header actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Details du Restaurant</h2>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('superadmin.tenants.edit', $tenant) }}"
               class="inline-flex items-center gap-2 bg-violet-600 text-white px-5 py-2.5 rounded-xl hover:bg-violet-700 transition-colors font-semibold text-sm shadow-sm">
                <x-heroicon-o-pencil class="w-4 h-4" />
                Modifier
            </a>
            <a href="{{ route('superadmin.tenants.index') }}"
               class="inline-flex items-center gap-2 bg-gray-100 text-gray-600 px-5 py-2.5 rounded-xl hover:bg-gray-200 transition-colors font-semibold text-sm">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
                Retour
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Informations principales --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-8">
                <h3 class="text-xl font-bold text-gray-900 mb-6">Informations Generales</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8">
                    <div>
                        <label class="block text-sm font-semibold text-gray-400 uppercase tracking-wide mb-1">Nom</label>
                        <p class="text-base font-medium text-gray-900">{{ $tenant->name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-400 uppercase tracking-wide mb-1">Slug</label>
                        <code class="text-base text-gray-700 bg-gray-100 px-2.5 py-0.5 rounded-lg">{{ $tenant->slug }}</code>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-400 uppercase tracking-wide mb-1">Type</label>
                        <span class="inline-flex px-3 py-1 text-sm font-bold rounded-full
                            {{ $tenant->type === 'restaurant' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800' }}">
                            {{ ucfirst($tenant->type) }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-400 uppercase tracking-wide mb-1">Devise</label>
                        <p class="text-base font-medium text-gray-900">{{ $tenant->currency }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-400 uppercase tracking-wide mb-1">Langue</label>
                        <p class="text-base font-medium text-gray-900">{{ $tenant->locale === 'fr' ? 'Francais' : 'English' }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-400 uppercase tracking-wide mb-1">Statut</label>
                        <span class="inline-flex px-3 py-1 text-sm font-bold rounded-full
                            {{ $tenant->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $tenant->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-400 uppercase tracking-wide mb-1">Cree le</label>
                        <p class="text-base font-medium text-gray-900">{{ $tenant->created_at->format('d/m/Y H:i') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-400 uppercase tracking-wide mb-1">Modifie le</label>
                        <p class="text-base font-medium text-gray-900">{{ $tenant->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>

            {{-- Statistiques --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-8">
                <h3 class="text-xl font-bold text-gray-900 mb-6">Statistiques</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center bg-blue-50 rounded-xl p-5">
                        <div class="text-3xl font-extrabold text-blue-600">{{ $tenant->users()->count() }}</div>
                        <div class="text-sm font-semibold text-blue-600/70 mt-1">Utilisateurs</div>
                    </div>
                    <div class="text-center bg-emerald-50 rounded-xl p-5">
                        <div class="text-3xl font-extrabold text-emerald-600">{{ $tenant->menus()->count() }}</div>
                        <div class="text-sm font-semibold text-emerald-600/70 mt-1">Menus</div>
                    </div>
                    <div class="text-center bg-purple-50 rounded-xl p-5">
                        <div class="text-3xl font-extrabold text-purple-600">{{ $tenant->orders()->count() }}</div>
                        <div class="text-sm font-semibold text-purple-600/70 mt-1">Commandes</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Actions rapides --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-5">Actions Rapides</h3>

                <div class="space-y-3">
                    <a href="{{ route('admin.dashboard', $tenant->slug) }}"
                       class="w-full flex items-center justify-center gap-2 bg-emerald-600 text-white px-4 py-3 rounded-xl hover:bg-emerald-700 transition-colors font-semibold text-sm">
                        <x-heroicon-o-cog-6-tooth class="w-5 h-5" />
                        Administration
                    </a>

                    <a href="{{ route('admin.menus', $tenant->slug) }}"
                       class="w-full flex items-center justify-center gap-2 bg-blue-600 text-white px-4 py-3 rounded-xl hover:bg-blue-700 transition-colors font-semibold text-sm">
                        <x-heroicon-o-clipboard-document-list class="w-5 h-5" />
                        Gerer les Menus
                    </a>

                    <a href="{{ route('superadmin.users.index') }}?tenant={{ $tenant->id }}"
                       class="w-full flex items-center justify-center gap-2 bg-purple-600 text-white px-4 py-3 rounded-xl hover:bg-purple-700 transition-colors font-semibold text-sm">
                        <x-heroicon-o-users class="w-5 h-5" />
                        Gerer les Utilisateurs
                    </a>

                    <a href="/menu?tenant={{ $tenant->id }}&table=1"
                       class="w-full flex items-center justify-center gap-2 bg-orange-500 text-white px-4 py-3 rounded-xl hover:bg-orange-600 transition-colors font-semibold text-sm">
                        <x-heroicon-o-eye class="w-5 h-5" />
                        Voir le Menu Client
                    </a>
                </div>
            </div>

            {{-- Utilisateurs recents --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-5">Utilisateurs Recents</h3>

                <div class="space-y-3">
                    @forelse($tenant->users()->latest()->limit(5)->get() as $user)
                    <div class="flex items-center justify-between py-1">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">{{ $user->name }}</div>
                            <div class="text-xs text-gray-400">{{ $user->email }}</div>
                        </div>
                        <span class="inline-flex px-2.5 py-1 text-xs font-bold rounded-full bg-gray-100 text-gray-600">
                            {{ $user->role ?? 'N/A' }}
                        </span>
                    </div>
                    @empty
                    <p class="text-gray-400 text-sm">Aucun utilisateur</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
