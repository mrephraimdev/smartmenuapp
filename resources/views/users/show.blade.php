@extends('layouts.superadmin')

@section('title', $user->name)
@section('page-title', 'Details de l\'utilisateur')
@section('breadcrumb')
    <a href="{{ route('superadmin.users.index') }}" class="text-violet-600 hover:text-violet-700">Utilisateurs</a>
    <span class="mx-1 text-gray-300">/</span>
    <span class="text-gray-500">{{ $user->name }}</span>
@endsection

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Informations de l'utilisateur</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                {{-- Nom --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">Nom complet</label>
                    <p class="text-base font-medium text-gray-900">{{ $user->name }}</p>
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">Adresse email</label>
                    <p class="text-base font-medium text-gray-900">{{ $user->email }}</p>
                </div>

                {{-- Tenant --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">Restaurant assigne</label>
                    <p class="text-base font-medium text-gray-900">
                        @if($user->tenant)
                            {{ $user->tenant->name }}
                            <span class="text-sm text-gray-400 ml-1">({{ $user->tenant->slug }})</span>
                        @else
                            <span class="text-gray-400">Aucun tenant assigne</span>
                        @endif
                    </p>
                </div>

                {{-- Role --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">Role</label>
                    <div class="mt-0.5">
                        @if($user->role)
                            @php $roleEnum = \App\Enums\UserRole::tryFrom($user->role) @endphp
                            @if($roleEnum)
                            <span class="inline-flex px-3 py-1 rounded-full text-sm font-bold {{ $roleEnum->badgeClass() }}">
                                {{ $roleEnum->label() }}
                            </span>
                            <p class="mt-1.5 text-sm text-gray-500">{{ $roleEnum->description() }}</p>
                            @else
                            <span class="inline-flex px-3 py-1 rounded-full text-sm font-bold bg-gray-100 text-gray-800">
                                {{ $user->role }}
                            </span>
                            @endif
                        @else
                            <span class="text-gray-400">Aucun role assigne</span>
                        @endif
                    </div>
                </div>

                {{-- Statut email --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">Statut</label>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold
                        {{ $user->email_verified_at ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ $user->email_verified_at ? 'Email verifie' : 'Email non verifie' }}
                    </span>
                </div>

                {{-- Date de creation --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">Date de creation</label>
                    <p class="text-base font-medium text-gray-900">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                </div>

                {{-- Derniere mise a jour --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">Derniere mise a jour</label>
                    <p class="text-base font-medium text-gray-900">{{ $user->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="mt-8 pt-6 border-t border-gray-100 flex flex-wrap gap-3">
                <a href="{{ route('superadmin.users.edit', $user) }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-violet-600 rounded-xl hover:bg-violet-700 transition-colors shadow-sm">
                    <x-heroicon-o-pencil class="w-4 h-4" />
                    Modifier
                </a>

                <form method="POST" action="{{ route('superadmin.users.destroy', $user) }}"
                      onsubmit="return confirm('Etes-vous sur de vouloir supprimer cet utilisateur ?')"
                      class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-xl hover:bg-red-700 transition-colors shadow-sm">
                        <x-heroicon-o-trash class="w-4 h-4" />
                        Supprimer
                    </button>
                </form>

                <a href="{{ route('superadmin.users.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">
                    <x-heroicon-o-arrow-left class="w-4 h-4" />
                    Retour a la liste
                </a>
            </div>
        </div>
    </div>
@endsection
