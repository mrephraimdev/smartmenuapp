@extends('layouts.superadmin')

@section('title', 'Restaurants')
@section('page-title', 'Restaurants')
@section('breadcrumb')
    <span class="text-gray-500">Restaurants</span>
@endsection

@section('content')
    {{-- Header Row --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Liste des Restaurants</h2>
            <p class="text-sm text-gray-500 mt-1">Tous les restaurants et etablissements enregistres</p>
        </div>
        <a href="{{ route('superadmin.tenants.create') }}"
           class="inline-flex items-center gap-2 bg-violet-600 text-white px-5 py-2.5 rounded-xl hover:bg-violet-700 transition-colors font-semibold text-sm shadow-sm">
            <x-heroicon-o-plus class="w-5 h-5" />
            Nouveau Restaurant
        </a>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-5 py-3.5 rounded-xl mb-6 text-base font-medium">
            {{ session('success') }}
        </div>
    @endif

    {{-- Table Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nom</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Slug</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Devise</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    @forelse($tenants as $tenant)
                    <tr class="hover:bg-violet-50/40 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-base font-semibold text-gray-900">{{ $tenant->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <code class="text-sm text-gray-500 bg-gray-100 px-2 py-0.5 rounded-lg">{{ $tenant->slug }}</code>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2.5 py-1 text-xs font-bold rounded-full
                                {{ $tenant->type === 'restaurant' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800' }}">
                                {{ ucfirst($tenant->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-600">
                            {{ $tenant->currency }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2.5 py-1 text-xs font-bold rounded-full
                                {{ $tenant->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $tenant->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('superadmin.tenants.show', $tenant) }}"
                                   class="p-2 text-gray-400 hover:text-violet-600 hover:bg-violet-50 rounded-lg transition-colors" title="Voir">
                                    <x-heroicon-o-eye class="w-5 h-5" />
                                </a>
                                <a href="{{ route('superadmin.tenants.edit', $tenant) }}"
                                   class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Modifier">
                                    <x-heroicon-o-pencil class="w-5 h-5" />
                                </a>
                                <a href="{{ route('admin.dashboard', $tenant->slug) }}"
                                   class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Administration">
                                    <x-heroicon-o-cog-6-tooth class="w-5 h-5" />
                                </a>
                                <form action="{{ route('superadmin.tenants.destroy', $tenant) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Etes-vous sur de vouloir supprimer ce tenant ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Supprimer">
                                        <x-heroicon-o-trash class="w-5 h-5" />
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="text-gray-400">
                                <x-heroicon-o-building-office class="w-10 h-10 mx-auto mb-2" />
                                <p class="text-base font-medium">Aucun restaurant trouve.</p>
                                <p class="text-sm mt-1">Commencez par creer votre premier restaurant.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
