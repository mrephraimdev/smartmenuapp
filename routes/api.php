<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\Api\UploadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Tenant;
use App\Models\Table;
use App\Http\Controllers\AdminMenuController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Les routes définies ici sont préfixées par /api/ et sont idéales pour les requêtes AJAX.
| Rate Limiting appliqué : 60 requêtes/min par IP (public), 1000 requêtes/min par tenant (authentifié)
|
*/

// Groupe avec Rate Limiting pour toutes les routes API publiques
Route::middleware(['throttle:api'])->group(function () {

// --- 1. Routes pour le CLIENT (menu-client.blade.php) ---

// URL: /api/menu - Optimisé pour performance avec cache
Route::get('/menu', function (Request $request) {
    $tenantId = $request->tenant;
    $tableCode = $request->table;

    if (!$tenantId || !$tableCode) {
        return response()->json(['success' => false, 'message' => 'Paramètres manquants'], 400);
    }

    // Charger table (non cachée car le statut peut changer)
    // Table optionnelle : si le code n'existe pas, on affiche quand même le menu
    $table = Table::where('tenant_id', $tenantId)
        ->where('code', $tableCode)
        ->first();

    // Charger tenant et menu depuis le cache (5 minutes)
    $cacheKey = "menu_client_{$tenantId}";
    $cachedData = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($tenantId) {
        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            return null;
        }

        $menus = $tenant->menus()
            ->with([
                'categories' => function ($query) {
                    $query->orderBy('sort_order')
                          ->with([
                              'dishes' => function ($q) {
                                  $q->where('active', true)
                                    ->with(['variants', 'options']);
                              }
                          ]);
                }
            ])
            ->where('active', true)
            ->get();

        // Merge all categories from all active menus into one virtual menu object
        $allCategories = $menus->flatMap(function ($menu) {
            return $menu->categories;
        })->values();

        $menu = $menus->first();
        if ($menu) {
            $menu->setRelation('categories', $allCategories);
        }

        return ['tenant' => $tenant, 'menu' => $menu];
    });

    if (!$cachedData || !$cachedData['tenant']) {
        return response()->json(['success' => false, 'message' => 'Restaurant introuvable'], 404);
    }

    return response()->json([
        'success' => true,
        'tenant' => $cachedData['tenant'],
        'table' => $table,
        'menu' => $cachedData['menu']
    ]);
});

// ✅ Route pour soumettre la commande (POST) - Rate limited
// URL: /api/orders
Route::post('/orders', [OrderController::class, 'store'])
    ->middleware('throttle:orders');

// ✅ Route pour récupérer le statut d'une commande (GET) - Suivi client temps réel
// URL: /api/orders/{id}
Route::get('/orders/{id}', [OrderController::class, 'getOrderForClient']);

// ✅ Route pour récupérer le thème d'un tenant
// URL: /api/tenants/{tenant}/theme
Route::get('/tenants/{tenant}/theme', [ThemeController::class, 'getTheme']);

// ✅ Routes pour appeler un serveur (depuis le menu client) - Rate limited
// URL: /api/waiter-calls
Route::post('/waiter-calls', [App\Http\Controllers\Api\WaiterCallController::class, 'store'])
    ->middleware('throttle:waiter-calls');

// -------------------------------------------------------------------
// --- 2. Routes pour le KDS (kds.blade.php) ---

// ✅ Route pour récupérer la liste des commandes (GET)
// URL: /api/kds/orders
Route::get('/kds/orders', [OrderController::class, 'index']);

// ✅ Route pour récupérer les commandes par tenant slug (KDS)
// URL: /api/orders/tenant/{tenantSlug}
Route::get('/orders/tenant/{tenantSlug}', [OrderController::class, 'getOrdersByTenant'])->middleware(['web', 'auth:web']);

// ✅ Route pour mettre à jour le statut (PATCH)
// URL: /api/orders/{id}/status
Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus'])->middleware(['web', 'auth:web']);

// ✅ Routes pour les appels de serveur (staff authentifié)
Route::middleware(['web', 'auth:web'])->group(function () {
    Route::get('/waiter-calls', [App\Http\Controllers\Api\WaiterCallController::class, 'index']);
    Route::patch('/waiter-calls/{waiterCall}/acknowledge', [App\Http\Controllers\Api\WaiterCallController::class, 'acknowledge']);
    Route::patch('/waiter-calls/{waiterCall}/resolve', [App\Http\Controllers\Api\WaiterCallController::class, 'resolve']);
});

// Routes Admin - Gestion des Menus
Route::prefix('admin')->group(function () {

    // CATÉGORIES
    Route::get('/{tenantId}/categories', [AdminMenuController::class, 'listCategories']);
    Route::post('/{tenantId}/categories', [AdminMenuController::class, 'createCategory']);
    Route::patch('/categories/{categoryId}', [AdminMenuController::class, 'updateCategory']);
    Route::delete('/categories/{categoryId}', [AdminMenuController::class, 'deleteCategory']);

    // PLATS
    Route::get('/categories/{categoryId}/dishes', [AdminMenuController::class, 'listDishes']);
    Route::post('/categories/{categoryId}/dishes', [AdminMenuController::class, 'createDish']);
    Route::patch('/dishes/{dishId}', [AdminMenuController::class, 'updateDish']);
    Route::delete('/dishes/{dishId}', [AdminMenuController::class, 'deleteDish']);

    // VARIANTES
    Route::post('/dishes/{dishId}/variants', [AdminMenuController::class, 'createVariant']);
    Route::delete('/variants/{variantId}', [AdminMenuController::class, 'deleteVariant']);

    // OPTIONS
    Route::post('/dishes/{dishId}/options', [AdminMenuController::class, 'createOption']);
    Route::delete('/options/{optionId}', [AdminMenuController::class, 'deleteOption']);

});

// Routes Upload (authentifiées)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/tenants/{tenant}/logo', [UploadController::class, 'uploadLogo']);
    Route::post('/tenants/{tenant}/cover', [UploadController::class, 'uploadCover']);
    Route::post('/dishes/{dish}/photo', [UploadController::class, 'uploadDishPhoto']);
    Route::post('/categories/{category}/image', [UploadController::class, 'uploadCategoryImage']);
    Route::delete('/uploads', [UploadController::class, 'deleteImage']);
});

}); // Fin du groupe throttle:api