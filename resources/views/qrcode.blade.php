<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - {{ $tenant->name }} - Table {{ $table->code }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- En-tête -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ $tenant->name }}</h1>
            <p class="text-xl text-gray-600">Table {{ $table->label }} ({{ $table->code }})</p>
        </div>

        <!-- Carte QR Code -->
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-8">
            <!-- Branding -->
            @if($tenant->branding && $tenant->branding['logo_url'])
                <div class="text-center mb-6">
                    <img src="{{ $tenant->branding['logo_url'] }}" alt="{{ $tenant->name }}"
                         class="h-16 mx-auto mb-4">
                </div>
            @endif

            <!-- QR Code -->
            <div class="text-center mb-6">
                <div class="inline-block p-4 bg-white border-2 border-gray-200 rounded-lg">
                    <img src="{{ $qrCodeUrl }}" alt="QR Code Menu"
                         class="w-64 h-64">
                </div>
            </div>

            <!-- Informations -->
            <div class="text-center mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-2">Scannez pour commander</h2>
                <p class="text-sm text-gray-600 mb-4">
                    Utilisez votre téléphone pour accéder au menu digital
                </p>
                <div class="text-xs text-gray-500">
                    <p><strong>Restaurant:</strong> {{ $tenant->name }}</p>
                    <p><strong>Table:</strong> {{ $table->label }}</p>
                    <p><strong>URL:</strong> <span class="break-all">{{ $menuUrl }}</span></p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex space-x-4 no-print">
                <button onclick="window.print()" class="flex-1 bg-blue-500 text-white py-3 px-4 rounded-lg hover:bg-blue-600 transition-colors">
                    <i class="fas fa-print mr-2"></i>Imprimer
                </button>
                <a href="{{ $menuUrl }}" target="_blank" class="flex-1 bg-green-500 text-white py-3 px-4 rounded-lg hover:bg-green-600 transition-colors text-center">
                    <i class="fas fa-external-link-alt mr-2"></i>Tester le menu
                </a>
            </div>
        </div>

        <!-- Instructions d'impression -->
        <div class="max-w-md mx-auto mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4 no-print">
            <h3 class="font-semibold text-blue-800 mb-2">
                <i class="fas fa-info-circle mr-2"></i>Instructions d'impression
            </h3>
            <ul class="text-sm text-blue-700 space-y-1">
                <li>• Imprimez cette page en format A4</li>
                <li>• Placez le QR code sur la table</li>
                <li>• Assurez-vous qu'il est scannable</li>
                <li>• Testez avec votre téléphone avant impression finale</li>
            </ul>
        </div>

        <!-- Retour -->
        <div class="text-center mt-8 no-print">
            <a href="{{ route('admin.dashboard', $tenant->slug) }}"
               class="inline-flex items-center px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Retour au dashboard
            </a>
        </div>
    </div>

    <script>
        // Auto-refresh du QR code toutes les 30 secondes (optionnel)
        setTimeout(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
