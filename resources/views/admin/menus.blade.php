@extends('layouts.admin')

@section('title', 'Gestion des Menus')
@section('page-title', 'Gestion des Menus')
@section('breadcrumb')
    <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="hover:text-indigo-600">Dashboard</a>
    <span class="mx-2">/</span>
    <span>Menus</span>
@endsection

@section('content')
<div x-data="menusStore()">
    <!-- Actions Bar -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="flex items-center space-x-3">
            <span class="text-gray-500">{{ $menus->count() }} menu(s)</span>
        </div>
        <x-ui.button variant="primary" @click="openModal()">
            <x-heroicon-o-plus class="w-5 h-5 mr-2" />
            Nouveau Menu
        </x-ui.button>
    </div>

    <!-- Menus Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($menus as $menu)
        <x-ui.card hover class="group">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <x-heroicon-o-clipboard-document-list class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-gray-800">{{ $menu->title }}</h3>
                        <span class="text-xs text-gray-500">Créé le {{ $menu->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
                <x-ui.badge :variant="$menu->active ? 'success' : 'danger'">
                    {{ $menu->active ? 'Actif' : 'Inactif' }}
                </x-ui.badge>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <div class="flex items-center justify-center text-indigo-600 mb-1">
                        <x-heroicon-o-folder class="w-5 h-5 mr-1" />
                        <span class="font-bold text-lg">{{ $menu->categories->count() }}</span>
                    </div>
                    <span class="text-xs text-gray-500">Catégories</span>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <div class="flex items-center justify-center text-green-600 mb-1">
                        <x-heroicon-o-cake class="w-5 h-5 mr-1" />
                        <span class="font-bold text-lg">{{ $menu->categories->flatMap->dishes->count() }}</span>
                    </div>
                    <span class="text-xs text-gray-500">Plats</span>
                </div>
            </div>

            <div class="flex space-x-2">
                <a href="{{ route('admin.categories', [$tenant->slug, $menu->id]) }}"
                   class="flex-1 bg-indigo-50 text-indigo-700 text-center py-2.5 rounded-lg hover:bg-indigo-100 font-medium transition-colors text-sm">
                    <x-heroicon-o-eye class="w-4 h-4 inline mr-1" />
                    Gérer
                </a>
                <button @click="editMenu({{ json_encode($menu) }})"
                        class="p-2.5 bg-amber-50 text-amber-600 rounded-lg hover:bg-amber-100 transition-colors">
                    <x-heroicon-o-pencil class="w-5 h-5" />
                </button>
                <button @click="deleteMenu({{ $menu->id }}, '{{ $menu->title }}')"
                        class="p-2.5 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors">
                    <x-heroicon-o-trash class="w-5 h-5" />
                </button>
            </div>
        </x-ui.card>
        @endforeach

        <!-- New Menu Card -->
        <div @click="openModal()"
             class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-indigo-400 hover:bg-indigo-50/50 transition-all cursor-pointer group">
            <div class="py-6">
                <div class="w-14 h-14 mx-auto bg-gray-100 group-hover:bg-indigo-100 rounded-xl flex items-center justify-center mb-3 transition-colors">
                    <x-heroicon-o-plus class="w-8 h-8 text-gray-400 group-hover:text-indigo-500 transition-colors" />
                </div>
                <div class="text-gray-600 font-medium group-hover:text-indigo-600">Nouveau Menu</div>
                <div class="text-sm text-gray-500 mt-1">Cliquez pour créer un nouveau menu</div>
            </div>
        </div>
    </div>

    @if($menus->isEmpty())
        <x-ui.empty-state
            icon="clipboard-document-list"
            title="Aucun menu"
            description="Commencez par créer votre premier menu pour organiser vos plats."
            class="mt-8"
        >
            <x-slot name="action">
                <x-ui.button variant="primary" @click="openModal()">
                    <x-heroicon-o-plus class="w-5 h-5 mr-2" />
                    Créer un menu
                </x-ui.button>
            </x-slot>
        </x-ui.empty-state>
    @endif

    <!-- Menu Modal -->
    <div x-show="showModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
         @click.self="showModal = false">
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-indigo-600" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-800" x-text="editingMenu ? 'Modifier le Menu' : 'Nouveau Menu'"></h3>
                </div>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                    <x-heroicon-o-x-mark class="w-6 h-6" />
                </button>
            </div>

            <form @submit.prevent="saveMenu()">
                <div class="space-y-4">
                    <x-ui.input
                        label="Nom du menu"
                        placeholder="Ex: Menu Principal"
                        x-model="form.title"
                        required
                    />

                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" x-model="form.active"
                               class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-gray-700">Menu actif</span>
                    </label>
                </div>

                <div class="flex space-x-3 mt-6">
                    <x-ui.button type="button" variant="secondary" class="flex-1" @click="showModal = false">
                        Annuler
                    </x-ui.button>
                    <x-ui.button type="submit" variant="primary" class="flex-1">
                        <x-heroicon-o-check class="w-5 h-5 mr-2" />
                        <span x-text="editingMenu ? 'Modifier' : 'Créer'"></span>
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('menusStore', () => ({
        showModal: false,
        editingMenu: null,
        form: {
            title: '',
            active: true
        },

        openModal() {
            this.editingMenu = null;
            this.form = { title: '', active: true };
            this.showModal = true;
        },

        editMenu(menu) {
            this.editingMenu = menu;
            this.form = {
                title: menu.title,
                active: menu.active
            };
            this.showModal = true;
        },

        async saveMenu() {
            const url = this.editingMenu
                ? '{{ route("admin.menus.update", [$tenant->slug, ":id"]) }}'.replace(':id', this.editingMenu.id)
                : '{{ route("admin.menus.store", $tenant->slug) }}';

            const method = this.editingMenu ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.form)
                });

                const result = await response.json();

                if (result.success) {
                    this.showModal = false;
                    location.reload();
                } else {
                    alert('Erreur: ' + (result.message || 'Erreur inconnue'));
                }
            } catch (error) {
                alert('Erreur réseau: ' + error.message);
            }
        },

        async deleteMenu(menuId, menuTitle) {
            if (!confirm(`Supprimer le menu "${menuTitle}" ? Cette action est irréversible.`)) return;

            try {
                const response = await fetch('{{ route("admin.menus.destroy", [$tenant->slug, ":id"]) }}'.replace(':id', menuId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await response.json();

                if (result.success) {
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
