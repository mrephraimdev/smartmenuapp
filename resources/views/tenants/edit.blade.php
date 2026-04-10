@extends('layouts.superadmin')

@section('title', 'Modifier — ' . $tenant->name)
@section('page-title', 'Modifier le Restaurant')
@section('breadcrumb')
    <a href="{{ route('superadmin.tenants.index') }}" class="text-violet-600 hover:underline">Restaurants</a>
    <span class="mx-1 text-gray-300">/</span>
    <a href="{{ route('superadmin.tenants.show', $tenant) }}" class="text-violet-600 hover:underline">{{ $tenant->name }}</a>
    <span class="mx-1 text-gray-300">/</span>
    <span class="text-gray-500">Modifier</span>
@endsection

@section('content')
    <div class="max-w-4xl mx-auto">
        <form action="{{ route('superadmin.tenants.update', $tenant) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Section: Informations Generales --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-8 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <x-heroicon-o-building-office class="w-6 h-6 text-violet-500" />
                    Informations Generales
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Nom --}}
                    <div class="md:col-span-2">
                        <label for="name" class="block text-base font-semibold text-gray-700 mb-2">
                            Nom du Restaurant / Etablissement *
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name', $tenant->name) }}"
                               class="w-full px-4 py-3 text-base border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent @error('name') border-red-400 @enderror"
                               required>
                        @error('name')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Slug --}}
                    <div class="md:col-span-2">
                        <label for="slug" class="block text-base font-semibold text-gray-700 mb-2">
                            Slug (URL unique) *
                        </label>
                        <input type="text" id="slug" name="slug" value="{{ old('slug', $tenant->slug) }}"
                               class="w-full px-4 py-3 text-base border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent @error('slug') border-red-400 @enderror"
                               required>
                        <p class="mt-1.5 text-sm text-gray-400">Utilise dans l'URL : /admin/{slug}/dashboard</p>
                        @error('slug')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Type --}}
                    <div>
                        <label for="type" class="block text-base font-semibold text-gray-700 mb-2">
                            Type d'etablissement *
                        </label>
                        <select id="type" name="type"
                                class="w-full px-4 py-3 text-base border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent @error('type') border-red-400 @enderror"
                                required>
                            <option value="restaurant" {{ old('type', $tenant->type) == 'restaurant' ? 'selected' : '' }}>Restaurant</option>
                            <option value="mariage" {{ old('type', $tenant->type) == 'mariage' ? 'selected' : '' }}>Mariage / Evenement</option>
                        </select>
                        @error('type')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Statut actif --}}
                    <div>
                        <label class="block text-base font-semibold text-gray-700 mb-2">
                            Statut
                        </label>
                        <div class="flex items-center mt-3">
                            <input type="checkbox" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', $tenant->is_active) ? 'checked' : '' }}
                                   class="h-5 w-5 text-violet-600 focus:ring-violet-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-3 block text-base text-gray-700">
                                Tenant actif
                            </label>
                        </div>
                    </div>

                    {{-- Devise --}}
                    <div>
                        <label for="currency" class="block text-base font-semibold text-gray-700 mb-2">
                            Devise *
                        </label>
                        <select id="currency" name="currency"
                                class="w-full px-4 py-3 text-base border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent @error('currency') border-red-400 @enderror"
                                required>
                            <option value="XOF" {{ old('currency', $tenant->currency) == 'XOF' ? 'selected' : '' }}>XOF (FCFA)</option>
                            <option value="FCFA" {{ old('currency', $tenant->currency) == 'FCFA' ? 'selected' : '' }}>FCFA</option>
                            <option value="EUR" {{ old('currency', $tenant->currency) == 'EUR' ? 'selected' : '' }}>EUR (Euro)</option>
                            <option value="USD" {{ old('currency', $tenant->currency) == 'USD' ? 'selected' : '' }}>USD (Dollar)</option>
                        </select>
                        @error('currency')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Langue --}}
                    <div>
                        <label for="locale" class="block text-base font-semibold text-gray-700 mb-2">
                            Langue *
                        </label>
                        <select id="locale" name="locale"
                                class="w-full px-4 py-3 text-base border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent @error('locale') border-red-400 @enderror"
                                required>
                            <option value="fr" {{ old('locale', $tenant->locale) == 'fr' ? 'selected' : '' }}>Francais</option>
                            <option value="en" {{ old('locale', $tenant->locale) == 'en' ? 'selected' : '' }}>English</option>
                        </select>
                        @error('locale')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Section: Informations de Contact --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-8 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <x-heroicon-o-phone class="w-6 h-6 text-emerald-500" />
                    Informations de Contact
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Adresse --}}
                    <div class="md:col-span-2">
                        <label for="address" class="block text-base font-semibold text-gray-700 mb-2">
                            Adresse
                        </label>
                        <input type="text" id="address" name="address" value="{{ old('address', $tenant->address) }}"
                               class="w-full px-4 py-3 text-base border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent @error('address') border-red-400 @enderror"
                               placeholder="Ex: 123 Rue de la Paix, Dakar, Senegal">
                        <p class="mt-1.5 text-sm text-gray-400">Adresse complete visible par les clients</p>
                        @error('address')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Telephone --}}
                    <div>
                        <label for="phone" class="block text-base font-semibold text-gray-700 mb-2">
                            Telephone
                        </label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone', $tenant->phone) }}"
                               class="w-full px-4 py-3 text-base border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent @error('phone') border-red-400 @enderror"
                               placeholder="Ex: +221 77 123 45 67">
                        @error('phone')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-base font-semibold text-gray-700 mb-2">
                            Email
                        </label>
                        <input type="email" id="email" name="email" value="{{ old('email', $tenant->email) }}"
                               class="w-full px-4 py-3 text-base border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent @error('email') border-red-400 @enderror"
                               placeholder="Ex: contact@restaurant.com">
                        @error('email')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Section: Images & Branding --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-8 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <x-heroicon-o-photo class="w-6 h-6 text-purple-500" />
                    Images & Branding
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Logo --}}
                    <div>
                        <label for="logo" class="block text-base font-semibold text-gray-700 mb-2">
                            Logo
                        </label>

                        @if($tenant->logo_url)
                        <div class="mb-3 flex items-center gap-4">
                            <img src="{{ $tenant->logo_url }}" alt="Logo actuel" class="h-16 w-16 object-cover rounded-full border-2 border-gray-200">
                            <span class="text-sm text-gray-400">Logo actuel</span>
                        </div>
                        @endif

                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-200 border-dashed rounded-xl hover:border-violet-400 transition-colors bg-gray-50/50">
                            <div class="space-y-1 text-center">
                                <x-heroicon-o-photo class="mx-auto h-10 w-10 text-gray-300" />
                                <div class="flex text-sm text-gray-500">
                                    <label for="logo" class="relative cursor-pointer rounded-md font-semibold text-violet-600 hover:text-violet-500 focus-within:outline-none">
                                        <span>{{ $tenant->logo_url ? 'Changer le logo' : 'Telecharger un fichier' }}</span>
                                        <input id="logo" name="logo" type="file" class="sr-only" accept="image/*">
                                    </label>
                                </div>
                                <p class="text-xs text-gray-400">PNG, JPG, GIF jusqu'a 2MB</p>
                            </div>
                        </div>
                        <div id="logo-preview" class="mt-3 hidden">
                            <img src="" alt="Apercu logo" class="h-20 w-20 object-cover rounded-full mx-auto border-2 border-gray-200">
                        </div>
                        @error('logo')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Image de Couverture --}}
                    <div>
                        <label for="cover" class="block text-base font-semibold text-gray-700 mb-2">
                            Image de Couverture
                        </label>

                        @if($tenant->cover_url)
                        <div class="mb-3">
                            <img src="{{ $tenant->cover_url }}" alt="Couverture actuelle" class="w-full h-20 object-cover rounded-xl border-2 border-gray-200">
                            <span class="text-sm text-gray-400 mt-1 block">Couverture actuelle</span>
                        </div>
                        @endif

                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-200 border-dashed rounded-xl hover:border-violet-400 transition-colors bg-gray-50/50">
                            <div class="space-y-1 text-center">
                                <x-heroicon-o-photo class="mx-auto h-10 w-10 text-gray-300" />
                                <div class="flex text-sm text-gray-500">
                                    <label for="cover" class="relative cursor-pointer rounded-md font-semibold text-violet-600 hover:text-violet-500 focus-within:outline-none">
                                        <span>{{ $tenant->cover_url ? 'Changer la couverture' : 'Telecharger un fichier' }}</span>
                                        <input id="cover" name="cover" type="file" class="sr-only" accept="image/*">
                                    </label>
                                </div>
                                <p class="text-xs text-gray-400">PNG, JPG jusqu'a 5MB (recommande: 1920x600)</p>
                            </div>
                        </div>
                        <div id="cover-preview" class="mt-3 hidden">
                            <img src="" alt="Apercu couverture" class="w-full h-24 object-cover rounded-xl border-2 border-gray-200">
                        </div>
                        @error('cover')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Boutons --}}
            <div class="flex justify-end gap-3">
                <a href="{{ route('superadmin.tenants.index') }}"
                   class="px-6 py-3 text-base font-semibold text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">
                    Annuler
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-3 text-base font-semibold text-white bg-violet-600 rounded-xl hover:bg-violet-700 transition-colors shadow-sm">
                    <x-heroicon-o-check class="w-5 h-5" />
                    Mettre a jour
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    // Previsualisation du logo
    document.getElementById('logo').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('logo-preview');
                preview.querySelector('img').src = e.target.result;
                preview.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    });

    // Previsualisation de la couverture
    document.getElementById('cover').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('cover-preview');
                preview.querySelector('img').src = e.target.result;
                preview.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    });
</script>
@endpush
