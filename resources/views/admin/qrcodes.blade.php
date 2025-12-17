<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Codes - {{ $tenant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-6">
        <!-- En-tête -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">QR Codes - {{ $tenant->name }}</h1>
                <p class="text-gray-600">Générez et imprimez les QR codes pour vos tables</p>
            </div>
            <a href="{{ route('admin.dashboard', $tenant->slug) }}"
               class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                <i class="fas fa-arrow-left mr-2"></i>Retour
            </a>
        </div>

        <!-- Actions globales -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex flex-wrap gap-4">
                <button onclick="printAllQRCodes()" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                    <i class="fas fa-print mr-2"></i>Imprimer tous les QR codes
                </button>
                <button onclick="downloadAllQRCodes()" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
                    <i class="fas fa-download mr-2"></i>Télécharger tous (PDF)
                </button>
            </div>
        </div>

        <!-- Grille des QR codes -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($qrCodes as $qrCode)
                <div class="bg-white rounded-lg shadow-sm border hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <!-- En-tête table -->
                        <div class="text-center mb-4">
                            <h3 class="font-bold text-lg text-gray-800">{{ $qrCode['table']->label }}</h3>
                            <p class="text-sm text-gray-600">Code: {{ $qrCode['table']->code }}</p>
                        </div>

                        <!-- QR Code -->
                        <div class="text-center mb-4">
                            <div class="inline-block p-3 bg-gray-50 rounded-lg">
                                <img src="{{ url('/qrcode/generate/' . $tenant->id . '/' . $qrCode['table']->code) }}"
                                     alt="QR Code Table {{ $qrCode['table']->code }}"
                                     class="w-32 h-32">
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex space-x-2">
                            <a href="{{ $qrCode['url'] }}" target="_blank"
                               class="flex-1 bg-blue-500 text-white text-center py-2 px-3 rounded text-sm hover:bg-blue-600">
                                <i class="fas fa-eye mr-1"></i>Voir
                            </a>
                            <button onclick="printSingleQRCode('{{ $qrCode['table']->code }}')"
                                    class="flex-1 bg-green-500 text-white text-center py-2 px-3 rounded text-sm hover:bg-green-600">
                                <i class="fas fa-print mr-1"></i>Imprimer
                            </button>
                        </div>

                        <!-- URL (masquée par défaut) -->
                        <details class="mt-3">
                            <summary class="text-xs text-gray-500 cursor-pointer hover:text-gray-700">
                                URL du menu
                            </summary>
                            <p class="text-xs text-gray-600 mt-2 break-all bg-gray-50 p-2 rounded">
                                {{ $qrCode['menu_url'] }}
                            </p>
                        </details>
                    </div>
                </div>
            @endforeach
        </div>

        @if(count($qrCodes) === 0)
            <div class="text-center py-12">
                <i class="fas fa-qrcode text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune table trouvée</h3>
                <p class="text-gray-500">Créez d'abord des tables pour générer les QR codes.</p>
            </div>
        @endif
    </div>

    <script>
        // Imprimer un QR code individuel
        function printSingleQRCode(tableCode) {
            const printWindow = window.open(`/qrcode/{{ $tenant->id }}/${tableCode}`, '_blank');
            printWindow.onload = function() {
                printWindow.print();
            };
        }

        // Imprimer tous les QR codes
        function printAllQRCodes() {
            // Ouvrir chaque QR code dans une nouvelle fenêtre et les imprimer
            @foreach($qrCodes as $qrCode)
                setTimeout(() => {
                    const printWindow = window.open(`{{ $qrCode['url'] }}`, '_blank');
                    printWindow.onload = function() {
                        printWindow.print();
                        printWindow.close();
                    };
                }, {{ $loop->index * 1000 }}); // Délai de 1 seconde entre chaque ouverture
            @endforeach
        }

        // Télécharger tous les QR codes (simulation)
        function downloadAllQRCodes() {
            alert('Fonctionnalité de téléchargement PDF à implémenter avec une bibliothèque comme TCPDF ou DomPDF');
        }
    </script>
</body>
</html>
