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
     * Invalider tous les caches liés au tenant
     */
    private function invalidateTenantCache(int $tenantId): void
    {
        \Illuminate\Support\Facades\Cache::forget("dashboard_full_{$tenantId}");
        \Illuminate\Support\Facades\Cache::forget("dashboard_stats_{$tenantId}");
        \Illuminate\Support\Facades\Cache::forget("menu_client_{$tenantId}");
        \Illuminate\Support\Facades\Cache::forget("statistics_{$tenantId}");
    }

    /**
     * Afficher le dashboard admin (optimisé avec cache)
     */
    public function dashboard(\Illuminate\Http\Request $request, $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $tenantId = $tenant->id;

        // Période sélectionnée (défaut : aujourd'hui)
        $period    = $request->get('period', 'today');
        $dateFrom  = $request->get('date_from');
        $dateTo    = $request->get('date_to');

        [$from, $to] = $this->resolveDateRange($period, $dateFrom, $dateTo);

        // Menus (pas de filtre date – donnée structurelle)
        $menus = Menu::with(['categories' => function ($q) {
            $q->withCount('dishes');
        }])->where('tenant_id', $tenantId)->get();

        // Stats filtrées par période
        $orderQuery = \App\Models\Order::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$from->startOfDay(), $to->copy()->endOfDay()]);

        $orderStats = (clone $orderQuery)
            ->selectRaw("COUNT(*) as total_orders, COALESCE(SUM(total), 0) as total_revenue")
            ->first();

        $activeDishes = Dish::where('tenant_id', $tenantId)->where('active', true)->count();

        $popularDishes = DB::table('order_items')
            ->join('dishes', 'order_items.dish_id', '=', 'dishes.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.tenant_id', $tenantId)
            ->whereBetween('orders.created_at', [$from->startOfDay(), $to->copy()->endOfDay()])
            ->select('dishes.name', DB::raw('COUNT(*) as order_count'))
            ->groupBy('dishes.id', 'dishes.name')
            ->orderBy('order_count', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'tenant'       => $tenant,
            'menus'        => $menus,
            'period'       => $period,
            'dateFrom'     => $from->toDateString(),
            'dateTo'       => $to->toDateString(),
            'periodLabel'  => $this->periodLabel($period, $from, $to),
            'stats'        => [
                'totalOrders'  => $orderStats->total_orders ?? 0,
                'totalRevenue' => $orderStats->total_revenue ?? 0,
                'activeDishes' => $activeDishes,
                'popularDishes'=> $popularDishes,
            ],
        ]);
    }

    private function resolveDateRange(string $period, ?string $dateFrom, ?string $dateTo): array
    {
        $today = \Carbon\Carbon::today();
        return match ($period) {
            'yesterday' => [$today->copy()->subDay(), $today->copy()->subDay()],
            'week'      => [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()],
            'month'     => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
            'custom'    => [
                \Carbon\Carbon::parse($dateFrom ?? $today),
                \Carbon\Carbon::parse($dateTo   ?? $today),
            ],
            default     => [$today, $today], // today
        };
    }

    private function periodLabel(string $period, \Carbon\Carbon $from, \Carbon\Carbon $to): string
    {
        return match ($period) {
            'yesterday' => 'Hier',
            'week'      => 'Cette semaine',
            'month'     => 'Ce mois',
            'custom'    => $from->format('d/m/Y') . ' – ' . $to->format('d/m/Y'),
            default     => "Aujourd'hui",
        };
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
            ]);

            $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

            Menu::create([
                'tenant_id' => $tenant->id,
                'title' => $request->title,
                'active' => $request->has('active')
            ]);

            // Invalider les caches
            $this->invalidateTenantCache($tenant->id);

            // Si requête AJAX, retourner JSON
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Menu créé avec succès!'
                ]);
            }

            // Sinon, rediriger vers le dashboard
            return redirect()->route('admin.dashboard', $tenantSlug)->with('success', 'Menu créé avec succès!');

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Mettre à jour un menu
     */
    public function updateMenu(Request $request, $tenantSlug, $id)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'active' => 'boolean'
            ]);

            $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
            $menu = Menu::where('tenant_id', $tenant->id)->findOrFail($id);
            $menu->update($request->all());

            // Invalider les caches
            $this->invalidateTenantCache($tenant->id);

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
    public function destroyMenu($tenantSlug, $id)
    {
        try {
            $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
            $menu = Menu::where('tenant_id', $tenant->id)->findOrFail($id);
            $menu->delete();

            // Invalider les caches
            $this->invalidateTenantCache($tenant->id);

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

            // Invalider les caches
            $this->invalidateTenantCache($tenant->id);

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

            // Invalider les caches
            $this->invalidateTenantCache($tenant->id);

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

            // Invalider les caches
            $this->invalidateTenantCache($tenant->id);

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

            // Invalider les caches
            $this->invalidateTenantCache($tenant->id);

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
     * Statistiques des plats populaires (avec cache 2 minutes)
     */
    public function statistics($tenantSlug)
    {
        try {
            $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

            // Cache les stats pendant 2 minutes
            $cacheKey = "dashboard_stats_{$tenant->id}";
            $stats = \Illuminate\Support\Facades\Cache::remember($cacheKey, 120, function () use ($tenant) {
                // Une seule requête pour orders stats
                $orderStats = \App\Models\Order::where('tenant_id', $tenant->id)
                    ->selectRaw('COUNT(*) as total_orders, COALESCE(SUM(total), 0) as total_revenue')
                    ->first();

                // Plats populaires (limité à 5 pour performance)
                $popularDishes = DB::table('order_items')
                    ->join('dishes', 'order_items.dish_id', '=', 'dishes.id')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->where('orders.tenant_id', $tenant->id)
                    ->select('dishes.name', DB::raw('COUNT(*) as order_count'))
                    ->groupBy('dishes.id', 'dishes.name')
                    ->orderBy('order_count', 'desc')
                    ->limit(5)
                    ->get();

                // Compteur de plats actifs
                $activeDishes = Dish::where('tenant_id', $tenant->id)
                    ->where('active', true)
                    ->count();

                return [
                    'popular_dishes' => $popularDishes,
                    'total_orders' => $orderStats->total_orders ?? 0,
                    'total_revenue' => $orderStats->total_revenue ?? 0,
                    'active_dishes' => $activeDishes
                ];
            });

            return response()->json([
                'success' => true,
                'popular_dishes' => $stats['popular_dishes'],
                'total_orders' => $stats['total_orders'],
                'total_revenue' => $stats['total_revenue'],
                'active_dishes' => $stats['active_dishes']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher le menu client public
     * Cette méthode retourne la vue qui charge les données via JavaScript/API
     */
    public function showMenu($tenantId, $tableId)
    {
        return view('menu-client', [
            'tenantId' => $tenantId,
            'tableCode' => $tableId,
        ]);
    }

    /**
     * Upload de la photo d'un plat
     */
    public function uploadDishPhoto(Request $request, $tenantSlug, $dishId)
    {
        try {
            $request->validate([
                'photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
            ]);

            $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
            $dish = Dish::whereHas('category.menu', function($query) use ($tenant) {
                $query->where('tenant_id', $tenant->id);
            })->findOrFail($dishId);

            // Supprimer l'ancienne photo si elle existe
            if ($dish->photo_url) {
                $oldPath = public_path(parse_url($dish->photo_url, PHP_URL_PATH));
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            // Stocker la nouvelle photo avec nom sécurisé (UUID)
            $file = $request->file('photo');
            $filename = \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('dishes/' . $tenant->id, $filename, 'public');

            // Mettre à jour le plat avec l'URL de la photo
            $dish->update(['photo_url' => '/storage/' . $path]);

            // Invalider les caches (le menu client doit voir la nouvelle photo)
            $this->invalidateTenantCache($tenant->id);

            return response()->json([
                'success' => true,
                'message' => 'Photo uploadée avec succès!',
                'photo_url' => $dish->photo_url
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur upload photo plat: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer la photo d'un plat
     */
    public function deleteDishPhoto($tenantSlug, $dishId)
    {
        try {
            $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
            $dish = Dish::whereHas('category.menu', function($query) use ($tenant) {
                $query->where('tenant_id', $tenant->id);
            })->findOrFail($dishId);

            if ($dish->photo_url) {
                // Supprimer le fichier
                $path = public_path(parse_url($dish->photo_url, PHP_URL_PATH));
                if (file_exists($path)) {
                    unlink($path);
                }

                // Mettre à jour le plat
                $dish->update(['photo_url' => null]);

                // Invalider les caches
                $this->invalidateTenantCache($tenant->id);
            }

            return response()->json([
                'success' => true,
                'message' => 'Photo supprimée avec succès!'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur suppression photo plat: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }
}