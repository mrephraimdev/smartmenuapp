@extends('layouts.superadmin')

@section('title', 'Nouvel Utilisateur')
@section('page-title', 'Nouvel Utilisateur')
@section('breadcrumb')
    <a href="{{ route('superadmin.users.index') }}" class="text-violet-600 hover:text-violet-700">Utilisateurs</a>
    <span class="mx-1 text-gray-300">/</span>
    <span class="text-gray-500">Nouveau</span>
@endsection

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-8">
            <form action="{{ route('superadmin.users.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Nom --}}
                    <div class="md:col-span-2">
                        <label for="name" class="block text-base font-semibold text-gray-700 mb-2">
                            Nom complet *
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                               class="w-full px-4 py-3 text-base border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500 @error('name') border-red-400 ring-1 ring-red-400 @enderror"
                               placeholder="Jean Dupont"
                               required>
                        @error('name')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="md:col-span-2">
                        <label for="email" class="block text-base font-semibold text-gray-700 mb-2">
                            Adresse email *
                        </label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               class="w-full px-4 py-3 text-base border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500 @error('email') border-red-400 ring-1 ring-red-400 @enderror"
                               placeholder="jean.dupont@exemple.com"
                               required>
                        @error('email')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Mot de passe --}}
                    <div>
                        <label for="password" class="block text-base font-semibold text-gray-700 mb-2">
                            Mot de passe *
                        </label>
                        <input type="password" id="password" name="password"
                               class="w-full px-4 py-3 text-base border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500 @error('password') border-red-400 ring-1 ring-red-400 @enderror"
                               placeholder="Minimum 8 caracteres"
                               required>
                        @error('password')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Confirmation mot de passe --}}
                    <div>
                        <label for="password_confirmation" class="block text-base font-semibold text-gray-700 mb-2">
                            Confirmer le mot de passe *
                        </label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="w-full px-4 py-3 text-base border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500"
                               placeholder="Confirmez le mot de passe"
                               required>
                    </div>

                    {{-- Role --}}
                    <div class="md:col-span-2">
                        <label for="role" class="block text-base font-semibold text-gray-700 mb-2">
                            Role *
                        </label>
                        <select id="role" name="role"
                                class="w-full px-4 py-3 text-base border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500 @error('role') border-red-400 ring-1 ring-red-400 @enderror"
                                required>
                            <option value="">-- Selectionner un role --</option>
                            @foreach(\App\Enums\UserRole::cases() as $role)
                            <option value="{{ $role->value }}" {{ old('role') == $role->value ? 'selected' : '' }}>
                                {{ $role->label() }} - {{ $role->description() }}
                            </option>
                            @endforeach
                        </select>
                        @error('role')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        {{-- Role descriptions --}}
                        <div class="mt-3 p-4 bg-gray-50 rounded-xl text-sm">
                            <p class="font-semibold text-gray-700 mb-2">Description des roles :</p>
                            <ul class="space-y-1.5 text-gray-600">
                                <li><span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold bg-red-100 text-red-800 mr-2">SUPER_ADMIN</span> Acces global a tous les tenants</li>
                                <li><span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold bg-purple-100 text-purple-800 mr-2">ADMIN</span> Gestion complete de son restaurant</li>
                                <li><span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold bg-green-100 text-green-800 mr-2">CAISSIER</span> POS, paiements, encaissements</li>
                                <li><span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold bg-orange-100 text-orange-800 mr-2">CHEF</span> Cuisine, preparation des commandes (KDS)</li>
                                <li><span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold bg-blue-100 text-blue-800 mr-2">SERVEUR</span> Service en salle, commandes, tables</li>
                                <li><span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold bg-gray-100 text-gray-800 mr-2">CLIENT</span> Menu public et commandes</li>
                            </ul>
                        </div>
                    </div>

                    {{-- Tenant --}}
                    <div class="md:col-span-2" id="tenant-field">
                        <label for="tenant_id" class="block text-base font-semibold text-gray-700 mb-2">
                            Restaurant assigne
                        </label>
                        <select id="tenant_id" name="tenant_id"
                                class="w-full px-4 py-3 text-base border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500 @error('tenant_id') border-red-400 ring-1 ring-red-400 @enderror">
                            <option value="">-- Aucun (Super Admin) --</option>
                            @foreach(\App\Models\Tenant::where('is_active', true)->orderBy('name')->get() as $tenant)
                            <option value="{{ $tenant->id }}" {{ old('tenant_id') == $tenant->id ? 'selected' : '' }}>
                                {{ $tenant->name }} ({{ $tenant->slug }})
                            </option>
                            @endforeach
                        </select>
                        <p class="mt-1.5 text-sm text-gray-500" id="tenant-help">
                            Le SUPER_ADMIN n'a pas besoin de tenant. Les autres roles doivent etre associes a un restaurant.
                        </p>
                        @error('tenant_id')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="mt-8 flex justify-end gap-3">
                    <a href="{{ route('superadmin.users.index') }}"
                       class="px-6 py-3 text-base font-semibold text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">
                        Annuler
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-3 text-base font-semibold text-white bg-violet-600 rounded-xl hover:bg-violet-700 transition-colors shadow-sm">
                        <x-heroicon-o-check class="w-5 h-5" />
                        Creer l'utilisateur
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('role').addEventListener('change', function() {
        const tenantField = document.getElementById('tenant-field');
        const tenantSelect = document.getElementById('tenant_id');
        const tenantHelp = document.getElementById('tenant-help');

        if (this.value === 'SUPER_ADMIN') {
            tenantSelect.value = '';
            tenantHelp.textContent = 'Le Super Admin a acces a tous les restaurants.';
        } else if (this.value === 'CLIENT') {
            tenantSelect.value = '';
            tenantHelp.textContent = 'Les clients n\'ont pas besoin d\'etre assignes a un restaurant.';
        } else if (this.value) {
            tenantHelp.textContent = 'Ce role doit etre associe a un restaurant.';
        } else {
            tenantHelp.textContent = 'Le SUPER_ADMIN n\'a pas besoin de tenant. Les autres roles doivent etre associes a un restaurant.';
        }
    });
</script>
@endpush
