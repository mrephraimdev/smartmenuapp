<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs</title>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="container mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">👥 Gestion des Utilisateurs</h1>
                    <div class="flex space-x-4">
                        <a href="{{ route('superadmin.users.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 inline-flex items-center">
                            <x-heroicon-o-plus class="w-5 h-5 mr-2" />Nouvel Utilisateur
                        </a>
                        <a href="{{ route('superadmin.dashboard') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                            🏠 Dashboard Super Admin
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="container mx-auto px-4 py-8">
            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
            @endif

            <!-- Filtres -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <form method="GET" class="flex flex-wrap gap-4">
                    <div>
                        <label for="tenant_filter" class="block text-sm font-medium text-gray-700 mb-2">Tenant</label>
                        <select id="tenant_filter" name="tenant" class="px-3 py-2 border border-gray-300 rounded-md">
                            <option value="">Tous les tenants</option>
                            @foreach(\App\Models\Tenant::all() as $tenant)
                            <option value="{{ $tenant->id }}" {{ request('tenant') == $tenant->id ? 'selected' : '' }}>
                                {{ $tenant->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="role_filter" class="block text-sm font-medium text-gray-700 mb-2">Rôle</label>
                        <select id="role_filter" name="role" class="px-3 py-2 border border-gray-300 rounded-md">
                            <option value="">Tous les rôles</option>
                            @foreach(\App\Enums\UserRole::cases() as $role)
                            <option value="{{ $role->value }}" {{ request('role') == $role->value ? 'selected' : '' }}>
                                {{ $role->label() }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 inline-flex items-center">
                            <x-heroicon-o-funnel class="w-5 h-5 mr-2" />Filtrer
                        </button>
                    </div>
                </form>
            </div>

            <!-- Liste des utilisateurs -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">Utilisateurs</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenant</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rôle</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($users as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($user->tenant)
                                    <div class="text-sm text-gray-900">{{ $user->tenant->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->tenant->slug }}</div>
                                    @else
                                    <span class="text-sm text-red-600 font-medium">Super Admin</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($user->role)
                                        @php $roleEnum = \App\Enums\UserRole::tryFrom($user->role) @endphp
                                        @if($roleEnum)
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $roleEnum->badgeClass() }}">
                                            {{ $roleEnum->label() }}
                                        </span>
                                        @else
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ $user->role }}
                                        </span>
                                        @endif
                                    @else
                                    <span class="text-sm text-gray-500">Aucun rôle</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('superadmin.users.show', $user) }}"
                                           class="text-indigo-600 hover:text-indigo-900">
                                            <x-heroicon-o-eye class="w-5 h-5" />
                                        </a>
                                        <a href="{{ route('superadmin.users.edit', $user) }}"
                                           class="text-blue-600 hover:text-blue-900">
                                            <x-heroicon-o-pencil class="w-5 h-5" />
                                        </a>
                                        <form action="{{ route('superadmin.users.destroy', $user) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                <x-heroicon-o-trash class="w-5 h-5" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    Aucun utilisateur trouvé.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($users->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $users->links() }}
                </div>
                @endif
            </div>
        </main>
    </div>
</body>
</html>
