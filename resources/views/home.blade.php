@extends('layouts.admin')

@section('title', 'Tableau de Bord')
@section('page-title', 'Tableau de Bord')
@section('breadcrumb')
    <span class="text-gray-500">Vue d'ensemble</span>
@endsection

@section('content')
    {{-- Statistiques rapides --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 bg-blue-100 rounded-xl flex items-center justify-center">
                    <x-heroicon-o-clipboard-document-list class="w-6 h-6 text-blue-600" />
                </div>
                <span class="text-xs font-semibold text-gray-400 bg-blue-50 px-2.5 py-1 rounded-full">Aujourd'hui</span>
            </div>
            <h3 class="text-sm font-semibold text-gray-500 mb-1">Commandes</h3>
            <p class="text-3xl font-extrabold text-gray-900">0</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <x-heroicon-o-banknotes class="w-6 h-6 text-emerald-600" />
                </div>
                <span class="text-xs font-semibold text-gray-400 bg-emerald-50 px-2.5 py-1 rounded-full">Ce mois</span>
            </div>
            <h3 class="text-sm font-semibold text-gray-500 mb-1">Chiffre d'affaires</h3>
            <p class="text-3xl font-extrabold text-gray-900">0 FCFA</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 bg-purple-100 rounded-xl flex items-center justify-center">
                    <x-heroicon-o-cake class="w-6 h-6 text-purple-600" />
                </div>
                <span class="text-xs font-semibold text-gray-400 bg-purple-50 px-2.5 py-1 rounded-full">Menu</span>
            </div>
            <h3 class="text-sm font-semibold text-gray-500 mb-1">Plats actifs</h3>
            <p class="text-3xl font-extrabold text-gray-900">0</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 bg-orange-100 rounded-xl flex items-center justify-center">
                    <x-heroicon-o-table-cells class="w-6 h-6 text-orange-600" />
                </div>
                <span class="text-xs font-semibold text-gray-400 bg-orange-50 px-2.5 py-1 rounded-full">Actives</span>
            </div>
            <h3 class="text-sm font-semibold text-gray-500 mb-1">Tables</h3>
            <p class="text-3xl font-extrabold text-gray-900">0</p>
        </div>
    </div>

    {{-- Actions rapides + Profil --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                <x-heroicon-o-bolt class="w-6 h-6 text-amber-500" />
                Actions Rapides
            </h2>

            <div class="grid sm:grid-cols-3 gap-4">
                <a href="/admin" class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl p-5 hover:from-blue-600 hover:to-blue-700 transition-all text-center shadow-sm">
                    <x-heroicon-o-cog-6-tooth class="w-8 h-8 mx-auto mb-3" />
                    <p class="font-semibold text-base">Administration</p>
                    <p class="text-xs text-blue-100 mt-1">Gerer les menus</p>
                </a>

                @if(auth()->user()->tenant)
                <a href="/kds/{{ auth()->user()->tenant->slug }}" class="bg-gradient-to-br from-emerald-500 to-emerald-600 text-white rounded-xl p-5 hover:from-emerald-600 hover:to-emerald-700 transition-all text-center shadow-sm">
                    <x-heroicon-o-fire class="w-8 h-8 mx-auto mb-3" />
                    <p class="font-semibold text-base">Espace Cuisine</p>
                    <p class="text-xs text-emerald-100 mt-1">Voir les commandes</p>
                </a>
                @endif

                <a href="/menu?tenant=1&table=A1" class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl p-5 hover:from-purple-600 hover:to-purple-700 transition-all text-center shadow-sm">
                    <x-heroicon-o-device-phone-mobile class="w-8 h-8 mx-auto mb-3" />
                    <p class="font-semibold text-base">Apercu Menu</p>
                    <p class="text-xs text-purple-100 mt-1">Vue client</p>
                </a>
            </div>
        </div>

        {{-- Profil --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                <x-heroicon-o-user-circle class="w-6 h-6 text-purple-500" />
                Mon Profil
            </h2>

            <div class="space-y-4">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Nom</p>
                    <p class="text-base font-medium text-gray-900">{{ auth()->user()->name }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Email</p>
                    <p class="text-base font-medium text-gray-900">{{ auth()->user()->email }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Role</p>
                    @if(auth()->user()->role)
                        @php $roleEnum = \App\Enums\UserRole::tryFrom(auth()->user()->role) @endphp
                        <span class="inline-flex px-3 py-1 text-xs font-bold rounded-full {{ $roleEnum ? $roleEnum->badgeClass() : 'bg-gray-100 text-gray-800' }}">
                            {{ $roleEnum ? $roleEnum->label() : auth()->user()->role }}
                        </span>
                    @else
                        <span class="text-sm text-gray-400">Aucun role</span>
                    @endif
                </div>

                @if(auth()->user()->tenant)
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Restaurant</p>
                    <p class="text-base font-medium text-gray-900">{{ auth()->user()->tenant->name }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Activite recente --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
            <x-heroicon-o-clock class="w-6 h-6 text-blue-500" />
            Activite Recente
        </h2>

        <div class="text-center py-12">
            <x-heroicon-o-clock class="w-12 h-12 mx-auto text-gray-200 mb-3" />
            <p class="text-base font-medium text-gray-400">Aucune activite recente</p>
            <p class="text-sm text-gray-300 mt-1">Les dernieres actions s'afficheront ici</p>
        </div>
    </div>
@endsection
