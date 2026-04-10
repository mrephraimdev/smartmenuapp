@extends('layouts.admin')

@section('title', $theme->name)
@section('page-title', $theme->name)
@section('breadcrumb')
    <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="text-amber-600 hover:underline">Dashboard</a>
    <span class="mx-1 text-gray-300">/</span>
    <a href="{{ route('admin.themes.index', $tenant->slug) }}" class="text-amber-600 hover:underline">Themes</a>
    <span class="mx-1 text-gray-300">/</span>
    <span class="text-gray-500">{{ $theme->name }}</span>
@endsection

@section('content')
    <div class="max-w-4xl mx-auto">
        {{-- Theme Preview Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="p-6 lg:p-8" style="background-color: {{ $theme->getBackgroundColor() }}; color: {{ $theme->getTextColor() }}">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 mb-8">
                    <div>
                        <h1 class="text-3xl font-bold mb-2" style="font-family: {{ $theme->getHeadingFont() }}">{{ $theme->name }}</h1>
                        <p class="text-lg opacity-80">{{ $theme->description }}</p>
                    </div>
                    <a href="{{ route('admin.themes.preview', [$tenant->slug, $theme]) }}"
                       class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm text-current font-semibold py-2.5 px-5 rounded-xl hover:bg-white/30 transition-colors text-sm">
                        <x-heroicon-o-eye class="w-5 h-5" />
                        Apercu Complet
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    {{-- Couleurs --}}
                    <div>
                        <h3 class="text-xl font-semibold mb-5">Palette de Couleurs</h3>
                        <div class="space-y-4">
                            @foreach([
                                ['Primaire', $theme->getPrimaryColor()],
                                ['Secondaire', $theme->getSecondaryColor()],
                                ['Accent', $theme->getAccentColor()],
                                ['Arriere-plan', $theme->getBackgroundColor()],
                                ['Texte', $theme->getTextColor()],
                            ] as [$label, $color])
                            <div class="flex items-center justify-between">
                                <span class="text-base">{{ $label }}</span>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg border-2 border-white/30 shadow-sm" style="background-color: {{ $color }}"></div>
                                    <code class="text-sm opacity-70">{{ $color }}</code>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Polices --}}
                    <div>
                        <h3 class="text-xl font-semibold mb-5">Typographie</h3>
                        <div class="space-y-6">
                            <div>
                                <span class="block text-sm font-medium mb-2 opacity-70">Titre</span>
                                <div style="font-family: {{ $theme->getHeadingFont() }}; font-size: 24px; font-weight: bold;">
                                    Exemple de titre
                                </div>
                                <code class="text-sm opacity-60">{{ $theme->getHeadingFont() }}</code>
                            </div>
                            <div>
                                <span class="block text-sm font-medium mb-2 opacity-70">Corps de texte</span>
                                <div style="font-family: {{ $theme->getBodyFont() }}; font-size: 16px;">
                                    Exemple de texte normal pour le contenu.
                                </div>
                                <code class="text-sm opacity-60">{{ $theme->getBodyFont() }}</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-8">
            <h3 class="text-xl font-bold text-gray-900 mb-5">Actions</h3>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.themes.preview', [$tenant->slug, $theme]) }}"
                   class="inline-flex items-center gap-2 bg-amber-500 text-white font-semibold py-3 px-5 rounded-xl hover:bg-amber-600 transition-colors text-sm">
                    <x-heroicon-o-eye class="w-5 h-5" />
                    Apercu du theme
                </a>
                <button onclick="copyThemeJson()"
                        class="inline-flex items-center gap-2 bg-emerald-500 text-white font-semibold py-3 px-5 rounded-xl hover:bg-emerald-600 transition-colors text-sm">
                    <x-heroicon-o-clipboard class="w-5 h-5" />
                    Copier JSON
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
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
            if (window.toast) {
                window.toast.success('Configuration du theme copiee !');
            } else {
                alert('Configuration du theme copiee dans le presse-papiers !');
            }
        })
        .catch(err => {
            console.error('Erreur lors de la copie :', err);
            if (window.toast) {
                window.toast.error('Erreur lors de la copie');
            }
        });
}
</script>
@endpush
