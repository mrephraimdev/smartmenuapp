@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Gestion des Thèmes</h1>
        <a href="{{ route('superadmin.dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Retour au Dashboard
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($themes as $theme)
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6" style="background-color: {{ $theme->getBackgroundColor() }}; color: {{ $theme->getTextColor() }}">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-xl font-semibold mb-2" style="font-family: {{ $theme->getHeadingFont() }}">{{ $theme->name }}</h3>
                        <span class="inline-block px-2 py-1 text-xs rounded-full"
                              style="background-color: {{ $theme->getAccentColor() }}; color: white;">
                            {{ ucfirst($theme->category) }}
                        </span>
                    </div>
                    @if($theme->is_default)
                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">
                        Défaut
                    </span>
                    @endif
                </div>

                <p class="text-sm mb-4 opacity-80">{{ $theme->description }}</p>

                <!-- Aperçu des couleurs -->
                <div class="flex space-x-2 mb-4">
                    <div class="w-6 h-6 rounded-full border-2 border-gray-300" style="background-color: {{ $theme->getPrimaryColor() }}" title="Primaire"></div>
                    <div class="w-6 h-6 rounded-full border-2 border-gray-300" style="background-color: {{ $theme->getSecondaryColor() }}" title="Secondaire"></div>
                    <div class="w-6 h-6 rounded-full border-2 border-gray-300" style="background-color: {{ $theme->getAccentColor() }}" title="Accent"></div>
                </div>

                <div class="flex space-x-2">
                    <a href="{{ route('themes.show', $theme) }}" class="bg-blue-500 hover:bg-blue-700 text-white text-sm font-bold py-2 px-4 rounded">
                        Voir Détails
                    </a>
                    <a href="{{ route('themes.preview', $theme) }}" class="bg-green-500 hover:bg-green-700 text-white text-sm font-bold py-2 px-4 rounded">
                        Aperçu
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($themes->isEmpty())
    <div class="text-center py-12">
        <p class="text-gray-500 text-lg">Aucun thème disponible.</p>
    </div>
    @endif
</div>
@endsection
