@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div x-data="{ showMenuModal: false, newMenu: { title: '', active: true } }">

    {{-- ── Filtre par période ─────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-3 mb-6 flex flex-wrap items-center gap-3">
        {{-- Presets --}}
        <div class="flex gap-1.5 flex-wrap">
            @foreach(['today' => "Aujourd'hui", 'yesterday' => 'Hier', 'week' => 'Semaine', 'month' => 'Mois'] as $p => $label)
            <a href="{{ route('admin.dashboard', array_merge([$tenant->slug], ['period' => $p])) }}"
               class="px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors
                      {{ $period === $p ? 'bg-amber-500 text-white border-amber-500' : 'bg-gray-50 text-gray-600 border-gray-200 hover:border-amber-300' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>

        <div class="h-5 w-px bg-gray-200 hidden sm:block"></div>

        {{-- Custom range --}}
        <form method="GET" action="{{ route('admin.dashboard', $tenant->slug) }}" class="flex items-center gap-2">
            <input type="hidden" name="period" value="custom">
            <input type="date" name="date_from" value="{{ $period === 'custom' ? $dateFrom : '' }}"
                   class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-amber-400">
            <span class="text-xs text-gray-400">→</span>
            <input type="date" name="date_to" value="{{ $period === 'custom' ? $dateTo : '' }}"
                   class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-amber-400">
            <button type="submit" class="px-3 py-1.5 text-xs font-semibold bg-slate-800 text-white rounded-lg hover:bg-slate-700 transition-colors">
                OK
            </button>
        </form>

        {{-- Label période active --}}
        <span class="ml-auto text-xs font-semibold text-amber-700 bg-amber-50 px-3 py-1.5 rounded-lg border border-amber-200">
            📅 {{ $periodLabel }}
        </span>
    </div>

    <!-- Stats Grid - Rendu côté serveur (pas d'AJAX) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Commandes</p>
                    <p class="text-3xl font-bold text-blue-600">{{ number_format($stats['totalOrders']) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <x-heroicon-o-shopping-cart class="w-6 h-6 text-blue-600" />
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Chiffre d'Affaires</p>
                    <p class="text-3xl font-bold text-green-600">{{ number_format($stats['totalRevenue'], 0, ',', ' ') }} F</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <x-heroicon-o-banknotes class="w-6 h-6 text-green-600" />
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Plats Actifs</p>
                    <p class="text-3xl font-bold text-purple-600">{{ $stats['activeDishes'] }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <x-heroicon-o-fire class="w-6 h-6 text-purple-600" />
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <a href="{{ route('admin.staff.index', $tenant->slug) }}"
           class="flex flex-col items-center justify-center p-5 bg-white rounded-xl border border-gray-200 hover:border-orange-300 hover:shadow-lg transition-all group">
            <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-orange-200 transition-colors">
                <x-heroicon-o-users class="w-6 h-6 text-orange-600" />
            </div>
            <span class="font-medium text-gray-700 group-hover:text-orange-600 transition-colors">Personnel</span>
        </a>

        <a href="{{ route('admin.tables.index', $tenant->slug) }}"
           class="flex flex-col items-center justify-center p-5 bg-white rounded-xl border border-gray-200 hover:border-teal-300 hover:shadow-lg transition-all group">
            <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-teal-200 transition-colors">
                <x-heroicon-o-table-cells class="w-6 h-6 text-teal-600" />
            </div>
            <span class="font-medium text-gray-700 group-hover:text-teal-600 transition-colors">Tables</span>
        </a>

        <a href="{{ route('admin.qrcodes.index', $tenant->slug) }}"
           class="flex flex-col items-center justify-center p-5 bg-white rounded-xl border border-gray-200 hover:border-violet-300 hover:shadow-lg transition-all group">
            <div class="w-12 h-12 bg-violet-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-violet-200 transition-colors">
                <x-heroicon-o-qr-code class="w-6 h-6 text-violet-600" />
            </div>
            <span class="font-medium text-gray-700 group-hover:text-violet-600 transition-colors">QR Codes</span>
        </a>

        <a href="{{ route('admin.statistics', $tenant->slug) }}"
           class="flex flex-col items-center justify-center p-5 bg-white rounded-xl border border-gray-200 hover:border-cyan-300 hover:shadow-lg transition-all group">
            <div class="w-12 h-12 bg-cyan-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-cyan-200 transition-colors">
                <x-heroicon-o-chart-bar class="w-6 h-6 text-cyan-600" />
            </div>
            <span class="font-medium text-gray-700 group-hover:text-cyan-600 transition-colors">Statistiques</span>
        </a>
    </div>

    <!-- Menus Section -->
    <div class="bg-white rounded-xl border border-gray-200 mb-8">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <x-heroicon-o-clipboard-document-list class="w-6 h-6 text-indigo-500" />
                <h2 class="text-xl font-bold text-gray-800">Vos Menus</h2>
            </div>
            <button @click="showMenuModal = true" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center">
                <x-heroicon-o-plus class="w-5 h-5 mr-2" />
                Nouveau Menu
            </button>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($menus as $menu)
            <div class="border border-gray-200 rounded-xl p-5 hover:shadow-lg hover:border-indigo-200 transition-all duration-300 bg-white">
                <div class="flex items-start justify-between mb-3">
                    <h3 class="font-bold text-lg text-gray-800">{{ $menu->title }}</h3>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $menu->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $menu->active ? 'Actif' : 'Inactif' }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 mb-4">
                    <span class="inline-flex items-center mr-3">
                        <x-heroicon-o-folder class="w-4 h-4 mr-1" />
                        {{ $menu->categories->count() }} catégories
                    </span>
                    <span class="inline-flex items-center">
                        <x-heroicon-o-cake class="w-4 h-4 mr-1" />
                        {{ $menu->categories->sum('dishes_count') }} plats
                    </span>
                </p>
                <a href="{{ route('admin.categories', [$tenant->slug, $menu->id]) }}"
                   class="block w-full text-center bg-indigo-50 text-indigo-700 py-2.5 rounded-lg hover:bg-indigo-100 font-medium transition-colors">
                    Gérer
                </a>
            </div>
            @endforeach

            <!-- New Menu Card -->
            <div @click="showMenuModal = true"
                 class="border-2 border-dashed border-gray-300 rounded-xl p-5 text-center hover:border-indigo-400 hover:bg-indigo-50/50 transition-all cursor-pointer group">
                <div class="py-6">
                    <div class="w-14 h-14 mx-auto bg-gray-100 group-hover:bg-indigo-100 rounded-xl flex items-center justify-center mb-3 transition-colors">
                        <x-heroicon-o-plus class="w-8 h-8 text-gray-400 group-hover:text-indigo-500 transition-colors" />
                    </div>
                    <div class="text-gray-600 font-medium group-hover:text-indigo-600">Nouveau Menu</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Popular Dishes - Rendu côté serveur -->
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center space-x-3">
            <x-heroicon-o-trophy class="w-6 h-6 text-yellow-500" />
            <h2 class="text-xl font-bold text-gray-800">Plats Populaires</h2>
        </div>

        <div class="p-6">
            @if($stats['popularDishes']->count() > 0)
                <div class="space-y-3">
                    @foreach($stats['popularDishes'] as $index => $dish)
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                        <div class="flex items-center space-x-3">
                            <span class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center text-sm font-bold text-gray-600">{{ $index + 1 }}</span>
                            <span class="font-medium text-gray-800">{{ $dish->name }}</span>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ $dish->order_count }} commandes
                        </span>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <x-heroicon-o-chart-bar class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                    <p class="font-medium">Aucune donnée</p>
                    <p class="text-sm">Les plats populaires apparaîtront ici après vos premières commandes.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Menu Modal -->
    <div x-show="showMenuModal" x-cloak
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
         @click.self="showMenuModal = false">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md" @click.stop>
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-800">Nouveau Menu</h3>
            </div>

            <form method="POST" action="{{ route('admin.menus.store', $tenant->slug) }}" class="p-6">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom du menu</label>
                        <input type="text" name="title" x-model="newMenu.title"
                               class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Ex: Menu Principal" required>
                    </div>

                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" name="active" x-model="newMenu.active" value="1"
                               class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-gray-700">Menu actif</span>
                    </label>
                </div>

                <div class="flex space-x-3 mt-6">
                    <button type="button" @click="showMenuModal = false"
                            class="flex-1 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
                        Annuler
                    </button>
                    <button type="submit"
                            class="flex-1 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
                        Créer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
