@extends('layouts.superadmin')

@section('title', 'Utilisateurs')
@section('page-title', 'Utilisateurs')
@section('breadcrumb')
    <span class="text-gray-500">Utilisateurs</span>
@endsection

@section('content')
    {{-- Header Row --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Gestion des Utilisateurs</h2>
            <p class="text-sm text-gray-500 mt-1">Tous les utilisateurs enregistres dans le systeme</p>
        </div>
        <a href="{{ route('superadmin.users.create') }}"
           class="inline-flex items-center gap-2 bg-violet-600 text-white px-5 py-2.5 rounded-xl hover:bg-violet-700 transition-colors font-semibold text-sm shadow-sm">
            <x-heroicon-o-plus class="w-5 h-5" />
            Nouvel Utilisateur
        </a>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-5 py-3.5 rounded-xl mb-6 text-base font-medium">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[180px]">
                <label for="tenant_filter" class="block text-base font-semibold text-gray-700 mb-2">Tenant</label>
                <select id="tenant_filter" name="tenant"
                        class="w-full px-4 py-3 text-base border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
                    <option value="">Tous les tenants</option>
                    @foreach(\App\Models\Tenant::all() as $tenant)
                    <option value="{{ $tenant->id }}" {{ request('tenant') == $tenant->id ? 'selected' : '' }}>
                        {{ $tenant->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="flex-1 min-w-[180px]">
                <label for="role_filter" class="block text-base font-semibold text-gray-700 mb-2">Role</label>
                <select id="role_filter" name="role"
                        class="w-full px-4 py-3 text-base border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
                    <option value="">Tous les roles</option>
                    @foreach(\App\Enums\UserRole::cases() as $role)
                    <option value="{{ $role->value }}" {{ request('role') == $role->value ? 'selected' : '' }}>
                        {{ $role->label() }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-violet-600 text-white px-5 py-3 rounded-xl hover:bg-violet-700 transition-colors font-semibold text-sm">
                    <x-heroicon-o-funnel class="w-5 h-5" />
                    Filtrer
                </button>
            </div>
        </form>
    </div>

    {{-- Users Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nom</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tenant</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    @forelse($users as $user)
                    <tr class="hover:bg-violet-50/40 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-base font-semibold text-gray-900">{{ $user->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($user->tenant)
                            <div class="text-sm font-medium text-gray-900">{{ $user->tenant->name }}</div>
                            <div class="text-xs text-gray-400">{{ $user->tenant->slug }}</div>
                            @else
                            <span class="inline-flex px-2.5 py-1 text-xs font-bold rounded-full bg-violet-100 text-violet-800">Super Admin</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($user->role)
                                @php $roleEnum = \App\Enums\UserRole::tryFrom($user->role) @endphp
                                @if($roleEnum)
                                <span class="inline-flex px-2.5 py-1 text-xs font-bold rounded-full {{ $roleEnum->badgeClass() }}">
                                    {{ $roleEnum->label() }}
                                </span>
                                @else
                                <span class="inline-flex px-2.5 py-1 text-xs font-bold rounded-full bg-gray-100 text-gray-800">
                                    {{ $user->role }}
                                </span>
                                @endif
                            @else
                            <span class="text-sm text-gray-400">Aucun role</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('superadmin.users.show', $user) }}"
                                   class="p-2 text-gray-400 hover:text-violet-600 hover:bg-violet-50 rounded-lg transition-colors" title="Voir">
                                    <x-heroicon-o-eye class="w-5 h-5" />
                                </a>
                                <a href="{{ route('superadmin.users.edit', $user) }}"
                                   class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Modifier">
                                    <x-heroicon-o-pencil class="w-5 h-5" />
                                </a>
                                <form action="{{ route('superadmin.users.destroy', $user) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Etes-vous sur de vouloir supprimer cet utilisateur ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Supprimer">
                                        <x-heroicon-o-trash class="w-5 h-5" />
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="text-gray-400">
                                <x-heroicon-o-users class="w-10 h-10 mx-auto mb-2" />
                                <p class="text-base font-medium">Aucun utilisateur trouve.</p>
                                <p class="text-sm mt-1">Commencez par creer votre premier utilisateur.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $users->links() }}
        </div>
        @endif
    </div>
@endsection
