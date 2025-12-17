<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Category;
use App\Models\Dish;
use App\Models\Variant;
use App\Models\Option;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminMenuController extends Controller
{
    /**
     * Afficher le dashboard admin
     */
    public function dashboard($tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $menus = Menu::with('categories.dishes')->where('tenant_id', $tenant->id)->get();

        return view('admin.dashboard', compact('tenant', 'menus'));
    }

    /**
     * Gestion des menus
     */
    public function menus($tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $menus = Menu::where('tenant_id', $tenant->id)->get();

        return view('admin.menus', compact('tenant', 'menus'));
    }

    /**
     * Créer un nouveau menu
     */
    public function storeMenu(Request $request, $tenantSlug)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'active' => 'boolean'
            ]);

            $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

            Menu::create([
                'tenant_id' => $tenant->id,
                'title' => $request->title,
                'active' => $request->active ?? true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Menu créé avec succès!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour un menu
     */
    public function updateMenu(Request $request, $id)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'active' => 'boolean'
            ]);

            $menu = Menu::findOrFail($id);
            $menu->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Menu mis à jour avec succès!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un menu
     */
    public function destroyMenu($id)
    {
        try {
            $menu = Menu::findOrFail($id);
            $menu->delete();

            return response()->json([
                'success' => true,
                'message' => 'Menu supprimé avec succès!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gestion des catégories
     */
    public function categories($tenantSlug, $menuId)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $menu = Menu::with('categories.dishes')->where('tenant_id', $tenant->id)->findOrFail($menuId);
        return view('admin.categories', compact('tenant', 'menu'));
    }

    /**
     * Créer une catégorie
     */
    public function storeCategory(Request $request, $tenantSlug, $menuId)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'sort_order' => 'integer'
            ]);

            $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
            $menu = Menu::where('tenant_id', $tenant->id)->findOrFail($menuId);

            Category::create([
                'menu_id' => $menuId,
                'name' => $request->name,
                'sort_order' => $request->sort_order ?? 0
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Catégorie créée avec succès!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gestion des plats d'une catégorie
     */
    public function dishes($tenantSlug, $categoryId)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $category = Category::with(['dishes.variants', 'dishes.options'])
                           ->whereHas('menu', function($query) use ($tenant) {
                               $query->where('tenant_id', $tenant->id);
                           })
                           ->findOrFail($categoryId);
        return view('admin.dishes', compact('tenant', 'category'));
    }

    /**
     * Créer un plat
     */
    public function storeDish(Request $request, $tenantSlug, $categoryId)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price_base' => 'required|numeric|min:0',
                'active' => 'boolean'
            ]);

            $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
            $category = Category::whereHas('menu', function($query) use ($tenant) {
                $query->where('tenant_id', $tenant->id);
            })->findOrFail($categoryId);

            $dish = Dish::create([
                'category_id' => $categoryId,
                'name' => $request->name,
                'description' => $request->description,
                'price_base' => $request->price_base,
                'active' => $request->active ?? true
            ]);

            // Gérer les variantes
            if ($request->has('variants')) {
                foreach ($request->variants as $variant) {
                    if (!empty($variant['name'])) {
                        Variant::create([
                            'dish_id' => $dish->id,
                            'name' => $variant['name'],
                            'extra_price' => $variant['extra_price'] ?? 0
                        ]);
                    }
                }
            }

            // Gérer les options
            if ($request->has('options')) {
                foreach ($request->options as $option) {
                    if (!empty($option['name'])) {
                        Option::create([
                            'dish_id' => $dish->id,
                            'name' => $option['name'],
                            'kind' => $option['kind'] ?? 'toggle',
                            'extra_price' => $option['extra_price'] ?? 0
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Plat créé avec succès!',
                'dish_id' => $dish->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour un plat
     */
    public function updateDish(Request $request, $tenantSlug, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price_base' => 'required|numeric|min:0',
                'active' => 'boolean'
            ]);

            $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
            $dish = Dish::whereHas('category.menu', function($query) use ($tenant) {
                $query->where('tenant_id', $tenant->id);
            })->findOrFail($id);
            $dish->update($request->only(['name', 'description', 'price_base', 'active']));

            // Synchroniser les variantes
            if ($request->has('variants')) {
                $dish->variants()->delete();
                foreach ($request->variants as $variant) {
                    if (!empty($variant['name'])) {
                        Variant::create([
                            'dish_id' => $dish->id,
                            'name' => $variant['name'],
                            'extra_price' => $variant['extra_price'] ?? 0
                        ]);
                    }
                }
            }

            // Synchroniser les options
            if ($request->has('options')) {
                $dish->options()->delete();
                foreach ($request->options as $option) {
                    if (!empty($option['name'])) {
                        Option::create([
                            'dish_id' => $dish->id,
                            'name' => $option['name'],
                            'kind' => $option['kind'] ?? 'toggle',
                            'extra_price' => $option['extra_price'] ?? 0
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Plat mis à jour avec succès!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un plat
     */
    public function destroyDish($tenantSlug, $id)
    {
        try {
            $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
            $dish = Dish::whereHas('category.menu', function($query) use ($tenant) {
                $query->where('tenant_id', $tenant->id);
            })->findOrFail($id);
            $dish->delete();

            return response()->json([
                'success' => true,
                'message' => 'Plat supprimé avec succès!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle activation d'un plat
     */
    public function toggleDish($tenantSlug, $id)
    {
        try {
            $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
            $dish = Dish::whereHas('category.menu', function($query) use ($tenant) {
                $query->where('tenant_id', $tenant->id);
            })->findOrFail($id);
            $dish->update(['active' => !$dish->active]);

            return response()->json([
                'success' => true,
                'message' => 'Statut du plat mis à jour!',
                'active' => $dish->active
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les données d'un plat pour édition
     */
    public function getDish($tenantSlug, $id)
    {
        try {
            $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
            $dish = Dish::with(['variants', 'options'])
                       ->whereHas('category.menu', function($query) use ($tenant) {
                           $query->where('tenant_id', $tenant->id);
                       })
                       ->findOrFail($id);

            return response()->json([
                'success' => true,
                'dish' => $dish
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Statistiques des plats populaires
     */
    public function statistics($tenantSlug)
    {
        try {
            $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

            $popularDishes = DB::table('order_items')
                ->join('dishes', 'order_items.dish_id', '=', 'dishes.id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.tenant_id', $tenant->id)
                ->select('dishes.name', DB::raw('COUNT(*) as order_count'))
                ->groupBy('dishes.id', 'dishes.name')
                ->orderBy('order_count', 'desc')
                ->limit(10)
                ->get();

            $totalOrders = \App\Models\Order::where('tenant_id', $tenant->id)->count();
            $totalRevenue = \App\Models\Order::where('tenant_id', $tenant->id)->sum('total');

            return response()->json([
                'success' => true,
                'popular_dishes' => $popularDishes,
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }
}