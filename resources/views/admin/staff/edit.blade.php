@extends('layouts.admin')

@section('title', 'Modifier ' . $user->name)
@section('page-title', 'Modifier le membre')
@section('breadcrumb')
    <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="hover:text-amber-500">Dashboard</a>
    <span class="mx-2">/</span>
    <a href="{{ route('admin.staff.index', $tenant->slug) }}" class="hover:text-amber-500">Personnel</a>
    <span class="mx-2">/</span>
    <span>{{ $user->name }}</span>
    <span class="mx-2">/</span>
    <span>Modifier</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- User header --}}
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-amber-600 rounded-full flex items-center justify-center text-white font-bold mr-4">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-800">{{ $user->name }}</h2>
                    <p class="text-sm text-gray-500">@{{ $user->username ?? '—' }}</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.staff.update', [$tenant->slug, $user]) }}" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            {{-- Nom --}}
            <div>
                <label for="name" class="block text-base font-semibold text-gray-700 mb-2">
                    Nom complet <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
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
                <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}" required
                    class="w-full px-4 py-3 text-base border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 @error('username') border-red-500 @enderror"
                    placeholder="Ex: jean.dupont" autocomplete="off">
                <p class="mt-1 text-xs text-gray-400">Lettres, chiffres, tirets et underscores uniquement.</p>
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
                    @foreach($roles as $role)
                    <option value="{{ $role->value }}" {{ old('role', $user->role) == $role->value ? 'selected' : '' }}>
                        {{ $role->label() }} - {{ $role->description() }}
                    </option>
                    @endforeach
                </select>
                @error('role')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password (optional) --}}
            <div class="pt-4 border-t border-gray-100">
                <p class="text-sm text-gray-500 mb-4">Laissez vide si vous ne souhaitez pas changer le mot de passe</p>

                <div class="space-y-4">
                    <div>
                        <label for="password" class="block text-base font-semibold text-gray-700 mb-2">
                            Nouveau mot de passe
                        </label>
                        <input type="password" id="password" name="password"
                            class="w-full px-4 py-3 text-base border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 @error('password') border-red-500 @enderror"
                            placeholder="Minimum 8 caracteres">
                        @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-base font-semibold text-gray-700 mb-2">
                            Confirmer le nouveau mot de passe
                        </label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            class="w-full px-4 py-3 text-base border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                            placeholder="Repetez le mot de passe">
                    </div>
                </div>
            </div>

            {{-- Buttons --}}
            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <form method="POST" action="{{ route('admin.staff.destroy', [$tenant->slug, $user]) }}"
                      onsubmit="return confirm('Etes-vous sur de vouloir supprimer ce membre ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-xl transition flex items-center text-sm font-medium">
                        <x-heroicon-o-trash class="w-5 h-5 mr-1" />
                        Supprimer
                    </button>
                </form>

                <div class="flex space-x-4">
                    <a href="{{ route('admin.staff.index', $tenant->slug) }}"
                       class="px-6 py-3 text-base font-semibold text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition">
                        Annuler
                    </a>
                    <button type="submit"
                            class="px-6 py-3 text-base font-semibold bg-amber-500 text-white rounded-xl hover:bg-amber-600 transition flex items-center">
                        <x-heroicon-o-check class="w-5 h-5 mr-2" />
                        Enregistrer
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Account info --}}
    <div class="mt-6 bg-gray-50 border border-gray-200 rounded-xl p-4">
        <h3 class="text-sm font-medium text-gray-700 mb-2">Informations du compte</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-500">Cree le:</span>
                <span class="text-gray-800 ml-1">{{ $user->created_at->format('d/m/Y a H:i') }}</span>
            </div>
            <div>
                <span class="text-gray-500">Derniere modification:</span>
                <span class="text-gray-800 ml-1">{{ $user->updated_at->format('d/m/Y a H:i') }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
