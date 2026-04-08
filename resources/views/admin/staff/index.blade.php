<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion du Personnel - {{ $tenant->name }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .gradient-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="gradient-header text-white shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="text-white/80 hover:text-white">
                        <x-heroicon-o-arrow-left class="w-6 h-6" />
                    </a>
                    <div>
                        <h1 class="text-xl font-bold">Gestion du Personnel</h1>
                        <p class="text-white/70 text-sm">{{ $tenant->name }}</p>
                    </div>
                </div>

                <a href="{{ route('admin.staff.create', $tenant->slug) }}" class="flex items-center space-x-2 px-4 py-2 bg-white/20 rounded-lg text-white hover:bg-white/30 transition">
                    <x-heroicon-o-plus class="w-5 h-5" />
                    <span>Ajouter</span>
                </a>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <!-- Messages de succès/erreur -->
        @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 flex items-center">
            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-3" />
            <p class="text-green-800">{{ session('success') }}</p>
        </div>
        @endif

        @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4">
            <div class="flex items-center mb-2">
                <x-heroicon-o-exclamation-circle class="w-5 h-5 text-red-500 mr-3" />
                <p class="text-red-800 font-medium">Erreur</p>
            </div>
            <ul class="list-disc list-inside text-red-700 text-sm">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Liste du personnel -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                        <x-heroicon-o-users class="w-5 h-5 mr-2 text-indigo-600" />
                        Personnel du Restaurant
                    </h2>
                    <span class="text-sm text-gray-500">{{ $staff->total() }} membre(s)</span>
                </div>
            </div>

            @if($staff->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rôle</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Créé le</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($staff as $member)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold mr-3">
                                        {{ strtoupper(substr($member->name, 0, 1)) }}
                                    </div>
                                    <div class="text-sm font-medium text-gray-900">{{ $member->name }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ $member->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php $roleEnum = \App\Enums\UserRole::tryFrom($member->role) @endphp
                                <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full {{ $roleEnum ? $roleEnum->badgeClass() : 'bg-gray-100 text-gray-800' }}">
                                    {{ $roleEnum ? $roleEnum->label() : $member->role }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $member->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.staff.edit', [$tenant->slug, $member]) }}" class="text-indigo-600 hover:text-indigo-900" title="Modifier">
                                        <x-heroicon-o-pencil class="w-5 h-5" />
                                    </a>
                                    <form method="POST" action="{{ route('admin.staff.destroy', [$tenant->slug, $member]) }}" class="inline-block" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce membre ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Supprimer">
                                            <x-heroicon-o-trash class="w-5 h-5" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($staff->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $staff->links() }}
            </div>
            @endif
            @else
            <div class="p-12 text-center">
                <x-heroicon-o-users class="w-16 h-16 mx-auto text-gray-300 mb-4" />
                <h3 class="text-lg font-medium text-gray-800 mb-2">Aucun personnel</h3>
                <p class="text-gray-500 mb-6">Vous n'avez pas encore ajouté de membres du personnel.</p>
                <a href="{{ route('admin.staff.create', $tenant->slug) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <x-heroicon-o-plus class="w-5 h-5 mr-2" />
                    Ajouter un membre
                </a>
            </div>
            @endif
        </div>

        <!-- Légende des rôles -->
        <div class="mt-8 bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <x-heroicon-o-information-circle class="w-5 h-5 mr-2 text-blue-500" />
                Rôles disponibles
            </h3>
            <div class="grid md:grid-cols-3 gap-4">
                <div class="p-4 bg-green-50 rounded-xl border border-green-200">
                    <div class="flex items-center mb-2">
                        <x-heroicon-o-banknotes class="w-5 h-5 text-green-600 mr-2" />
                        <span class="font-medium text-green-800">Caissier(e)</span>
                    </div>
                    <p class="text-sm text-green-700">Accès à la caisse, paiements et commandes</p>
                </div>
                <div class="p-4 bg-orange-50 rounded-xl border border-orange-200">
                    <div class="flex items-center mb-2">
                        <x-heroicon-o-fire class="w-5 h-5 text-orange-600 mr-2" />
                        <span class="font-medium text-orange-800">Chef Cuisinier</span>
                    </div>
                    <p class="text-sm text-orange-700">Accès à l'écran cuisine (KDS) et préparation</p>
                </div>
                <div class="p-4 bg-blue-50 rounded-xl border border-blue-200">
                    <div class="flex items-center mb-2">
                        <x-heroicon-o-user-group class="w-5 h-5 text-blue-600 mr-2" />
                        <span class="font-medium text-blue-800">Serveur</span>
                    </div>
                    <p class="text-sm text-blue-700">Accès aux commandes et service en salle</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-8">
        <div class="container mx-auto px-4 py-6">
            <div class="text-center">
                <p class="text-gray-400 text-sm">SmartMenu © {{ date('Y') }}</p>
            </div>
        </div>
    </footer>
</body>
</html>
