<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails Table - {{ $tenant->name }}</title>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-6">
        <!-- En-tête -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">🍽️ Détails de la Table</h1>
                <p class="text-gray-600">{{ $tenant->name }} - {{ $table->label }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.tables.edit', [$tenant->slug, $table->id]) }}" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 inline-flex items-center">
                    <x-heroicon-o-pencil class="w-5 h-5 mr-2" />Modifier
                </a>
                <a href="{{ route('admin.tables.index', $tenant->slug) }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 inline-flex items-center">
                    <x-heroicon-o-arrow-left class="w-5 h-5 mr-2" />Retour
                </a>
            </div>
        </div>

        <!-- Informations principales -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <x-heroicon-o-hashtag class="w-6 h-6 text-blue-600" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Code</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $table->code }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <x-heroicon-o-users class="w-6 h-6 text-green-600" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Capacité</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $table->capacity }} pers.</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 {{ $table->is_active ? 'bg-green-100' : 'bg-red-100' }} rounded-full">
                        @if($table->is_active)
                            <x-heroicon-o-check class="w-6 h-6 text-green-600" />
                        @else
                            <x-heroicon-o-no-symbol class="w-6 h-6 text-red-600" />
                        @endif
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Statut</p>
                        <p class="text-2xl font-bold {{ $table->is_active ? 'text-green-600' : 'text-red-600' }}">
                            {{ $table->is_active ? 'Active' : 'Inactive' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Code -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-xl font-bold mb-4 flex items-center">
                <x-heroicon-o-qr-code class="w-6 h-6 mr-2" />QR Code
            </h2>
            <div class="flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-6">
                <div class="text-center">
                    <img src="{{ route('qrcode.generate', [$tenant->id, $table->code]) }}"
                         alt="QR Code Table {{ $table->code }}"
                         class="border rounded-lg shadow-sm">
                    <p class="mt-2 text-sm text-gray-600">Scannez pour accéder au menu</p>
                </div>
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-800 mb-2">Informations du QR Code</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">URL:</span>
                            <a href="{{ url("/menu?tenant={$tenant->id}&table={$table->code}") }}"
                               target="_blank"
                               class="text-blue-600 hover:text-blue-800 break-all">
                                {{ url("/menu?tenant={$tenant->id}&table={$table->code}") }}
                            </a>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Taille:</span>
                            <span class="text-gray-900">300x300px</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Format:</span>
                            <span class="text-gray-900">PNG</span>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('qrcode.show', [$tenant->id, $table->code]) }}"
                           target="_blank"
                           class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                            <x-heroicon-o-printer class="w-5 h-5 mr-2" />Voir page d'impression
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historique des commandes -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold mb-4 flex items-center">
                <x-heroicon-o-clock class="w-6 h-6 mr-2" />Historique des Commandes
            </h2>

            @if($table->orders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($table->orders->sortByDesc('created_at') as $order)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">#{{ $order->id }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($order->status === 'RECU') bg-yellow-100 text-yellow-800
                                            @elseif($order->status === 'PREP') bg-blue-100 text-blue-800
                                            @elseif($order->status === 'PRET') bg-purple-100 text-purple-800
                                            @elseif($order->status === 'SERVI') bg-green-100 text-green-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ $order->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ number_format($order->total, 0, ',', ' ') }} FCFA</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="#" class="text-blue-600 hover:text-blue-900">
                                            <x-heroicon-o-eye class="w-5 h-5" />
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <x-heroicon-o-document-text class="w-12 h-12 text-gray-300 mx-auto mb-4" />
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune commande</h3>
                    <p class="text-gray-500">Cette table n'a pas encore été utilisée pour des commandes.</p>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
