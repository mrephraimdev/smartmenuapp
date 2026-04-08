@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Sélection du Thème</h1>
            <p class="text-gray-600 mt-2">Choisissez un thème pour personnaliser l'apparence de votre menu</p>
        </div>
        <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Current Theme -->
    @if($currentTheme)
    <div class="bg-white rounded-lg shadow-md p-6 mb-8 border-2 border-blue-500">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-full mr-4 flex items-center justify-center" style="background-color: {{ $currentTheme->primary_color }}">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Thème actuel : {{ $currentTheme->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $currentTheme->description }}</p>
                </div>
            </div>
            <div class="flex space-x-2">
                <div class="w-8 h-8 rounded-full border-2 border-gray-200" style="background-color: {{ $currentTheme->primary_color }}" title="Primaire"></div>
                <div class="w-8 h-8 rounded-full border-2 border-gray-200" style="background-color: {{ $currentTheme->secondary_color }}" title="Secondaire"></div>
                <div class="w-8 h-8 rounded-full border-2 border-gray-200" style="background-color: {{ $currentTheme->accent_color }}" title="Accent"></div>
            </div>
        </div>
    </div>
    @endif

    <!-- Theme Categories Filter -->
    <div class="mb-6">
        <div class="flex flex-wrap gap-2">
            <button class="theme-filter-btn active px-4 py-2 rounded-full bg-blue-500 text-white text-sm font-medium" data-category="all">
                Tous
            </button>
            <button class="theme-filter-btn px-4 py-2 rounded-full bg-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-300" data-category="restaurant">
                Restaurant
            </button>
            <button class="theme-filter-btn px-4 py-2 rounded-full bg-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-300" data-category="wedding">
                Mariage
            </button>
            <button class="theme-filter-btn px-4 py-2 rounded-full bg-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-300" data-category="cafe">
                Café
            </button>
            <button class="theme-filter-btn px-4 py-2 rounded-full bg-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-300" data-category="fast_food">
                Fast Food
            </button>
            <button class="theme-filter-btn px-4 py-2 rounded-full bg-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-300" data-category="fine_dining">
                Gastronomique
            </button>
            <button class="theme-filter-btn px-4 py-2 rounded-full bg-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-300" data-category="corporate">
                Corporate
            </button>
        </div>
    </div>

    <!-- Themes Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="themes-grid">
        @foreach($themes as $theme)
        <div class="theme-card bg-white rounded-lg shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl {{ $currentTheme && $currentTheme->id === $theme->id ? 'ring-2 ring-blue-500' : '' }}"
             data-category="{{ $theme->category }}">

            <!-- Theme Preview Header -->
            <div class="h-32 p-4 relative" style="background-color: {{ $theme->background_color }};">
                <!-- Mini Preview -->
                <div class="flex justify-between items-start">
                    <div>
                        <div class="h-4 w-24 rounded mb-2" style="background-color: {{ $theme->primary_color }}"></div>
                        <div class="h-2 w-16 rounded" style="background-color: {{ $theme->text_color }}; opacity: 0.3"></div>
                    </div>
                    <div class="w-8 h-8 rounded" style="background-color: {{ $theme->accent_color }}"></div>
                </div>

                <!-- Fake Menu Items -->
                <div class="absolute bottom-4 left-4 right-4 flex space-x-2">
                    <div class="flex-1 h-10 rounded p-2" style="background-color: {{ $theme->secondary_color }}">
                        <div class="h-2 w-full rounded" style="background-color: {{ $theme->text_color }}; opacity: 0.2"></div>
                        <div class="h-2 w-3/4 rounded mt-1" style="background-color: {{ $theme->primary_color }}; opacity: 0.5"></div>
                    </div>
                    <div class="flex-1 h-10 rounded p-2" style="background-color: {{ $theme->secondary_color }}">
                        <div class="h-2 w-full rounded" style="background-color: {{ $theme->text_color }}; opacity: 0.2"></div>
                        <div class="h-2 w-3/4 rounded mt-1" style="background-color: {{ $theme->primary_color }}; opacity: 0.5"></div>
                    </div>
                </div>

                @if($currentTheme && $currentTheme->id === $theme->id)
                <div class="absolute top-2 right-2 bg-blue-500 text-white text-xs px-2 py-1 rounded-full">
                    Actif
                </div>
                @endif
            </div>

            <!-- Theme Info -->
            <div class="p-4">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $theme->name }}</h3>
                    <span class="inline-block px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600">
                        {{ ucfirst(str_replace('_', ' ', $theme->category)) }}
                    </span>
                </div>

                <p class="text-sm text-gray-500 mb-4 line-clamp-2">{{ $theme->description }}</p>

                <!-- Color Palette -->
                <div class="flex items-center space-x-1 mb-4">
                    <div class="w-6 h-6 rounded-full border border-gray-200 cursor-help"
                         style="background-color: {{ $theme->primary_color }}"
                         title="Couleur primaire"></div>
                    <div class="w-6 h-6 rounded-full border border-gray-200 cursor-help"
                         style="background-color: {{ $theme->secondary_color }}"
                         title="Couleur secondaire"></div>
                    <div class="w-6 h-6 rounded-full border border-gray-200 cursor-help"
                         style="background-color: {{ $theme->background_color }}"
                         title="Arrière-plan"></div>
                    <div class="w-6 h-6 rounded-full border border-gray-200 cursor-help"
                         style="background-color: {{ $theme->text_color }}"
                         title="Texte"></div>
                    <div class="w-6 h-6 rounded-full border border-gray-200 cursor-help"
                         style="background-color: {{ $theme->accent_color }}"
                         title="Accent"></div>
                </div>

                <!-- Fonts Info -->
                <div class="text-xs text-gray-400 mb-4">
                    <span>Titres: {{ $theme->font_family_heading }}</span>
                    <span class="mx-2">|</span>
                    <span>Corps: {{ $theme->font_family_body }}</span>
                </div>

                <!-- Actions -->
                <div class="flex space-x-2">
                    <a href="{{ route('admin.themes.preview', [$tenant->slug, $theme]) }}"
                       class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium py-2 px-4 rounded transition-colors">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Aperçu
                    </a>

                    @if(!$currentTheme || $currentTheme->id !== $theme->id)
                    <form action="{{ route('admin.themes.apply', [$tenant->slug, $theme]) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full text-white text-sm font-medium py-2 px-4 rounded transition-colors"
                                style="background-color: {{ $theme->primary_color }}">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Appliquer
                        </button>
                    </form>
                    @else
                    <button disabled class="flex-1 bg-gray-300 text-gray-500 text-sm font-medium py-2 px-4 rounded cursor-not-allowed">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Actif
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($themes->isEmpty())
    <div class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun thème disponible</h3>
        <p class="mt-1 text-sm text-gray-500">Les thèmes seront bientôt disponibles.</p>
    </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.theme-filter-btn');
    const themeCards = document.querySelectorAll('.theme-card');

    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const category = this.dataset.category;

            // Update active button
            filterButtons.forEach(btn => {
                btn.classList.remove('active', 'bg-blue-500', 'text-white');
                btn.classList.add('bg-gray-200', 'text-gray-700');
            });
            this.classList.remove('bg-gray-200', 'text-gray-700');
            this.classList.add('active', 'bg-blue-500', 'text-white');

            // Filter cards
            themeCards.forEach(card => {
                if (category === 'all' || card.dataset.category === category) {
                    card.style.display = 'block';
                    card.classList.add('animate-fade-in');
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
});
</script>
@endpush

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.animate-fade-in {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
@endsection
