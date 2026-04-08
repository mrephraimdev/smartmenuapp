@extends('layouts.admin')

@section('title', 'Gestion des Tables')
@section('page-title', 'Gestion des Tables')
@section('breadcrumb')
    <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="hover:text-indigo-600">Dashboard</a>
    <span class="mx-2">/</span>
    <span>Tables</span>
@endsection

@section('content')
<div x-data="tablesStore()">
    <!-- Actions Bar -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="flex items-center space-x-3">
            <x-ui.button variant="primary" @click="showGenerateModal = true">
                <x-heroicon-o-sparkles class="w-5 h-5 mr-2" />
                Générer
            </x-ui.button>
            <a href="{{ route('admin.tables.create', $tenant->slug) }}">
                <x-ui.button variant="success">
                    <x-heroicon-o-plus class="w-5 h-5 mr-2" />
                    Nouvelle Table
                </x-ui.button>
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <x-ui.stat-card
            title="Total Tables"
            :value="$tables->count()"
            icon="table-cells"
            color="blue"
        />
        <x-ui.stat-card
            title="Tables Actives"
            :value="$tables->where('is_active', true)->count()"
            icon="check-circle"
            color="green"
        />
        <x-ui.stat-card
            title="Tables Inactives"
            :value="$tables->where('is_active', false)->count()"
            icon="x-circle"
            color="red"
        />
        <x-ui.stat-card
            title="Places Totales"
            :value="$tables->sum('capacity')"
            icon="users"
            color="purple"
        />
    </div>

    <!-- Tables List -->
    <x-ui.card :noPadding="true">
        <x-slot name="header">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <x-heroicon-o-table-cells class="w-6 h-6 text-indigo-500" />
                    <h2 class="text-lg font-semibold text-gray-800">Vos Tables</h2>
                </div>
                <span class="text-sm text-gray-500">{{ $tables->count() }} tables</span>
            </div>
        </x-slot>

        @if($tables->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nom</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Capacité</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($tables as $table)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-2">
                                        <span class="font-semibold text-gray-900">{{ $table->code }}</span>
                                        <a href="{{ route('qrcode.show', [$tenant->id, $table->code]) }}" target="_blank"
                                           class="text-indigo-500 hover:text-indigo-700 transition-colors">
                                            <x-heroicon-o-qr-code class="w-5 h-5" />
                                        </a>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-gray-700">{{ $table->label }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center text-gray-600">
                                        <x-heroicon-o-users class="w-4 h-4 mr-1" />
                                        {{ $table->capacity }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-ui.badge :variant="$table->is_active ? 'success' : 'danger'">
                                        {{ $table->is_active ? 'Active' : 'Inactive' }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('admin.tables.show', [$tenant->slug, $table->id]) }}"
                                           class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all"
                                           title="Voir">
                                            <x-heroicon-o-eye class="w-5 h-5" />
                                        </a>
                                        <a href="{{ route('admin.tables.edit', [$tenant->slug, $table->id]) }}"
                                           class="p-2 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all"
                                           title="Modifier">
                                            <x-heroicon-o-pencil class="w-5 h-5" />
                                        </a>
                                        <button @click="toggleTable({{ $table->id }}, {{ $table->is_active ? 'true' : 'false' }})"
                                                class="p-2 text-gray-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all"
                                                title="{{ $table->is_active ? 'Désactiver' : 'Activer' }}">
                                            @if($table->is_active)
                                                <x-heroicon-o-pause class="w-5 h-5" />
                                            @else
                                                <x-heroicon-o-play class="w-5 h-5" />
                                            @endif
                                        </button>
                                        <button @click="deleteTable({{ $table->id }}, '{{ $table->label }}')"
                                                class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all"
                                                title="Supprimer">
                                            <x-heroicon-o-trash class="w-5 h-5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-ui.empty-state
                icon="table"
                title="Aucune table trouvée"
                description="Commencez par créer votre première table ou générez-en plusieurs automatiquement."
            >
                <x-slot name="action">
                    <div class="flex items-center justify-center space-x-3">
                        <x-ui.button variant="outline" @click="showGenerateModal = true">
                            <x-heroicon-o-sparkles class="w-5 h-5 mr-2" />
                            Générer
                        </x-ui.button>
                        <a href="{{ route('admin.tables.create', $tenant->slug) }}">
                            <x-ui.button variant="primary">
                                <x-heroicon-o-plus class="w-5 h-5 mr-2" />
                                Créer une table
                            </x-ui.button>
                        </a>
                    </div>
                </x-slot>
            </x-ui.empty-state>
        @endif
    </x-ui.card>

    <!-- Generate Modal -->
    <div x-show="showGenerateModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
         @click.self="showGenerateModal = false">
        <div x-show="showGenerateModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-sparkles class="w-5 h-5 text-purple-600" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Générer des Tables</h3>
                </div>
                <button @click="showGenerateModal = false" class="text-gray-400 hover:text-gray-600">
                    <x-heroicon-o-x-mark class="w-6 h-6" />
                </button>
            </div>

            <form action="{{ route('admin.tables.generate', $tenant->slug) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <x-ui.input
                        label="Préfixe"
                        name="prefix"
                        value="T"
                        required
                        maxlength="5"
                        placeholder="Ex: A, B, T..."
                    />

                    <x-ui.input
                        label="Numéro de départ"
                        name="start_number"
                        type="number"
                        value="1"
                        required
                        min="1"
                    />

                    <x-ui.input
                        label="Nombre de tables"
                        name="count"
                        type="number"
                        value="10"
                        required
                        min="1"
                        max="50"
                    />

                    <x-ui.input
                        label="Capacité par table"
                        name="capacity"
                        type="number"
                        value="4"
                        required
                        min="1"
                        max="50"
                    />
                </div>

                <div class="flex space-x-3 mt-6">
                    <x-ui.button type="button" variant="secondary" class="flex-1" @click="showGenerateModal = false">
                        Annuler
                    </x-ui.button>
                    <x-ui.button type="submit" variant="primary" class="flex-1">
                        <x-heroicon-o-sparkles class="w-5 h-5 mr-2" />
                        Générer
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('tablesStore', () => ({
        showGenerateModal: false,

        async toggleTable(tableId, currentStatus) {
            if (!confirm(currentStatus ? 'Désactiver cette table ?' : 'Activer cette table ?')) return;

            try {
                const response = await fetch(`{{ route('admin.tables.index', $tenant->slug) }}/${tableId}/toggle`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erreur: ' + data.message);
                }
            } catch (error) {
                alert('Erreur réseau: ' + error.message);
            }
        },

        deleteTable(tableId, tableName) {
            if (!confirm(`Êtes-vous sûr de vouloir supprimer la table "${tableName}" ?`)) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ route('admin.tables.index', $tenant->slug) }}/${tableId}`;

            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';

            const csrfField = document.createElement('input');
            csrfField.type = 'hidden';
            csrfField.name = '_token';
            csrfField.value = document.querySelector('meta[name="csrf-token"]').content;

            form.appendChild(methodField);
            form.appendChild(csrfField);
            document.body.appendChild(form);
            form.submit();
        }
    }));
});
</script>
@endpush
@endsection
