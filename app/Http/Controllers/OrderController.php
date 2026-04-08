<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Tenant;
use App\Services\OrderService;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    /**
     * Get order status for client tracking (public API - no auth required)
     * Used by menu-client.blade.php for real-time order tracking
     */
    public function getOrderForClient(int $id): JsonResponse
    {
        $order = Order::with(['items.dish', 'table'])
            ->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Commande non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'total' => $order->total,
                'table' => $order->table ? [
                    'id' => $order->table->id,
                    'code' => $order->table->code,
                ] : null,
                'items' => $order->items->map(fn($item) => [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'dish' => $item->dish ? [
                        'id' => $item->dish->id,
                        'name' => $item->dish->name,
                    ] : null,
                ]),
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ]
        ]);
    }

    /**
     * Create a new order
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'tenant_id' => 'required|exists:tenants,id',
                'table_id' => [
                    'required',
                    'exists:tables,id',
                    // SECURITE: Vérifier que la table appartient au tenant
                    function ($attribute, $value, $fail) use ($request) {
                        $table = \App\Models\Table::find($value);
                        if (!$table || $table->tenant_id != $request->tenant_id) {
                            $fail('Cette table n\'appartient pas à ce restaurant.');
                        }
                    },
                ],
                'items' => 'required|array|min:1',
                'items.*.dish_id' => [
                    'required',
                    'exists:dishes,id',
                    // SECURITE: Vérifier que le plat appartient au tenant
                    function ($attribute, $value, $fail) use ($request) {
                        $dish = \App\Models\Dish::find($value);
                        if (!$dish || $dish->tenant_id != $request->tenant_id) {
                            $fail('Ce plat n\'appartient pas à ce restaurant.');
                        }
                    },
                ],
                'items.*.quantity' => 'required|integer|min:1|max:99',
                'items.*.variant_id' => 'nullable|exists:variants,id',
                'items.*.options' => 'nullable|array',
                'items.*.notes' => 'nullable|string|max:500',
                'notes' => 'nullable|string|max:1000',
            ]);

            $order = $this->orderService->createOrder($validated);

            return response()->json([
                'success' => true,
                'message' => 'Commande créée avec succès!',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total' => $order->total
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get orders for authenticated user's tenant (Admin View)
     */
    public function index(Request $request, string $tenantSlug)
    {
        $user = auth()->user();

        if (!$user) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }
            return redirect()->route('login');
        }

        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        if (!$user->hasRole('SUPER_ADMIN') && $user->tenant_id != $tenant->id) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Accès non autorisé'], 403);
            }
            abort(403, 'Accès non autorisé à ce tenant');
        }

        // Date filter - par défaut aujourd'hui si non spécifié
        $filterDate = $request->filled('date') ? $request->date : now()->format('Y-m-d');

        // Build query with filters
        $query = Order::with(['table', 'items.dish', 'items.variant'])
            ->where('tenant_id', $tenant->id)
            ->whereDate('created_at', $filterDate)
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('table')) {
            $query->where('table_id', $request->table);
        }

        // For JSON requests (API), return all data
        if ($request->wantsJson()) {
            return response()->json($query->get());
        }

        // For web requests, paginate and return view
        $orders = $query->paginate(50)->withQueryString();

        // Get tables for filter dropdown
        $tables = \App\Models\Table::where('tenant_id', $tenant->id)->get();

        // Calculate statistics for the selected date
        $statistics = [
            'total' => Order::where('tenant_id', $tenant->id)
                ->whereDate('created_at', $filterDate)
                ->count(),
            'pending' => Order::where('tenant_id', $tenant->id)
                ->whereDate('created_at', $filterDate)
                ->whereIn('status', ['RECU', 'PREP', 'PRET'])
                ->count(),
            'completed' => Order::where('tenant_id', $tenant->id)
                ->whereDate('created_at', $filterDate)
                ->where('status', 'SERVI')
                ->count(),
            'revenue' => Order::where('tenant_id', $tenant->id)
                ->whereDate('created_at', $filterDate)
                ->where('payment_status', 'PAID')
                ->sum('total'),
        ];

        return view('admin.orders.index', [
            'orders' => $orders,
            'tables' => $tables,
            'statistics' => $statistics,
            'tenantSlug' => $tenantSlug,
            'tenant' => $tenant,
            'filterDate' => $filterDate,
        ]);
    }

    /**
     * Get orders for a specific tenant (KDS API)
     * Retourne toutes les commandes du jour (actives + servies)
     */
    public function getOrdersByTenant(string $tenantSlug): JsonResponse
    {
        $user = auth()->user();
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        if (!$user) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        if (!$user->hasRole('SUPER_ADMIN') && $user->tenant_id != $tenant->id) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        // Récupérer toutes les commandes du jour (pas annulées) pour le KDS
        $orders = Order::with(['table', 'items.dish', 'items.variant'])
            ->where('tenant_id', $tenant->id)
            ->whereDate('created_at', now())
            ->where('status', '!=', 'ANNULE')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $user = auth()->user();

        if (!$user) {
            return response()->json(['success' => false, 'error' => 'Non authentifié'], 401);
        }

        if (!$user->hasRole('SUPER_ADMIN') && $order->tenant_id != $user->tenant_id) {
            return response()->json(['success' => false, 'error' => 'Accès non autorisé'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:' . implode(',', array_column(OrderStatus::cases(), 'value')),
        ]);

        $newStatus = OrderStatus::from($validated['status']);
        $order = $this->orderService->updateStatus($order, $newStatus);

        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour',
            'order' => $order
        ]);
    }

    /**
     * Progress order to next status
     */
    public function progressStatus(int $id): JsonResponse
    {
        return $this->progress($id);
    }

    /**
     * Progress order to next status (alias for progressStatus)
     */
    public function progress(string $tenantSlug, int $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $user = auth()->user();

        if (!$user->hasRole('SUPER_ADMIN') && $order->tenant_id != $user->tenant_id) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $order = $this->orderService->progressStatus($order);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'La commande ne peut pas progresser'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Statut progressé',
            'order' => $order
        ]);
    }

    /**
     * Cancel an order
     */
    public function cancel(Request $request, string $tenantSlug, int $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $user = auth()->user();

        if (!$user->hasRole('SUPER_ADMIN') && $order->tenant_id != $user->tenant_id) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $reason = $request->input('reason', '');
        $order = $this->orderService->cancelOrder($order, $reason);

        return response()->json([
            'success' => true,
            'message' => 'Commande annulée',
            'order' => $order
        ]);
    }

    /**
     * Get order details
     */
    public function show(Request $request, string $tenantSlug, int $id)
    {
        $order = $this->orderService->getOrderWithDetails($id);
        $user = auth()->user();
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        if (!$order) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Commande non trouvée'], 404);
            }
            abort(404, 'Commande non trouvée');
        }

        if (!$user->hasRole('SUPER_ADMIN') && $order->tenant_id != $user->tenant_id) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Accès non autorisé'], 403);
            }
            abort(403, 'Accès non autorisé');
        }

        if ($request->wantsJson()) {
            return response()->json($order);
        }

        return view('admin.orders.show', [
            'order' => $order,
            'tenantSlug' => $tenantSlug,
            'tenant' => $tenant,
        ]);
    }

    /**
     * KDS view
     */
    public function kds(string $tenantSlug)
    {
        $user = auth()->user();
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        if (!$user->hasRole('SUPER_ADMIN') && $user->tenant_id != $tenant->id) {
            abort(403, 'Accès non autorisé à ce tenant');
        }

        return view('kds', [
            'tenantId' => $tenant->id,
            'tenantSlug' => $tenantSlug
        ]);
    }

    /**
     * Get orders grouped by status for KDS
     */
    public function kdsData(string $tenantSlug): JsonResponse
    {
        $user = auth()->user();
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        if (!$user->hasRole('SUPER_ADMIN') && $user->tenant_id != $tenant->id) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $data = $this->orderService->getOrdersForKDS($tenant->id);

        return response()->json($data);
    }
}
