@extends('layouts.admin')

@section('title', 'Catégories - ' . $menu->title)
@section('page-title', 'Catégories')
@section('breadcrumb')
    <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="hover:text-indigo-600">Dashboard</a>
    <span class="mx-2">/</span>
    <a href="{{ route('admin.menus', $tenant->slug) }}" class="hover:text-indigo-600">Menus</a>
    <span class="mx-2">/</span>
    <span>{{ $menu->title }}</span>
@endsection

@section('content')
<div x-data="categoriesStore()">
    <!-- Actions Bar -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="flex items-center space-x-3">
            <div class="flex items-center space-x-2 bg-indigo-50 text-indigo-700 px-4 py-2 rounded-lg">
                <x-heroicon-o-clipboard-document-list class="w-5 h-5" />
                <span class="font-medium">{{ $menu->title }}</span>
            </div>
        </div>
        <x-ui.button variant="primary" @click="showCategoryModal = true">
            <x-heroicon-o-plus class="w-5 h-5 mr-2" />
            Nouvelle Catégorie
        </x-ui.button>
    </div>

    <!-- Categories Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($menu->categories as $category)
        <x-ui.card hover class="group">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center group-hover:bg-indigo-200 transition-colors">
                        <x-heroicon-o-folder class="w-5 h-5 text-indigo-600" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-gray-800">{{ $category->name }}</h3>
                        <span class="text-xs text-gray-500">Ordre: {{ $category->sort_order }}</span>
                    </div>
                </div>
            </div>

            <div class="space-y-2 mb-4">
                <div class="flex items-center text-sm text-gray-600">
                    <x-heroicon-o-cake class="w-4 h-4 mr-2 text-gray-400" />
                    <span>{{ $category->dishes->count() }} plats</span>
                </div>
                <div class="flex items-center text-sm text-gray-600">
                    <x-heroicon-o-calendar class="w-4 h-4 mr-2 text-gray-400" />
                    <span>Créé le {{ $category->created_at->format('d/m/Y') }}</span>
                </div>
            </div>

            <a href="{{ route('admin.dishes', [$tenant->slug, $category->id]) }}"
               class="block w-full text-center bg-indigo-50 text-indigo-700 py-2.5 rounded-lg hover:bg-indigo-100 font-medium transition-colors">
                <x-heroicon-o-eye class="w-4 h-4 inline mr-1" />
                Voir les plats
            </a>
        </x-ui.card>
        @endforeach

        <!-- New Category Card -->
        <div @click="showCategoryModal = true"
             class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-indigo-400 hover:bg-indigo-50/50 transition-all cursor-pointer group">
            <div class="py-6">
                <div class="w-14 h-14 mx-auto bg-gray-100 group-hover:bg-indigo-100 rounded-xl flex items-center justify-center mb-3 transition-colors">
                    <x-heroicon-o-plus class="w-8 h-8 text-gray-400 group-hover:text-indigo-500 transition-colors" />
                </div>
                <div class="text-gray-600 font-medium group-hover:text-indigo-600">Nouvelle Catégorie</div>
                <div class="text-sm text-gray-500 mt-1">Cliquez pour créer une nouvelle catégorie</div>
            </div>
        </div>
    </div>

    @if($menu->categories->isEmpty())
        <x-ui.empty-state
            icon="folder"
            title="Aucune catégorie"
            description="Commencez par créer votre première catégorie pour organiser vos plats."
            class="mt-8"
        >
            <x-slot name="action">
                <x-ui.button variant="primary" @click="showCategoryModal = true">
                    <x-heroicon-o-plus class="w-5 h-5 mr-2" />
                    Créer une catégorie
                </x-ui.button>
            </x-slot>
        </x-ui.empty-state>
    @endif

    <!-- Category Modal -->
    <div x-show="showCategoryModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
         @click.self="showCategoryModal = false">
        <div x-show="showCategoryModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-folder-plus class="w-5 h-5 text-indigo-600" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Nouvelle Catégorie</h3>
                </div>
                <button @click="showCategoryModal = false" class="text-gray-400 hover:text-gray-600">
                    <x-heroicon-o-x-mark class="w-6 h-6" />
                </button>
            </div>

            <form @submit.prevent="createCategory()">
                <div class="space-y-4">
                    <x-ui.input
                        label="Nom de la catégorie"
                        placeholder="Ex: Entrées, Plats Principaux..."
                        x-model="newCategory.name"
                        required
                    />

                    <x-ui.input
                        label="Ordre d'affichage"
                        type="number"
                        x-model="newCategory.sort_order"
                        min="0"
                    />
                </div>

                <div class="flex space-x-3 mt-6">
                    <x-ui.button type="button" variant="secondary" class="flex-1" @click="showCategoryModal = false">
                        Annuler
                    </x-ui.button>
                    <x-ui.button type="submit" variant="primary" class="flex-1">
                        <x-heroicon-o-plus class="w-5 h-5 mr-2" />
                        Créer
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('categoriesStore', () => ({
        showCategoryModal: false,
        newCategory: {
            name: '',
            sort_order: 0
        },

        async createCategory() {
            try {
                const response = await fetch('/admin/{{ $tenant->slug }}/menus/{{ $menu->id }}/categories', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.newCategory)
                });

                const result = await response.json();

                if (result.success) {
                    this.showCategoryModal = false;
                    location.reload();
                } else {
                    alert('Erreur: ' + result.message);
                }
            } catch (error) {
                alert('Erreur réseau: ' + error.message);
            }
        }
    }));
});
</script>
@endpush
@endsection
