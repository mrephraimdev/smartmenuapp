<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Table;
use App\Models\Tenant;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Prise de commande par le serveur (sans paiement)
 * Permet au serveur de créer des commandes pour les clients qui ne peuvent pas scanner.
 */
class ServeurController extends Controller
{
    public function __construct(
        private OrderService $orderService,
    ) {}

    /**
     * Affiche l'interface de prise de commande du serveur.
     */
    public function index(string $tenantSlug)
    {
        $tenant = Tenant::findBySlug($tenantSlug);

        $menus = Menu::forTenant($tenant->id)->with([
            'categories' => function ($q) {
                $q->orderBy('sort_order')->with(['dishes' => function ($q) {
                    $q->where('active', true)->orderBy('name');
                }]);
            }
        ])->where('active', true)->get();

        $tables = Table::forTenant($tenant->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('serveur.commande', compact('tenant', 'menus', 'tables'));
    }

    /**
     * Crée une commande passée par le serveur (sans paiement).
     */
    public function store(Request $request, string $tenantSlug): JsonResponse
    {
        $tenant = Tenant::findBySlug($tenantSlug);

        try {
            $validated = $request->validate([
                'table_id'          => 'required|exists:tables,id',
                'notes'             => 'nullable|string|max:1000',
                'items'             => 'required|array|min:1',
                'items.*.dish_id'   => 'required|integer|exists:dishes,id',
                'items.*.quantity'  => 'required|integer|min:1|max:99',
                'items.*.notes'     => 'nullable|string|max:500',
            ]);

            // Vérifier que la table appartient bien à ce tenant
            $table = Table::find($validated['table_id']);
            if (!$table || $table->tenant_id !== $tenant->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette table n\'appartient pas à ce restaurant.',
                ], 422);
            }

            // Vérifier que tous les plats appartiennent à ce tenant
            foreach ($validated['items'] as $item) {
                $dish = Dish::find($item['dish_id']);
                if (!$dish || $dish->tenant_id !== $tenant->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Un plat ne correspond pas à ce restaurant.',
                    ], 422);
                }
            }

            $order = $this->orderService->createOrder([
                'tenant_id'  => $tenant->id,
                'table_id'   => $validated['table_id'],
                'items'      => $validated['items'],
                'notes'      => $validated['notes'] ?? '',
                'serveur_id' => Auth::id(),
            ]);

            return response()->json([
                'success'      => true,
                'message'      => "Commande {$order->order_number} envoyée en cuisine !",
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'total'        => $order->total,
                'table'        => $table->label ?? $table->code,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Encaisser une commande directement par le serveur (sans passer par la caisse).
     * Enregistre le serveur qui a encaissé pour la traçabilité.
     */
    public function encaisser(Request $request, string $tenantSlug, Order $order)
    {
        $tenant = Tenant::findBySlug($tenantSlug);

        if ($order->tenant_id !== $tenant->id) {
            $msg = 'Commande introuvable.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 404)
                : back()->with('error', $msg);
        }

        if ($order->payment_status === 'PAID') {
            $msg = 'Cette commande est déjà encaissée.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        if ($order->status === 'ANNULE') {
            $msg = 'Impossible d\'encaisser une commande annulée.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        $serveur = Auth::user();

        $order->update([
            'payment_status'    => 'PAID',
            'paid_amount'       => $order->total,
            'paid_at'           => now(),
            'collected_by_id'   => $serveur->id,
            'collected_by_name' => $serveur->name,
            'status'            => $order->status === 'PRET' ? 'SERVI' : $order->status,
        ]);

        Payment::create([
            'order_id'        => $order->id,
            'tenant_id'       => $tenant->id,
            'amount'          => $order->total,
            'method'          => 'CASH',
            'status'          => 'SUCCESS',
            'transaction_id'  => Payment::generateTransactionId(),
            'processed_by'    => $serveur->id,
            'processed_at'    => now(),
            'amount_received' => $order->total,
            'change_given'    => 0,
        ]);

        $message = "Commande {$order->order_number} encaissée par {$serveur->name}.";

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => $message])
            : back()->with('success', $message);
    }

    /**
     * Historique des commandes passées par ce serveur.
     */
    public function historique(string $tenantSlug)
    {
        $tenant  = Tenant::findBySlug($tenantSlug);
        $serveur = Auth::user();

        $orders = Order::with(['table', 'items.dish'])
            ->where('tenant_id', $tenant->id)
            ->where('serveur_id', $serveur->id)
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return view('serveur.historique', compact('tenant', 'orders', 'serveur'));
    }
}
