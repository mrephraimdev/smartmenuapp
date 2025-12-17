<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Dish;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'tenant_id' => 'required|exists:tenants,id',
                'table_id' => 'required|exists:tables,id',
                'items' => 'required|array|min:1',
                'items.*.dish_id' => 'required|exists:dishes,id',
                'items.*.quantity' => 'required|integer|min:1'
            ]);

            DB::beginTransaction();

            $total = 0;
            $orderItems = [];

            foreach ($validated['items'] as $item) {
                $dish = Dish::find($item['dish_id']);
                $itemPrice = $dish->price_base;

                if (!empty($item['variant_id'])) {
                    $variant = Variant::find($item['variant_id']);
                    if ($variant) {
                        $itemPrice += $variant->extra_price;
                    }
                }

                $itemTotal = $itemPrice * $item['quantity'];
                $total += $itemTotal;

                $orderItems[] = [
                    'dish_id' => $item['dish_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'options' => json_encode($item['options'] ?? []), // ✅ CONVERTIR EN JSON
                    'quantity' => $item['quantity'],
                    'unit_price' => $itemPrice,
                    'notes' => $item['notes'] ?? ''
                ];
            }

            $order = Order::create([
                'tenant_id' => $validated['tenant_id'],
                'table_id' => $validated['table_id'],
                'status' => 'RECU',
                'total' => $total,
                'notes' => $request->notes ?? ''
            ]);

            foreach ($orderItems as $itemData) {
                $itemData['order_id'] = $order->id;
                OrderItem::create($itemData);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Commande créée avec succès!',
                'order_id' => $order->id,
                'total' => $total
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    // ✅ CORRIGÉ : Charger les variants + Filtrer les statuts + Filtrer par tenant de l'utilisateur connecté
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Order::with([
                    'table',
                    'items.dish',
                    'items.variant'  // ✅ AJOUTER LA VARIANTE
                ])
                ->whereIn('status', ['RECU', 'PREP', 'PRET']);  // ✅ EXCLURE SERVI

        // Filtrer par tenant de l'utilisateur connecté (sauf super admin)
        if ($user && !$user->hasRole('SUPER_ADMIN')) {
            $query->where('tenant_id', $user->tenant_id);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        return response()->json($orders);
    }

    // ✅ NOUVELLE MÉTHODE : Récupérer les commandes pour un tenant spécifique (KDS)
    public function getOrdersByTenant($tenantSlug)
    {
        $user = auth()->user();
        $tenant = \App\Models\Tenant::where('slug', $tenantSlug)->firstOrFail();

        // Vérifier l'accès au tenant
        if (!$user->hasRole('SUPER_ADMIN') && $user->tenant_id != $tenant->id) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $orders = Order::with([
                    'table',
                    'items.dish',
                    'items.variant'
                ])
                ->where('tenant_id', $tenant->id)
                ->whereIn('status', ['RECU', 'PREP', 'PRET'])
                ->orderBy('created_at', 'desc')
                ->get();

        return response()->json($orders);
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $user = auth()->user();

        // Vérifier l'accès au tenant
        if (!$user->hasRole('SUPER_ADMIN') && $order->tenant_id != $user->tenant_id) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $order->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour'
        ]);
    }

    public function kds($tenantSlug)
    {
        $user = auth()->user();

        $tenant = \App\Models\Tenant::where('slug', $tenantSlug)->firstOrFail();

        // Vérifier l'accès au tenant
        if (!$user->hasRole('SUPER_ADMIN') && $user->tenant_id != $tenant->id) {
            abort(403, 'Accès non autorisé à ce tenant');
        }

        return view('kds', ['tenantId' => $tenant->id, 'tenantSlug' => $tenantSlug]);
    }
}