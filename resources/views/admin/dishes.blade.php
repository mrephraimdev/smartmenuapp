@extends('layouts.admin')

@section('title', 'Plats - ' . $category->name)
@section('page-title', 'Gestion des Plats')
@section('breadcrumb')
    <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="hover:text-indigo-600">Dashboard</a>
    <span class="mx-2">/</span>
    <a href="{{ route('admin.menus', $tenant->slug) }}" class="hover:text-indigo-600">Menus</a>
    <span class="mx-2">/</span>
    <a href="{{ route('admin.categories', [$tenant->slug, $category->menu->id]) }}" class="hover:text-indigo-600">{{ $category->menu->title }}</a>
    <span class="mx-2">/</span>
    <span>{{ $category->name }}</span>
@endsection

@section('content')
<div x-data="dishesStore()">
    <!-- Actions Bar -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="flex items-center space-x-3">
            <div class="flex items-center space-x-2 bg-indigo-50 text-indigo-700 px-4 py-2 rounded-lg">
                <x-heroicon-o-folder class="w-5 h-5" />
                <span class="font-medium">{{ $category->name }}</span>
            </div>
            <span class="text-gray-500">{{ $category->dishes->count() }} plat(s)</span>
        </div>
        <x-ui.button variant="primary" @click="openModal()">
            <x-heroicon-o-plus class="w-5 h-5 mr-2" />
            Nouveau Plat
        </x-ui.button>
    </div>

    <!-- Dishes Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($category->dishes as $dish)
        <x-ui.card hover class="group overflow-hidden">
            <!-- Photo du plat -->
            <div class="relative -mx-5 -mt-5 mb-4">
                @if($dish->photo_url)
                    <img src="{{ $dish->photo_url }}" alt="{{ $dish->name }}" class="w-full h-40 object-cover">
                    <button @click="deleteDishPhoto({{ $dish->id }})"
                            class="absolute top-2 right-2 p-1.5 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors shadow-lg opacity-0 group-hover:opacity-100">
                        <x-heroicon-o-x-mark class="w-4 h-4" />
                    </button>
                    <label class="absolute bottom-2 right-2 p-1.5 bg-white text-gray-700 rounded-full hover:bg-gray-100 transition-colors shadow-lg cursor-pointer opacity-0 group-hover:opacity-100">
                        <x-heroicon-o-camera class="w-4 h-4" />
                        <input type="file" class="hidden" accept="image/*" @change="uploadPhoto($event, {{ $dish->id }})">
                    </label>
                @else
                    <div class="w-full h-40 bg-gradient-to-br from-gray-100 to-gray-200 flex flex-col items-center justify-center">
                        <x-heroicon-o-photo class="w-10 h-10 text-gray-400 mb-2" />
                        <label class="cursor-pointer text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                            <span>Ajouter une photo</span>
                            <input type="file" class="hidden" accept="image/*" @change="uploadPhoto($event, {{ $dish->id }})">
                        </label>
                    </div>
                @endif
            </div>

            <div class="flex items-start justify-between mb-3">
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-lg text-gray-800 truncate">{{ $dish->name }}</h3>
                    @if($dish->description)
                        <p class="text-sm text-gray-500 line-clamp-2 mt-1">{{ $dish->description }}</p>
                    @endif
                </div>
                <div class="flex flex-col items-end space-y-1 ml-3">
                    <x-ui.badge :variant="$dish->active ? 'success' : 'danger'">
                        {{ $dish->active ? 'Actif' : 'Inactif' }}
                    </x-ui.badge>
                    <span class="text-lg font-bold text-green-600">{{ number_format($dish->price_base, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>

            <div class="flex items-center space-x-4 text-xs text-gray-500 mb-4">
                <div class="flex items-center">
                    <x-heroicon-o-rectangle-stack class="w-4 h-4 mr-1" />
                    {{ $dish->variants->count() }} variantes
                </div>
                <div class="flex items-center">
                    <x-heroicon-o-cog-6-tooth class="w-4 h-4 mr-1" />
                    {{ $dish->options->count() }} options
                </div>
            </div>

            <div class="flex space-x-2">
                <button @click="editDish({{ $dish->id }})"
                        class="flex-1 bg-indigo-50 text-indigo-700 text-center py-2 rounded-lg hover:bg-indigo-100 font-medium transition-colors text-sm">
                    <x-heroicon-o-pencil class="w-4 h-4 inline mr-1" />
                    Modifier
                </button>
                <button @click="toggleDish({{ $dish->id }})"
                        class="p-2 {{ $dish->active ? 'bg-amber-50 text-amber-600 hover:bg-amber-100' : 'bg-green-50 text-green-600 hover:bg-green-100' }} rounded-lg transition-colors">
                    @if($dish->active)
                        <x-heroicon-o-pause class="w-5 h-5" />
                    @else
                        <x-heroicon-o-play class="w-5 h-5" />
                    @endif
                </button>
                <button @click="deleteDish({{ $dish->id }}, '{{ addslashes($dish->name) }}')"
                        class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors">
                    <x-heroicon-o-trash class="w-5 h-5" />
                </button>
            </div>
        </x-ui.card>
        @endforeach

        <!-- New Dish Card -->
        <div @click="openModal()"
             class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-indigo-400 hover:bg-indigo-50/50 transition-all cursor-pointer group">
            <div class="py-6">
                <div class="w-14 h-14 mx-auto bg-gray-100 group-hover:bg-indigo-100 rounded-xl flex items-center justify-center mb-3 transition-colors">
                    <x-heroicon-o-plus class="w-8 h-8 text-gray-400 group-hover:text-indigo-500 transition-colors" />
                </div>
                <div class="text-gray-600 font-medium group-hover:text-indigo-600">Nouveau Plat</div>
                <div class="text-sm text-gray-500 mt-1">Cliquez pour créer un nouveau plat</div>
            </div>
        </div>
    </div>

    @if($category->dishes->isEmpty())
        <x-ui.empty-state
            icon="cake"
            title="Aucun plat"
            description="Commencez par créer votre premier plat dans cette catégorie."
            class="mt-8"
        >
            <x-slot name="action">
                <x-ui.button variant="primary" @click="openModal()">
                    <x-heroicon-o-plus class="w-5 h-5 mr-2" />
                    Créer un plat
                </x-ui.button>
            </x-slot>
        </x-ui.empty-state>
    @endif

    <!-- Dish Modal -->
    <div x-show="showModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
         @click.self="showModal = false"
         @keydown.escape.window="showModal = false">
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-cake class="w-5 h-5 text-indigo-600" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-800" x-text="editingDish ? 'Modifier le Plat' : 'Nouveau Plat'"></h3>
                </div>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                    <x-heroicon-o-x-mark class="w-6 h-6" />
                </button>
            </div>

            <form @submit.prevent="saveDish()">
                <!-- Basic Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <x-ui.input
                        label="Nom du plat"
                        placeholder="Ex: Salade César"
                        x-model="form.name"
                        required
                    />
                    <x-ui.input
                        label="Prix de base (FCFA)"
                        type="number"
                        placeholder="Ex: 4500"
                        x-model="form.price_base"
                        min="0"
                        step="100"
                        required
                    />
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea x-model="form.description"
                              rows="3"
                              placeholder="Description du plat..."
                              class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"></textarea>
                </div>

                <label class="flex items-center space-x-3 cursor-pointer mb-6">
                    <input type="checkbox" x-model="form.active"
                           class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-gray-700">Plat actif (visible dans le menu)</span>
                </label>

                <!-- Variants -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold text-gray-800 flex items-center">
                            <x-heroicon-o-rectangle-stack class="w-5 h-5 mr-2 text-indigo-500" />
                            Variantes (tailles, portions)
                        </h4>
                        <button type="button" @click="addVariant()"
                                class="text-indigo-600 hover:text-indigo-700 text-sm font-medium flex items-center">
                            <x-heroicon-o-plus class="w-4 h-4 mr-1" />
                            Ajouter
                        </button>
                    </div>
                    <div class="space-y-2">
                        <template x-for="(variant, index) in form.variants" :key="index">
                            <div class="flex items-center space-x-2 p-3 bg-gray-50 rounded-lg">
                                <input type="text" x-model="variant.name"
                                       placeholder="Nom (ex: Grand, Moyen...)"
                                       class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                                <input type="number" x-model="variant.extra_price"
                                       placeholder="Supplément"
                                       min="0" step="100"
                                       class="w-28 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                                <button type="button" @click="form.variants.splice(index, 1)"
                                        class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                    <x-heroicon-o-x-mark class="w-5 h-5" />
                                </button>
                            </div>
                        </template>
                        <div x-show="form.variants.length === 0" class="text-center py-4 text-gray-500 text-sm">
                            Aucune variante ajoutée
                        </div>
                    </div>
                </div>

                <!-- Options -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold text-gray-800 flex items-center">
                            <x-heroicon-o-cog-6-tooth class="w-5 h-5 mr-2 text-indigo-500" />
                            Options de personnalisation
                        </h4>
                        <button type="button" @click="addOption()"
                                class="text-indigo-600 hover:text-indigo-700 text-sm font-medium flex items-center">
                            <x-heroicon-o-plus class="w-4 h-4 mr-1" />
                            Ajouter
                        </button>
                    </div>
                    <div class="space-y-2">
                        <template x-for="(option, index) in form.options" :key="index">
                            <div class="flex items-center space-x-2 p-3 bg-gray-50 rounded-lg">
                                <input type="text" x-model="option.name"
                                       placeholder="Nom (ex: Sans gluten...)"
                                       class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                                <select x-model="option.kind"
                                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                                    <option value="toggle">Toggle</option>
                                    <option value="multiple">Multiple</option>
                                </select>
                                <input type="number" x-model="option.extra_price"
                                       placeholder="Supplément"
                                       min="0" step="100"
                                       class="w-28 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                                <button type="button" @click="form.options.splice(index, 1)"
                                        class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                    <x-heroicon-o-x-mark class="w-5 h-5" />
                                </button>
                            </div>
                        </template>
                        <div x-show="form.options.length === 0" class="text-center py-4 text-gray-500 text-sm">
                            Aucune option ajoutée
                        </div>
                    </div>
                </div>

                <div class="flex space-x-3">
                    <x-ui.button type="button" variant="secondary" class="flex-1" @click="showModal = false">
                        Annuler
                    </x-ui.button>
                    <x-ui.button type="submit" variant="primary" class="flex-1">
                        <x-heroicon-o-check class="w-5 h-5 mr-2" />
                        <span x-text="editingDish ? 'Modifier' : 'Créer'"></span>
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('dishesStore', () => ({
        showModal: false,
        editingDish: null,
        form: {
            name: '',
            description: '',
            price_base: '',
            active: true,
            variants: [],
            options: []
        },

        openModal() {
            this.editingDish = null;
            this.form = {
                name: '',
                description: '',
                price_base: '',
                active: true,
                variants: [],
                options: []
            };
            this.showModal = true;
        },

        async editDish(dishId) {
            try {
                const response = await fetch(`/admin/{{ $tenant->slug }}/dishes/${dishId}`);
                const result = await response.json();

                if (result.success) {
                    this.editingDish = result.dish;
                    this.form = {
                        name: result.dish.name,
                        description: result.dish.description || '',
                        price_base: result.dish.price_base,
                        active: result.dish.active,
                        variants: result.dish.variants.map(v => ({
                            name: v.name,
                            extra_price: v.extra_price
                        })),
                        options: result.dish.options.map(o => ({
                            name: o.name,
                            kind: o.kind,
                            extra_price: o.extra_price
                        }))
                    };
                    this.showModal = true;
                } else {
                    alert('Erreur: ' + result.message);
                }
            } catch (error) {
                alert('Erreur réseau: ' + error.message);
            }
        },

        addVariant() {
            this.form.variants.push({ name: '', extra_price: 0 });
        },

        addOption() {
            this.form.options.push({ name: '', kind: 'toggle', extra_price: 0 });
        },

        async saveDish() {
            const data = {
                ...this.form,
                price_base: parseFloat(this.form.price_base),
                variants: this.form.variants.filter(v => v.name.trim()),
                options: this.form.options.filter(o => o.name.trim())
            };

            const url = this.editingDish
                ? `/admin/{{ $tenant->slug }}/dishes/${this.editingDish.id}`
                : `/admin/{{ $tenant->slug }}/categories/{{ $category->id }}/dishes`;

            const method = this.editingDish ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
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

        async toggleDish(dishId) {
            try {
                const response = await fetch(`/admin/{{ $tenant->slug }}/dishes/${dishId}/toggle`, {
                    method: 'POST',
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
        },

        async deleteDish(dishId, dishName) {
            if (!confirm(`Supprimer le plat "${dishName}" ? Cette action est irréversible.`)) return;

            try {
                const response = await fetch(`/admin/{{ $tenant->slug }}/dishes/${dishId}`, {
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
        },

        async uploadPhoto(event, dishId) {
            const file = event.target.files[0];
            if (!file) return;

            // Vérifier le type de fichier
            if (!file.type.startsWith('image/')) {
                alert('Veuillez sélectionner une image valide.');
                return;
            }

            // Vérifier la taille (max 2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('L\'image ne doit pas dépasser 2 Mo.');
                return;
            }

            const formData = new FormData();
            formData.append('photo', file);

            try {
                const response = await fetch(`/admin/{{ $tenant->slug }}/dishes/${dishId}/photo`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    location.reload();
                } else {
                    alert('Erreur: ' + (result.message || 'Erreur lors de l\'upload'));
                }
            } catch (error) {
                alert('Erreur réseau: ' + error.message);
            }
        },

        async deleteDishPhoto(dishId) {
            if (!confirm('Supprimer la photo de ce plat ?')) return;

            try {
                const response = await fetch(`/admin/{{ $tenant->slug }}/dishes/${dishId}/photo`, {
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
