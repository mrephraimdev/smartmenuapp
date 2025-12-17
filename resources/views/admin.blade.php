<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Menu QR</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">⚙️ Administration</h1>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Carte Gestion Menus -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold mb-4">📋 Menus</h2>
                <p class="text-gray-600 mb-4">Gérer les catégories et plats</p>
                <a href="#" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Gérer les Menus
                </a>
            </div>

            <!-- Carte QR Codes -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold mb-4">📱 QR Codes</h2>
                <p class="text-gray-600 mb-4">Générer des QR codes pour les tables</p>
                <a href="/qrcode/1/A1" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    Générer QR Code
                </a>
            </div>

            <!-- Carte Commandes -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold mb-4">📊 Commandes</h2>
                <p class="text-gray-600 mb-4">Voir les statistiques des commandes</p>
                <a href="/kds/{{ auth()->user()->tenant->slug }}" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                    Voir le KDS
                </a>
            </div>

            <!-- Carte Restaurant -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold mb-4">🏪 Restaurant</h2>
                <p class="text-gray-600 mb-4">Informations et paramètres</p>
                <a href="#" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
                    Paramètres
                </a>
            </div>
        </div>
    </div>
</body>
</html>