@extends('layouts.admin')

@section('title', 'Modifier Table')
@section('page-title', 'Modifier Table')
@section('breadcrumb')
    <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="hover:text-amber-500">Dashboard</a>
    <span class="mx-2">/</span>
    <a href="{{ route('admin.tables.index', $tenant->slug) }}" class="hover:text-amber-500">Tables</a>
    <span class="mx-2">/</span>
    <a href="{{ route('admin.tables.show', [$tenant->slug, $table->id]) }}" class="hover:text-amber-500">{{ $table->label }}</a>
    <span class="mx-2">/</span>
    <span>Modifier</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Formulaire -->
    <div class="bg-white rounded-2xl shadow-sm p-6 md:p-8">
        <form action="{{ route('admin.tables.update', [$tenant->slug, $table->id]) }}" method="POST">
            @csrf
            @method('PUT')

            @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                <ul class="text-sm text-red-700 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Code de la table -->
                <div>
                    <label for="code" class="block text-base font-semibold text-gray-700 mb-2">
                        Code de la table <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="code" name="code" required maxlength="10"
                           class="w-full px-4 py-3 text-base border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500"
                           value="{{ old('code', $table->code) }}">
                    @error('code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Code unique pour identifier la table</p>
                </div>

                <!-- Nom de la table -->
                <div>
                    <label for="label" class="block text-base font-semibold text-gray-700 mb-2">
                        Nom de la table <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="label" name="label" required maxlength="255"
                           class="w-full px-4 py-3 text-base border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500"
                           value="{{ old('label', $table->label) }}">
                    @error('label')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Nom affiche pour les clients</p>
                </div>

                <!-- Capacite -->
                <div>
                    <label for="capacity" class="block text-base font-semibold text-gray-700 mb-2">
                        Capacite <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="capacity" name="capacity" required min="1" max="50"
                           class="w-full px-4 py-3 text-base border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500"
                           value="{{ old('capacity', $table->capacity) }}">
                    @error('capacity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Nombre de personnes maximum</p>
                </div>

                <!-- Statut actif -->
                <div>
                    <label class="block text-base font-semibold text-gray-700 mb-2">
                        Statut
                    </label>
                    <div class="flex items-center mt-3">
                        <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $table->is_active) ? 'checked' : '' }}
                               class="h-5 w-5 text-amber-500 focus:ring-amber-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-3 block text-base text-gray-900">
                            Table active
                        </label>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Desactivez pour masquer la table temporairement</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.tables.index', $tenant->slug) }}"
                   class="rounded-xl px-6 py-3 text-base font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors inline-flex items-center">
                    Annuler
                </a>
                <button type="submit"
                        class="rounded-xl px-6 py-3 text-base font-semibold bg-amber-500 text-white hover:bg-amber-600 transition-colors inline-flex items-center">
                    <x-heroicon-o-check class="w-5 h-5 mr-2" />Mettre a jour
                </button>
            </div>
        </form>
    </div>

    <!-- Informations sur la table -->
    <div class="bg-white rounded-2xl shadow-sm p-6 mt-6">
        <h3 class="text-base font-semibold text-gray-800 mb-4 flex items-center">
            <x-heroicon-o-information-circle class="w-5 h-5 mr-2 text-amber-500" />Informations de la table
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <span class="font-semibold text-gray-600">Creee le:</span>
                <span class="text-gray-900">{{ $table->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div>
                <span class="font-semibold text-gray-600">Derniere modification:</span>
                <span class="text-gray-900">{{ $table->updated_at->format('d/m/Y H:i') }}</span>
            </div>
            <div>
                <span class="font-semibold text-gray-600">Commandes associees:</span>
                <span class="text-gray-900">{{ $table->orders->count() }}</span>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-format du code en majuscules
    document.getElementById('code').addEventListener('input', function(e) {
        e.target.value = e.target.value.toUpperCase();
    });
</script>
@endpush
@endsection
