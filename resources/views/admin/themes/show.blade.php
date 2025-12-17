@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
            <div class="p-6" style="background-color: {{ $theme->getBackgroundColor() }}; color: {{ $theme->getTextColor() }}">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h1 class="text-3xl font-bold mb-2" style="font-family: {{ $theme->getHeadingFont() }}">{{ $theme->name }}</h1>
                        <p class="text-lg opacity-80">{{ $theme->description }}</p>
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('themes.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Retour
                        </a>
                        <a href="{{ route('themes.preview', $theme) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            Aperçu Complet
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Couleurs -->
                    <div>
                        <h3 class="text-xl font-semibold mb-4">Palette de Couleurs</h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span>Primaire</span>
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded border-2 border-gray-300" style="background-color: {{ $theme->getPrimaryColor() }}"></div>
                                    <code class="text-sm">{{ $theme->getPrimaryColor() }}</code>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Secondaire</span>
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded border-2 border-gray-300" style="background-color: {{ $theme->getSecondaryColor() }}"></div>
                                    <code class="text-sm">{{ $theme->getSecondaryColor() }}</code>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Accent</span>
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded border-2 border-gray-300" style="background-color: {{ $theme->getAccentColor() }}"></div>
                                    <code class="text-sm">{{ $theme->getAccentColor() }}</code>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Arrière-plan</span>
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded border-2 border-gray-300" style="background-color: {{ $theme->getBackgroundColor() }}"></div>
                                    <code class="text-sm">{{ $theme->getBackgroundColor() }}</code>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Texte</span>
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded border-2 border-gray-300" style="background-color: {{ $theme->getTextColor() }}"></div>
                                    <code class="text-sm">{{ $theme->getTextColor() }}</code>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Polices -->
                    <div>
                        <h3 class="text-xl font-semibold mb-4">Typographie</h3>
                        <div class="space-y-4">
                            <div>
                                <span class="block text-sm font-medium mb-1">Titre</span>
                                <div style="font-family: {{ $theme->getHeadingFont() }}; font-size: 24px; font-weight: bold;">
                                    Exemple de titre
                                </div>
                                <code class="text-sm text-gray-600">{{ $theme->getHeadingFont() }}</code>
                            </div>
                            <div>
                                <span class="block text-sm font-medium mb-1">Corps de texte</span>
                                <div style="font-family: {{ $theme->getBodyFont() }}; font-size: 16px;">
                                    Exemple de texte normal pour le contenu.
                                </div>
                                <code class="text-sm text-gray-600">{{ $theme->getBodyFont() }}</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-semibold mb-4">Actions</h3>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('themes.preview', $theme) }}"
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Aperçu du thème
                </a>
                <button onclick="copyThemeJson()"
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Copier JSON
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function copyThemeJson() {
    const themeData = {
        name: "{{ $theme->name }}",
        colors: @json($theme->colors),
        fonts: @json($theme->fonts),
        category: "{{ $theme->category }}"
    };

    navigator.clipboard.writeText(JSON.stringify(themeData, null, 2))
        .then(() => {
            alert('Configuration du thème copiée dans le presse-papiers !');
        })
        .catch(err => {
            console.error('Erreur lors de la copie :', err);
            alert('Erreur lors de la copie');
        });
}
</script>
@endsection
