@extends('layouts.superadmin')

@section('title', 'Vue d\'ensemble')
@section('page-title', 'Vue d\'ensemble')

@section('content')
{{-- Stats globales --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"/>
                </svg>
            </div>
            <a href="{{ route('superadmin.tenants.create') }}" class="text-blue-400 hover:text-blue-600 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
            </a>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_tenants'] }}</p>
        <p class="text-sm text-gray-500 mt-0.5">Restaurants</p>
        @if($stats['active_tenants'] > 0)
        <p class="text-xs text-emerald-600 font-medium mt-1">{{ $stats['active_tenants'] }} actif(s)</p>
        @endif
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
                </svg>
            </div>
            <a href="{{ route('superadmin.users.create') }}" class="text-emerald-400 hover:text-emerald-600 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
            </a>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_users'] }}</p>
        <p class="text-sm text-gray-500 mt-0.5">Utilisateurs</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_orders'] ?? 0) }}</p>
        <p class="text-sm text-gray-500 mt-0.5">Commandes totales</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 bg-violet-100 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_revenue'] ?? 0, 0, ',', ' ') }} F</p>
        <p class="text-sm text-gray-500 mt-0.5">Revenus totaux</p>
    </div>
</div>

{{-- Actions rapides --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
    <a href="{{ route('superadmin.tenants.create') }}"
       class="flex items-center gap-3 bg-white border border-gray-100 rounded-2xl px-4 py-3.5 hover:border-blue-200 hover:shadow-md transition-all group">
        <div class="w-9 h-9 bg-blue-100 group-hover:bg-blue-200 rounded-xl flex items-center justify-center transition-colors flex-shrink-0">
            <svg class="w-4.5 h-4.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
        </div>
        <span class="text-sm font-semibold text-gray-700 group-hover:text-blue-700 transition-colors">Nouveau Restaurant</span>
    </a>

    <a href="{{ route('superadmin.users.create') }}"
       class="flex items-center gap-3 bg-white border border-gray-100 rounded-2xl px-4 py-3.5 hover:border-emerald-200 hover:shadow-md transition-all group">
        <div class="w-9 h-9 bg-emerald-100 group-hover:bg-emerald-200 rounded-xl flex items-center justify-center transition-colors flex-shrink-0">
            <svg class="w-4.5 h-4.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z"/>
            </svg>
        </div>
        <span class="text-sm font-semibold text-gray-700 group-hover:text-emerald-700 transition-colors">Nouvel Utilisateur</span>
    </a>

    <a href="{{ route('superadmin.tenants.index') }}"
       class="flex items-center gap-3 bg-white border border-gray-100 rounded-2xl px-4 py-3.5 hover:border-indigo-200 hover:shadow-md transition-all group">
        <div class="w-9 h-9 bg-indigo-100 group-hover:bg-indigo-200 rounded-xl flex items-center justify-center transition-colors flex-shrink-0">
            <svg class="w-4.5 h-4.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z"/>
            </svg>
        </div>
        <span class="text-sm font-semibold text-gray-700 group-hover:text-indigo-700 transition-colors">Tous les Restaurants</span>
    </a>

    <a href="{{ route('superadmin.users.index') }}"
       class="flex items-center gap-3 bg-white border border-gray-100 rounded-2xl px-4 py-3.5 hover:border-violet-200 hover:shadow-md transition-all group">
        <div class="w-9 h-9 bg-violet-100 group-hover:bg-violet-200 rounded-xl flex items-center justify-center transition-colors flex-shrink-0">
            <svg class="w-4.5 h-4.5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
            </svg>
        </div>
        <span class="text-sm font-semibold text-gray-700 group-hover:text-violet-700 transition-colors">Tous les Utilisateurs</span>
    </a>
</div>

{{-- Grille principale --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
    {{-- Restaurants récents --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-bold text-gray-900 flex items-center gap-2">
                <svg class="w-4.5 h-4.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"/>
                </svg>
                Restaurants récents
            </h2>
            <a href="{{ route('superadmin.tenants.create') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition-colors flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Ajouter
            </a>
        </div>

        <div class="divide-y divide-gray-50">
            @forelse($recentTenants as $tenant)
            <div class="px-5 py-3.5 hover:bg-gray-50 transition-colors">
                <div class="flex items-center gap-3 mb-2.5">
                    <div class="w-9 h-9 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                        {{ strtoupper(substr($tenant->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-900 text-sm truncate">{{ $tenant->name }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ $tenant->slug }}</p>
                    </div>
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $tenant->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $tenant->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                </div>
                <div class="flex gap-1.5">
                    <a href="{{ route('admin.dashboard', $tenant->slug) }}"
                       class="flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Admin
                    </a>
                    <a href="{{ route('kds', $tenant->slug) }}"
                       class="flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-lg bg-orange-50 text-orange-700 hover:bg-orange-100 transition-colors">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z"/></svg>
                        Cuisine
                    </a>
                    <a href="/menu/{{ $tenant->id }}/A1" target="_blank"
                       class="flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-lg bg-gray-50 text-gray-600 hover:bg-gray-100 transition-colors">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                        Menu
                    </a>
                </div>
            </div>
            @empty
            <div class="py-12 text-center">
                <div class="w-12 h-12 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"/>
                    </svg>
                </div>
                <p class="text-sm text-gray-400 mb-3">Aucun restaurant pour l'instant</p>
                <a href="{{ route('superadmin.tenants.create') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition-colors">
                    + Créer le premier restaurant
                </a>
            </div>
            @endforelse
        </div>

        @if($recentTenants->count() > 0)
        <div class="px-5 py-3 bg-gray-50 border-t border-gray-100">
            <a href="{{ route('superadmin.tenants.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition-colors flex items-center gap-1">
                Voir tous les restaurants
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                </svg>
            </a>
        </div>
        @endif
    </div>

    {{-- Utilisateurs récents --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-bold text-gray-900 flex items-center gap-2">
                <svg class="w-4.5 h-4.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
                </svg>
                Utilisateurs récents
            </h2>
            <a href="{{ route('superadmin.users.create') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-800 transition-colors flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Ajouter
            </a>
        </div>

        <div class="divide-y divide-gray-50">
            @forelse($recentUsers as $user)
            <div class="px-5 py-3.5 hover:bg-gray-50 transition-colors">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-900 text-sm truncate">{{ $user->name }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ $user->email }}</p>
                        </div>
                    </div>
                    <div class="text-right ml-3 flex-shrink-0">
                        @if($user->role)
                            @php $roleEnum = \App\Enums\UserRole::tryFrom($user->role) @endphp
                            <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $roleEnum ? $roleEnum->badgeClass() : 'bg-gray-100 text-gray-600' }}">
                                {{ $roleEnum ? $roleEnum->label() : $user->role }}
                            </span>
                        @endif
                        @if($user->tenant)
                            <p class="text-xs text-gray-400 mt-0.5 truncate max-w-[100px]">{{ $user->tenant->name }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="py-12 text-center">
                <div class="w-12 h-12 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
                    </svg>
                </div>
                <p class="text-sm text-gray-400">Aucun utilisateur</p>
            </div>
            @endforelse
        </div>

        @if($recentUsers->count() > 0)
        <div class="px-5 py-3 bg-gray-50 border-t border-gray-100">
            <a href="{{ route('superadmin.users.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-800 transition-colors flex items-center gap-1">
                Voir tous les utilisateurs
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                </svg>
            </a>
        </div>
        @endif
    </div>
</div>

{{-- Accès rapide tous restaurants --}}
@php $allTenants = \App\Models\Tenant::latest()->take(6)->get(); @endphp
@if($allTenants->count() > 0)
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100">
        <h2 class="font-bold text-gray-900 flex items-center gap-2">
            <svg class="w-4.5 h-4.5 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/>
            </svg>
            Accès rapide — tous les restaurants
        </h2>
    </div>
    <div class="p-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($allTenants as $t)
        <div class="border border-gray-100 rounded-xl p-4 hover:border-violet-200 hover:shadow-sm transition-all">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 bg-gradient-to-br from-violet-500 to-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                    {{ strtoupper(substr($t->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900 text-sm truncate">{{ $t->name }}</p>
                    <p class="text-xs text-gray-400">{{ $t->slug }}</p>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-1.5">
                <a href="{{ route('admin.dashboard', $t->slug) }}" class="flex flex-col items-center p-2 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-600 transition-colors">
                    <svg class="w-4 h-4 mb-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span class="text-[10px] font-semibold">Admin</span>
                </a>
                <a href="{{ route('kds', $t->slug) }}" class="flex flex-col items-center p-2 rounded-lg bg-orange-50 hover:bg-orange-100 text-orange-600 transition-colors">
                    <svg class="w-4 h-4 mb-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z"/></svg>
                    <span class="text-[10px] font-semibold">Cuisine</span>
                </a>
                <a href="{{ route('admin.payments.index', $t->slug) }}" class="flex flex-col items-center p-2 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-600 transition-colors">
                    <svg class="w-4 h-4 mb-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <span class="text-[10px] font-semibold">Paiements</span>
                </a>
                <a href="{{ route('admin.menus', $t->slug) }}" class="flex flex-col items-center p-2 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 transition-colors">
                    <svg class="w-4 h-4 mb-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/></svg>
                    <span class="text-[10px] font-semibold">Menu</span>
                </a>
                <a href="{{ route('admin.statistics', $t->slug) }}" class="flex flex-col items-center p-2 rounded-lg bg-violet-50 hover:bg-violet-100 text-violet-600 transition-colors">
                    <svg class="w-4 h-4 mb-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                    <span class="text-[10px] font-semibold">Stats</span>
                </a>
                <a href="/menu/{{ $t->id }}/A1" target="_blank" class="flex flex-col items-center p-2 rounded-lg bg-gray-50 hover:bg-gray-100 text-gray-600 transition-colors">
                    <svg class="w-4 h-4 mb-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                    <span class="text-[10px] font-semibold">Voir</span>
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection
