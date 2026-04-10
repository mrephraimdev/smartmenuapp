@extends('layouts.admin')

@section('title', 'Themes')
@section('page-title', 'Themes')
@section('breadcrumb')
    <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="text-amber-600 hover:underline">Dashboard</a>
    <span class="mx-1 text-gray-300">/</span>
    <span class="text-gray-500">Themes</span>
@endsection

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Gestion des Themes</h2>
            <p class="text-sm text-gray-500 mt-1">Personnalisez l'apparence de votre menu</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($themes as $theme)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
            <div class="p-6" style="background-color: {{ $theme->getBackgroundColor() }}; color: {{ $theme->getTextColor() }}">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-xl font-semibold mb-2" style="font-family: {{ $theme->getHeadingFont() }}">{{ $theme->name }}</h3>
                        <span class="inline-block px-2.5 py-1 text-xs font-bold rounded-full"
                              style="background-color: {{ $theme->getAccentColor() }}; color: white;">
                            {{ ucfirst($theme->category) }}
                        </span>
                    </div>
                    @if($theme->is_default)
                    <span class="bg-green-100 text-green-800 text-xs font-bold px-2.5 py-1 rounded-full">
                        Defaut
                    </span>
                    @endif
                </div>

                <p class="text-sm mb-4 opacity-80">{{ $theme->description }}</p>

                {{-- Apercu des couleurs --}}
                <div class="flex space-x-2 mb-4">
                    <div class="w-7 h-7 rounded-full border-2 border-white/30 shadow-sm" style="background-color: {{ $theme->getPrimaryColor() }}" title="Primaire"></div>
                    <div class="w-7 h-7 rounded-full border-2 border-white/30 shadow-sm" style="background-color: {{ $theme->getSecondaryColor() }}" title="Secondaire"></div>
                    <div class="w-7 h-7 rounded-full border-2 border-white/30 shadow-sm" style="background-color: {{ $theme->getAccentColor() }}" title="Accent"></div>
                </div>

                <div class="flex space-x-2">
                    <a href="{{ route('admin.themes.show', [$tenant->slug, $theme]) }}"
                       class="inline-flex items-center gap-1.5 bg-white/20 backdrop-blur-sm text-current text-sm font-semibold py-2.5 px-4 rounded-xl hover:bg-white/30 transition-colors">
                        <x-heroicon-o-eye class="w-4 h-4" />
                        Voir Details
                    </a>
                    <a href="{{ route('admin.themes.edit', [$tenant->slug, $theme]) }}"
                       class="inline-flex items-center gap-1.5 bg-white/20 backdrop-blur-sm text-current text-sm font-semibold py-2.5 px-4 rounded-xl hover:bg-white/30 transition-colors">
                        <x-heroicon-o-sparkles class="w-4 h-4" />
                        Apercu
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($themes->isEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
        <x-heroicon-o-paint-brush class="w-12 h-12 mx-auto text-gray-300 mb-3" />
        <p class="text-base font-medium text-gray-500">Aucun theme disponible.</p>
        <p class="text-sm text-gray-400 mt-1">Les themes seront bientot disponibles.</p>
    </div>
    @endif
@endsection
