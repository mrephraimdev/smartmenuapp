<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Tenant;
use App\Enums\OrderStatus;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Suivi des commandes en temps réel (board kanban).
 * Permet au personnel de voir et progresser les commandes actives.
 */
class SuiviController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    /**
     * Affiche le board de suivi des commandes.
     */
    public function index(string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        return view('admin.suivi.index', compact('tenant'));
    }

    /**
     * API : retourne les commandes actives groupées par statut (JSON).
     * Utilisé pour l'auto-refresh côté Alpine.js.
     */
    public function data(string $tenantSlug): JsonResponse
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        $activeStatuses = OrderStatus::activeValues();

        $orders = Order::with(['items.dish', 'table'])
            ->where('tenant_id', $tenant->id)
            ->whereIn('status', $activeStatuses)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($order) {
                return [
                    'id'            => $order->id,
                    'order_number'  => $order->order_number,
                    'status'        => $order->status,
                    'total'         => $order->total,
                    'notes'         => $order->notes,
                    'items_count'   => $order->items->sum('quantity'),
                    'created_at'    => $order->created_at->diffForHumans(),
                    'created_raw'   => $order->created_at->format('H:i'),
                    'table'         => $order->table
                        ? ['code' => $order->table->code, 'label' => $order->table->label]
                        : null,
                    'items'         => $order->items->map(fn($i) => [
                        'name'     => $i->dish->name ?? 'Plat supprimé',
                        'quantity' => $i->quantity,
                        'notes'    => $i->notes,
                    ]),
                ];
            });

        $grouped = [
            'RECU' => $orders->where('status', 'RECU')->values(),
            'PREP' => $orders->where('status', 'PREP')->values(),
            'PRET' => $orders->where('status', 'PRET')->values(),
        ];

        return response()->json([
            'success' => true,
            'data'    => $grouped,
            'total'   => $orders->count(),
        ]);
    }

    /**
     * Fait progresser le statut d'une commande au prochain stade.
     */
    public function progress(Request $request, string $tenantSlug, Order $order): JsonResponse
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        if ($order->tenant_id !== $tenant->id) {
            return response()->json(['success' => false, 'message' => 'Non autorisé.'], 403);
        }

        $this->orderService->progressStatus($order);

        $fresh = $order->fresh();

        return response()->json([
            'success'    => true,
            'message'    => 'Statut mis à jour.',
            'new_status' => $fresh->status,
        ]);
    }

    /**
     * Annule une commande.
     */
    public function cancel(Request $request, string $tenantSlug, Order $order): JsonResponse
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        if ($order->tenant_id !== $tenant->id) {
            return response()->json(['success' => false, 'message' => 'Non autorisé.'], 403);
        }

        $this->orderService->cancelOrder($order, $request->input('reason', 'Annulée au comptoir'));

        return response()->json([
            'success' => true,
            'message' => 'Commande annulée.',
        ]);
    }
}
