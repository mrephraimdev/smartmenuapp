<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Table;
use App\Models\Tenant;
use App\Enums\PaymentMethod;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Prise de commande au comptoir (présentiel)
 * Permet au personnel de créer des commandes sans QR code.
 */
class ComptourController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private PaymentService $paymentService,
    ) {}

    /**
     * Affiche l'interface de prise de commande au comptoir.
     */
    public function index(string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        $menus = Menu::with([
            'categories' => function ($q) {
                $q->orderBy('name')->with(['dishes' => function ($q) {
                    $q->where('active', true)->orderBy('name');
                }]);
            }
        ])->where('active', true)->get();

        $tables = Table::where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('admin.comptoir.index', compact('tenant', 'menus', 'tables'));
    }

    /**
     * Affiche le reçu de commande pour impression.
     */
    public function receipt(string $tenantSlug, int $orderId)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $order = Order::where('id', $orderId)
            ->where('tenant_id', $tenant->id)
            ->with(['items.dish', 'items.variant', 'table'])
            ->firstOrFail();

        return response()
            ->view('prints.order-receipt', compact('order', 'tenant'))
            ->header('Cache-Control', 'no-store');
    }

    /**
     * Crée une commande passée au comptoir (table_id optionnel).
     */
    public function store(Request $request, string $tenantSlug): JsonResponse
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        try {
            $methodValues = implode(',', array_column(PaymentMethod::cases(), 'value'));

            $validated = $request->validate([
                'table_id'          => 'nullable|exists:tables,id',
                'customer_name'     => 'nullable|string|max:100',
                'items'             => 'required|array|min:1',
                'items.*.dish_id'   => 'required|integer|exists:dishes,id',
                'items.*.quantity'  => 'required|integer|min:1|max:99',
                'items.*.notes'     => 'nullable|string|max:500',
                'notes'             => 'nullable|string|max:1000',
                'payment_method'    => "nullable|string|in:{$methodValues}",
                'amount_received'   => 'nullable|numeric|min:0',
            ]);

            // Validate table belongs to this tenant
            if (!empty($validated['table_id'])) {
                $table = Table::find($validated['table_id']);
                if (!$table || $table->tenant_id !== $tenant->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cette table n\'appartient pas à ce restaurant.',
                    ], 422);
                }
            }

            // Validate all dishes belong to this tenant
            foreach ($validated['items'] as $item) {
                $dish = Dish::find($item['dish_id']);
                if (!$dish || $dish->tenant_id !== $tenant->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Un plat ne correspond pas à ce restaurant.',
                    ], 422);
                }
            }

            // Build notes with customer name if provided
            $notes = '';
            if (!empty($validated['customer_name'])) {
                $notes = 'Client : ' . $validated['customer_name'];
                if (!empty($validated['notes'])) {
                    $notes .= "\n" . $validated['notes'];
                }
            } else {
                $notes = $validated['notes'] ?? '';
            }

            $order = $this->orderService->createOrder([
                'tenant_id' => $tenant->id,
                'table_id'  => $validated['table_id'] ?? null,
                'items'     => $validated['items'],
                'notes'     => $notes,
            ]);

            // Traiter le paiement si un mode est fourni
            $payment = null;
            if (!empty($validated['payment_method'])) {
                $method = PaymentMethod::from($validated['payment_method']);
                $payment = match($method) {
                    PaymentMethod::CASH => $this->paymentService->processCashPayment(
                        $order,
                        $validated['amount_received'] ?? $order->getRemainingAmount(),
                    ),
                    PaymentMethod::CARD => $this->paymentService->processCardPayment($order),
                    default             => $this->paymentService->processMobilePayment($order, $method),
                };
            }

            $receiptUrl = $payment
                ? route('admin.payments.receipt', [$tenantSlug, $payment->id])
                : route('admin.comptoir.receipt', [$tenantSlug, $order->id]);

            $message = $payment
                ? "Commande {$order->order_number} créée et encaissée !"
                : "Commande {$order->order_number} créée avec succès !";

            return response()->json([
                'success'      => true,
                'message'      => $message,
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'total'        => $order->total,
                'receipt_url'  => $receiptUrl,
                'payment_id'   => $payment?->id,
                'change'       => $payment?->change_given ?? 0,
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
}
