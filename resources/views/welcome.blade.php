<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu QR - Accueil</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-4xl font-bold text-center mb-8">🏪 Menu QR - Système de Gestion</h1>

        <div class="grid md:grid-cols-3 gap-6">
            <!-- Carte Administration -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold mb-4">⚙️ Administration</h2>
                <p class="text-gray-600 mb-4">Gérer les menus, tables et QR codes</p>
                <a href="/admin" class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 block text-center">
                    Accéder à l'Admin
                </a>
            </div>

            <!-- Carte Cuisine -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold mb-4">👨‍🍳 Espace Cuisine</h2>
                <p class="text-gray-600 mb-4">Tableau des commandes en temps réel</p>
                @if(auth()->user()->tenant)
                <a href="/kds/{{ auth()->user()->tenant->slug }}" class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 block text-center">
                    Accéder au KDS
                    </a>
                @else
                <span class="text-gray-500">Aucun tenant assigné</span>
                @endif
            </div>

            <!-- Carte Super Admin -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold mb-4">👑 Super Administration</h2>
                <p class="text-gray-600 mb-4">Gérer les restaurants et utilisateurs</p>
                <a href="/superadmin" class="bg-purple-500 text-white px-6 py-2 rounded-lg hover:bg-purple-600 block text-center">
                    Accéder au Super Admin
                </a>
            </div>
        </div>

        <!-- Informations utilisateur -->
        <div class="bg-white rounded-lg shadow-lg p-6 mt-8">
            <h2 class="text-xl font-bold mb-4">👤 Informations Utilisateur</h2>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <strong>Nom:</strong> {{ auth()->user()->name }}
                </div>
                <div>
                    <strong>Email:</strong> {{ auth()->user()->email }}
                </div>
                <div>
                    <strong>Rôles:</strong>
                    @foreach(auth()->user()->roles as $role)
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">{{ $role->name }}</span>
                    @endforeach
                </div>
                <div>
                    <strong>Tenant:</strong>
                    @if(auth()->user()->tenant)
                        {{ auth()->user()->tenant->name }} ({{ auth()->user()->tenant->slug }})
                    @else
                        Aucun tenant assigné
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="bg-white rounded-lg shadow-lg p-6 mt-8">
            <h2 class="text-xl font-bold mb-4">⚡ Actions Rapides</h2>
            <div class="grid md:grid-cols-4 gap-4">
                <a href="/debug" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600 text-center">
                    🛠️ Debug
                </a>
                <a href="/menu?tenant=1&table=A1" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 text-center">
                    📱 Test Menu Client
                </a>
                <a href="/qrcode/1/A1" class="bg-teal-500 text-white px-4 py-2 rounded hover:bg-teal-600 text-center">
                    📱 Test QR Code
                </a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 w-full">
                        🚪 Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
