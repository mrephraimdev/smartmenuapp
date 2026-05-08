<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Table;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServeurDashboardController extends Controller
{
    /**
     * Afficher le dashboard serveur avec l'état des tables.
     */
    public function index(string $tenantSlug)
    {
        $tenant = Tenant::findBySlug($tenantSlug);

        $tables = Table::where('tenant_id', $tenant->id)
            ->with(['activeSession.openedBy'])
            ->orderBy('label')
            ->get();

        $pendingOrders = Order::where('tenant_id', $tenant->id)
            ->where('status', OrderStatus::PENDING->value)
            ->with(['table', 'items.dish', 'tableSession'])
            ->orderBy('created_at')
            ->get();

        $tablesJson = json_encode(
            $tables->map(fn ($t) => $this->serializeTable($t))->values()
        );

        $ordersJson = json_encode(
            $pendingOrders->map(fn ($o) => $this->serializeOrder($o))->values()
        );

        return view('serveur.dashboard', compact('tenant', 'tables', 'pendingOrders', 'tablesJson', 'ordersJson'));
    }

    /**
     * Données JSON pour le polling temps réel.
     */
    public function data(string $tenantSlug): JsonResponse
    {
        $tenant = Tenant::findBySlug($tenantSlug);

        $tables = Table::where('tenant_id', $tenant->id)
            ->with(['activeSession.openedBy'])
            ->orderBy('label')
            ->get()
            ->map(fn ($table) => $this->serializeTable($table));

        $pendingOrders = Order::where('tenant_id', $tenant->id)
            ->where('status', OrderStatus::PENDING->value)
            ->with(['table', 'items.dish'])
            ->orderBy('created_at')
            ->get()
            ->map(fn ($order) => $this->serializeOrder($order));

        return response()->json([
            'tables'         => $tables,
            'pending_orders' => $pendingOrders,
            'pending_count'  => $pendingOrders->count(),
        ]);
    }

    /**
     * Valider une commande EN_ATTENTE → RECU (part en cuisine).
     */
    public function validateOrder(Request $request, string $tenantSlug, Order $order): JsonResponse
    {
        $tenant = Tenant::findBySlug($tenantSlug);

        if ($order->tenant_id !== $tenant->id) {
            return response()->json(['success' => false, 'message' => 'Commande introuvable.'], 404);
        }

        if ($order->status !== OrderStatus::PENDING->value) {
            return response()->json(['success' => false, 'message' => 'Cette commande ne peut pas être validée.'], 422);
        }

        $order->update(['status' => OrderStatus::RECEIVED->value]);

        return response()->json([
            'success' => true,
            'message' => "Commande #{$order->order_number} envoyée en cuisine.",
        ]);
    }

    /**
     * Refuser une commande EN_ATTENTE → ANNULE.
     */
    public function refuseOrder(Request $request, string $tenantSlug, Order $order): JsonResponse
    {
        $tenant = Tenant::findBySlug($tenantSlug);

        if ($order->tenant_id !== $tenant->id) {
            return response()->json(['success' => false, 'message' => 'Commande introuvable.'], 404);
        }

        if ($order->status !== OrderStatus::PENDING->value) {
            return response()->json(['success' => false, 'message' => 'Cette commande ne peut pas être refusée.'], 422);
        }

        $order->update(['status' => OrderStatus::CANCELLED->value]);

        return response()->json([
            'success' => true,
            'message' => "Commande #{$order->order_number} refusée.",
        ]);
    }

    /**
     * Page dédiée commandes en attente.
     */
    public function pendingOrdersPage(string $tenantSlug)
    {
        $tenant = Tenant::findBySlug($tenantSlug);

        $pendingOrders = Order::where('tenant_id', $tenant->id)
            ->where('status', OrderStatus::PENDING->value)
            ->with(['table', 'items.dish'])
            ->orderBy('created_at')
            ->get();

        $ordersJson = json_encode(
            $pendingOrders->map(fn ($o) => $this->serializeOrder($o))->values()
        );

        return view('serveur.pending-orders', compact('tenant', 'pendingOrders', 'ordersJson'));
    }

    /**
     * JSON polling commandes en attente.
     */
    public function pendingOrdersData(string $tenantSlug): JsonResponse
    {
        $tenant = Tenant::findBySlug($tenantSlug);

        $pendingOrders = Order::where('tenant_id', $tenant->id)
            ->where('status', OrderStatus::PENDING->value)
            ->with(['table', 'items.dish'])
            ->orderBy('created_at')
            ->get()
            ->map(fn ($o) => $this->serializeOrder($o));

        return response()->json([
            'pending_orders' => $pendingOrders,
            'pending_count'  => $pendingOrders->count(),
        ]);
    }

    private function serializeTable(Table $table): array
    {
        $session = $table->activeSession;

        return [
            'id'          => $table->id,
            'code'        => $table->code,
            'label'       => $table->label,
            'capacity'    => $table->capacity,
            'has_session' => (bool) $session,
            'session'     => $session ? [
                'id'                => $session->id,
                'opened_at'         => $session->opened_at->toIso8601String(),
                'last_activity_at'  => $session->last_activity_at?->toIso8601String(),
                'remaining_seconds' => $session->getRemainingSeconds(),
                'opened_by'         => $session->openedBy?->name,
            ] : null,
        ];
    }

    private function serializeOrder(Order $order): array
    {
        return [
            'id'           => $order->id,
            'order_number' => $order->order_number,
            'table'        => $order->table ? ['label' => $order->table->label, 'code' => $order->table->code] : null,
            'total'        => $order->total,
            'notes'        => $order->notes,
            'created_at'   => $order->created_at->toIso8601String(),
            'items'        => $order->items->map(fn ($item) => [
                'name'     => $item->dish?->name ?? '—',
                'quantity' => $item->quantity,
                'price'    => $item->unit_price,
            ]),
        ];
    }
}
