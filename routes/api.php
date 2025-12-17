<?php

use App\Http\Controllers\OrderController;
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
|
*/

// --- 1. Routes pour le CLIENT (menu-client.blade.php) ---

// URL: /api/menu
Route::get('/menu', function (Request $request) {
    $tenant = Tenant::find($request->tenant);
    $table = Table::where('tenant_id', $request->tenant)
                          ->where('code', $request->table)
                          ->first();

    if (!$tenant || !$table) {
         return response()->json(['success' => false, 'message' => 'Ressource introuvable'], 404);
    }
    
    $menu = $tenant->menus()
                  ->with(['categories.dishes.variants', 'categories.dishes.options'])
                  ->where('active', true)
                  ->first();

    return response()->json([
        'success' => true,
        'tenant' => $tenant,
        'table' => $table,
        'menu' => $menu
    ]);
});

// ✅ Route pour soumettre la commande (POST)
// URL: /api/orders
Route::post('/api/orders', [OrderController::class, 'store']);


// -------------------------------------------------------------------
// --- 2. Routes pour le KDS (kds.blade.php) ---

// ✅ Route pour récupérer la liste des commandes (GET)
// URL: /api/kds/orders
Route::get('/api/kds/orders', [OrderController::class, 'index']);

// ✅ Route pour mettre à jour le statut (PATCH)
// URL: /api/orders/{id}/status
Route::patch('/api/orders/{id}/status', [OrderController::class, 'updateStatus'])->middleware('web');

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