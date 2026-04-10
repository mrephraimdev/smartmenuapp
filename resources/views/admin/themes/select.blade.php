@extends('layouts.admin')

@section('title', 'Themes')
@section('page-title', 'Selection du Theme')
@section('breadcrumb')
    <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="hover:text-amber-500">Dashboard</a>
    <span class="mx-2">/</span>
    <span>Themes</span>
@endsection

@section('content')
    @if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 flex items-center">
        <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" />
        <p class="text-green-800">{{ session('success') }}</p>
    </div>
    @endif

    {{-- Current Theme --}}
    @if($currentTheme)
    <div class="bg-white rounded-2xl shadow-sm border-2 border-amber-400 p-6 mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-full mr-4 flex items-center justify-center" style="background-color: {{ $currentTheme->primary_color }}">
                    <x-heroicon-s-check class="w-6 h-6 text-white" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Theme actuel : {{ $currentTheme->name }}</h3>
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

    {{-- Category filter --}}
    <div class="mb-6">
        <div class="flex flex-wrap gap-2">
            <button class="theme-filter-btn active px-4 py-2 rounded-full bg-amber-500 text-white text-sm font-medium" data-category="all">
                Tous
            </button>
            <button class="theme-filter-btn px-4 py-2 rounded-full bg-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-300" data-category="restaurant">
                Restaurant
            </button>
            <button class="theme-filter-btn px-4 py-2 rounded-full bg-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-300" data-category="wedding">
                Mariage
            </button>
            <button class="theme-filter-btn px-4 py-2 rounded-full bg-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-300" data-category="cafe">
                Cafe
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

    {{-- Themes Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="themes-grid">
        @foreach($themes as $theme)
        <div class="theme-card bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-md {{ $currentTheme && $currentTheme->id === $theme->id ? 'ring-2 ring-amber-400' : '' }}"
             data-category="{{ $theme->category }}">

            {{-- Theme Preview Header --}}
            <div class="h-32 p-4 relative" style="background-color: {{ $theme->background_color }};">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="h-4 w-24 rounded mb-2" style="background-color: {{ $theme->primary_color }}"></div>
                        <div class="h-2 w-16 rounded" style="background-color: {{ $theme->text_color }}; opacity: 0.3"></div>
                    </div>
                    <div class="w-8 h-8 rounded" style="background-color: {{ $theme->accent_color }}"></div>
                </div>

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
                <div class="absolute top-2 right-2 bg-amber-500 text-white text-xs px-2 py-1 rounded-full font-medium">
                    Actif
                </div>
                @endif
            </div>

            {{-- Theme Info --}}
            <div class="p-4">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-base font-semibold text-gray-900">{{ $theme->name }}</h3>
                    <span class="inline-block px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600">
                        {{ ucfirst(str_replace('_', ' ', $theme->category)) }}
                    </span>
                </div>

                <p class="text-sm text-gray-500 mb-4 line-clamp-2">{{ $theme->description }}</p>

                {{-- Color Palette --}}
                <div class="flex items-center space-x-1 mb-4">
                    <div class="w-6 h-6 rounded-full border border-gray-200 cursor-help"
                         style="background-color: {{ $theme->primary_color }}"
                         title="Couleur primaire"></div>
                    <div class="w-6 h-6 rounded-full border border-gray-200 cursor-help"
                         style="background-color: {{ $theme->secondary_color }}"
                         title="Couleur secondaire"></div>
                    <div class="w-6 h-6 rounded-full border border-gray-200 cursor-help"
                         style="background-color: {{ $theme->background_color }}"
                         title="Arriere-plan"></div>
                    <div class="w-6 h-6 rounded-full border border-gray-200 cursor-help"
                         style="background-color: {{ $theme->text_color }}"
                         title="Texte"></div>
                    <div class="w-6 h-6 rounded-full border border-gray-200 cursor-help"
                         style="background-color: {{ $theme->accent_color }}"
                         title="Accent"></div>
                </div>

                {{-- Fonts --}}
                <div class="text-xs text-gray-400 mb-4">
                    <span>Titres: {{ $theme->font_family_heading }}</span>
                    <span class="mx-2">|</span>
                    <span>Corps: {{ $theme->font_family_body }}</span>
                </div>

                {{-- Actions --}}
                <div class="flex space-x-2">
                    <a href="{{ route('admin.themes.preview', [$tenant->slug, $theme]) }}"
                       class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium py-2 px-4 rounded-xl transition-colors">
                        <x-heroicon-o-eye class="w-4 h-4 inline mr-1" />
                        Apercu
                    </a>

                    @if(!$currentTheme || $currentTheme->id !== $theme->id)
                    <form action="{{ route('admin.themes.apply', [$tenant->slug, $theme]) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full text-white text-sm font-medium py-2 px-4 rounded-xl transition-colors"
                                style="background-color: {{ $theme->primary_color }}">
                            <x-heroicon-o-check class="w-4 h-4 inline mr-1" />
                            Appliquer
                        </button>
                    </form>
                    @else
                    <button disabled class="flex-1 bg-gray-300 text-gray-500 text-sm font-medium py-2 px-4 rounded-xl cursor-not-allowed">
                        <x-heroicon-o-check class="w-4 h-4 inline mr-1" />
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
        <x-heroicon-o-paint-brush class="w-12 h-12 mx-auto text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun theme disponible</h3>
        <p class="mt-1 text-sm text-gray-500">Les themes seront bientot disponibles.</p>
    </div>
    @endif
@endsection

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
                btn.classList.remove('active', 'bg-amber-500', 'text-white');
                btn.classList.add('bg-gray-200', 'text-gray-700');
            });
            this.classList.remove('bg-gray-200', 'text-gray-700');
            this.classList.add('active', 'bg-amber-500', 'text-white');

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

@push('head')
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
@endpush
