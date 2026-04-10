@extends('layouts.admin')

@section('title', 'Nouvelle Table')
@section('page-title', 'Nouvelle Table')
@section('breadcrumb')
    <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="hover:text-amber-500">Dashboard</a>
    <span class="mx-2">/</span>
    <a href="{{ route('admin.tables.index', $tenant->slug) }}" class="hover:text-amber-500">Tables</a>
    <span class="mx-2">/</span>
    <span>Nouvelle</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Formulaire -->
    <div class="bg-white rounded-2xl shadow-sm p-6 md:p-8">
        <form action="{{ route('admin.tables.store', $tenant->slug) }}" method="POST" id="createTableForm">
            @csrf

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
                    <input type="text" id="code" name="code" required
                           class="w-full px-4 py-3 text-base border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500"
                           placeholder="Ex: A01, T05, VIP1..."
                           maxlength="10"
                           value="{{ old('code') }}">
                    @error('code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="text-sm text-gray-500 mt-1">Code unique pour identifier la table (max 10 caracteres)</p>
                </div>

                <!-- Nom de la table -->
                <div>
                    <label for="label" class="block text-base font-semibold text-gray-700 mb-2">
                        Nom de la table <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="label" name="label" required
                           class="w-full px-4 py-3 text-base border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500"
                           placeholder="Ex: Table 1, Terrasse A, VIP..."
                           maxlength="255"
                           value="{{ old('label') }}">
                    @error('label')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="text-sm text-gray-500 mt-1">Nom affiche pour la table</p>
                </div>

                <!-- Capacite -->
                <div>
                    <label for="capacity" class="block text-base font-semibold text-gray-700 mb-2">
                        Capacite <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="capacity" name="capacity" required min="1" max="50"
                           class="w-full px-4 py-3 text-base border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500"
                           placeholder="4"
                           value="{{ old('capacity', 4) }}">
                    @error('capacity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="text-sm text-gray-500 mt-1">Nombre de personnes maximum (1-50)</p>
                </div>

                <!-- Statut actif -->
                <div>
                    <label class="block text-base font-semibold text-gray-700 mb-2">
                        Statut
                    </label>
                    <div class="flex items-center mt-3">
                        <input type="checkbox" id="is_active" name="is_active" value="1" checked
                               class="h-5 w-5 text-amber-500 focus:ring-amber-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-3 block text-base text-gray-900">
                            Table active
                        </label>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">Les tables inactives ne peuvent pas recevoir de commandes</p>
                </div>
            </div>

            <!-- Boutons -->
            <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.tables.index', $tenant->slug) }}"
                   class="rounded-xl px-6 py-3 text-base font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors inline-flex items-center">
                    <x-heroicon-o-x-mark class="w-5 h-5 mr-2" />Annuler
                </a>
                <button type="submit"
                        class="rounded-xl px-6 py-3 text-base font-semibold bg-amber-500 text-white hover:bg-amber-600 transition-colors inline-flex items-center">
                    <x-heroicon-o-check class="w-5 h-5 mr-2" />Creer la table
                </button>
            </div>
        </form>
    </div>

    <!-- Informations supplementaires -->
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mt-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <x-heroicon-o-information-circle class="w-5 h-5 text-amber-500" />
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-semibold text-amber-800">Informations importantes</h3>
                <div class="mt-2 text-sm text-amber-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Le code doit etre unique pour ce restaurant</li>
                        <li>Le QR code sera genere automatiquement avec ce code</li>
                        <li>Vous pouvez modifier ces informations plus tard</li>
                        <li>Pour creer plusieurs tables rapidement, utilisez la fonction "Generer"</li>
                    </ul>
                </div>
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

    // Validation du formulaire
    document.getElementById('createTableForm').addEventListener('submit', function(e) {
        const code = document.getElementById('code').value.trim();
        const label = document.getElementById('label').value.trim();
        const capacity = document.getElementById('capacity').value;

        if (!code) {
            alert('Le code de la table est obligatoire.');
            e.preventDefault();
            return;
        }

        if (!label) {
            alert('Le nom de la table est obligatoire.');
            e.preventDefault();
            return;
        }

        if (!capacity || capacity < 1 || capacity > 50) {
            alert('La capacite doit etre comprise entre 1 et 50 personnes.');
            e.preventDefault();
            return;
        }
    });
</script>
@endpush
@endsection
