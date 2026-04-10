@extends('layouts.admin')

@section('title', 'Nouveau membre')
@section('page-title', 'Ajouter un membre')
@section('breadcrumb')
    <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="hover:text-amber-500">Dashboard</a>
    <span class="mx-2">/</span>
    <a href="{{ route('admin.staff.index', $tenant->slug) }}" class="hover:text-amber-500">Personnel</a>
    <span class="mx-2">/</span>
    <span>Nouveau</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-800 flex items-center">
                <x-heroicon-o-user-plus class="w-5 h-5 mr-2 text-amber-500" />
                Nouveau membre du personnel
            </h2>
        </div>

        <form method="POST" action="{{ route('admin.staff.store', $tenant->slug) }}" class="p-6 space-y-6">
            @csrf

            {{-- Nom --}}
            <div>
                <label for="name" class="block text-base font-semibold text-gray-700 mb-2">
                    Nom complet <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                    class="w-full px-4 py-3 text-base border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 @error('name') border-red-500 @enderror"
                    placeholder="Ex: Jean Dupont">
                @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Identifiant --}}
            <div>
                <label for="username" class="block text-base font-semibold text-gray-700 mb-2">
                    Identifiant <span class="text-red-500">*</span>
                </label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required
                    class="w-full px-4 py-3 text-base border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 @error('username') border-red-500 @enderror"
                    placeholder="Ex: jean.dupont" autocomplete="off">
                <p class="mt-1 text-xs text-gray-400">Lettres, chiffres, tirets et underscores uniquement. Utilisé pour se connecter.</p>
                @error('username')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Role --}}
            <div>
                <label for="role" class="block text-base font-semibold text-gray-700 mb-2">
                    Role <span class="text-red-500">*</span>
                </label>
                <select id="role" name="role" required
                    class="w-full px-4 py-3 text-base border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 @error('role') border-red-500 @enderror">
                    <option value="">Selectionner un role</option>
                    @foreach($roles as $role)
                    <option value="{{ $role->value }}" {{ old('role') == $role->value ? 'selected' : '' }}>
                        {{ $role->label() }} - {{ $role->description() }}
                    </option>
                    @endforeach
                </select>
                @error('role')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Mot de passe --}}
            <div>
                <label for="password" class="block text-base font-semibold text-gray-700 mb-2">
                    Mot de passe <span class="text-red-500">*</span>
                </label>
                <input type="password" id="password" name="password" required
                    class="w-full px-4 py-3 text-base border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 @error('password') border-red-500 @enderror"
                    placeholder="Minimum 8 caracteres">
                @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirmation --}}
            <div>
                <label for="password_confirmation" class="block text-base font-semibold text-gray-700 mb-2">
                    Confirmer le mot de passe <span class="text-red-500">*</span>
                </label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                    class="w-full px-4 py-3 text-base border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                    placeholder="Repetez le mot de passe">
            </div>

            {{-- Buttons --}}
            <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.staff.index', $tenant->slug) }}"
                   class="px-6 py-3 text-base font-semibold text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition">
                    Annuler
                </a>
                <button type="submit"
                        class="px-6 py-3 text-base font-semibold bg-amber-500 text-white rounded-xl hover:bg-amber-600 transition flex items-center">
                    <x-heroicon-o-check class="w-5 h-5 mr-2" />
                    Creer le compte
                </button>
            </div>
        </form>
    </div>

    {{-- Info box --}}
    <div class="mt-6 bg-amber-50 border border-amber-200 rounded-xl p-4">
        <div class="flex items-start">
            <x-heroicon-o-information-circle class="w-5 h-5 text-amber-500 mr-3 mt-0.5 flex-shrink-0" />
            <div class="text-sm text-amber-800">
                <p class="font-medium mb-1">Information</p>
                <p>Le membre pourra se connecter avec son identifiant et mot de passe sur la page de connexion. Il sera automatiquement redirige vers son espace de travail selon son role.</p>
            </div>
        </div>
    </div>
</div>
@endsection
