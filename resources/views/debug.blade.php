<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Menu QR</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">🛠️ Debug Menu QR</h1>
        
        <div class="grid md:grid-cols-2 gap-8">
            <!-- Section Base de données -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold mb-4">📊 Base de données</h2>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span>Restaurants:</span>
                        <span class="font-bold">{{ App\Models\Tenant::count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Tables:</span>
                        <span class="font-bold">{{ App\Models\Table::count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Plats:</span>
                        <span class="font-bold">{{ App\Models\Dish::count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Commandes:</span>
                        <span class="font-bold">{{ App\Models\Order::count() }}</span>
                    </div>
                </div>
            </div>

            <!-- Section Test Routes -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold mb-4">🔗 Test Routes</h2>
                <div class="space-y-2">
                    <a href="/api/menu?tenant=1&table=A1" target="_blank" 
                       class="block bg-blue-500 text-white px-4 py-2 rounded text-center hover:bg-blue-600">
                        Test Menu API
                    </a>
                    <a href="/orders" target="_blank" 
                       class="block bg-green-500 text-white px-4 py-2 rounded text-center hover:bg-green-600">
                        Test Commandes (GET)
                    </a>
                </div>
            </div>

            <!-- Section Test Création Commande -->
            <div class="bg-white rounded-lg shadow-lg p-6 md:col-span-2">
                <h2 class="text-xl font-bold mb-4">🧪 Test Création Commande</h2>
                <button onclick="testOrder()" 
                        class="bg-purple-500 text-white px-6 py-3 rounded-lg hover:bg-purple-600">
                    Tester création commande
                </button>
                <div id="testResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                    <pre class="text-sm"></pre>
                </div>
            </div>

            <!-- Section URLs de test -->
            <div class="bg-white rounded-lg shadow-lg p-6 md:col-span-2">
                <h2 class="text-xl font-bold mb-4">🎯 URLs de Test</h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <a href="/menu?tenant=1&table=A1" target="_blank" 
                       class="block bg-orange-500 text-white px-4 py-3 rounded text-center hover:bg-orange-600">
                        📱 Interface Client
                    </a>
                    <a href="/kds/{{ auth()->user()->tenant->slug }}" target="_blank"
                       class="block bg-red-500 text-white px-4 py-3 rounded text-center hover:bg-red-600">
                        👨‍🍳 Interface Cuisine
                    </a>
                    <a href="/qrcode/1/A1" target="_blank" 
                       class="block bg-teal-500 text-white px-4 py-3 rounded text-center hover:bg-teal-600">
                        📱 QR Code Test
                    </a>
                    <a href="/admin" target="_blank" 
                       class="block bg-indigo-500 text-white px-4 py-3 rounded text-center hover:bg-indigo-600">
                        ⚙️ Administration
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function testOrder() {
            const testData = {
                tenant_id: 1,
                table_id: 1,
                items: [{
                    dish_id: 1,
                    quantity: 1,
                    variant_id: null,
                    options: [],
                    notes: "Test depuis debug"
                }]
            };

            console.log('📤 Envoi test:', testData);

            try {
                const response = await fetch('/orders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(testData)
                });

                console.log('📥 Statut:', response.status);
                
                const result = await response.json();
                console.log('📥 Réponse:', result);

                // Afficher le résultat
                const resultDiv = document.getElementById('testResult');
                const pre = resultDiv.querySelector('pre');
                pre.textContent = JSON.stringify(result, null, 2);
                resultDiv.classList.remove('hidden');

                if (result.success) {
                    resultDiv.className = 'mt-4 p-4 bg-green-100 border border-green-400 rounded';
                } else {
                    resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 rounded';
                }

            } catch (error) {
                console.error('💥 Erreur:', error);
                
                const resultDiv = document.getElementById('testResult');
                const pre = resultDiv.querySelector('pre');
                pre.textContent = 'Erreur: ' + error.message;
                resultDiv.className = 'mt-4 p-4 bg-red-100 border border-red-400 rounded';
                resultDiv.classList.remove('hidden');
            }
        }
    </script>
</body>
</html>