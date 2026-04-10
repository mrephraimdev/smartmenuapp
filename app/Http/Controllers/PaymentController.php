<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Tenant;
use App\Enums\PaymentMethod;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    /**
     * Liste des paiements pour un tenant
     */
    public function index(Request $request, string $tenantSlug)
    {
        $user = auth()->user();
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        if (!$user->hasRole('SUPER_ADMIN') && $user->tenant_id != $tenant->id) {
            abort(403);
        }

        $query = Payment::where('tenant_id', $tenant->id)
            ->with(['order.table', 'processedBy'])
            ->orderBy('created_at', 'desc');

        // Filtres
        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $payments = $query->paginate(20)->withQueryString();

        // Statistiques du jour
        $stats = $this->paymentService->getPaymentStats($tenant->id);

        // Commandes impayées
        $unpaidOrders = $this->paymentService->getUnpaidOrders($tenant->id);
        $totalUnpaid = $this->paymentService->getTotalUnpaid($tenant->id);

        if ($request->wantsJson()) {
            return response()->json([
                'payments' => $payments->items(),
                'stats' => $stats,
                'unpaid_orders' => $unpaidOrders,
                'total_unpaid' => $totalUnpaid,
            ]);
        }

        return view('admin.payments.index', [
            'payments' => $payments,
            'stats' => $stats,
            'unpaidOrders' => $unpaidOrders,
            'totalUnpaid' => $totalUnpaid,
            'tenantSlug' => $tenantSlug,
            'tenant' => $tenant,
            'paymentMethods' => PaymentMethod::cashierMethods(),
        ]);
    }

    /**
     * Encaisser une commande (modal)
     */
    public function processPayment(Request $request, string $tenantSlug, int $orderId): JsonResponse
    {
        $user = auth()->user();
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        if (!$user->hasRole('SUPER_ADMIN') && $user->tenant_id != $tenant->id) {
            return response()->json(['success' => false, 'error' => 'Accès non autorisé'], 403);
        }

        $order = Order::where('id', $orderId)
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        if ($order->isPaid()) {
            return response()->json([
                'success' => false,
                'error' => 'Cette commande est déjà payée'
            ], 400);
        }

        $validated = $request->validate([
            'method' => 'required|string|in:' . implode(',', array_column(PaymentMethod::cases(), 'value')),
            'amount_received' => 'nullable|numeric|min:0',
            'transaction_id' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $method = PaymentMethod::from($validated['method']);

        try {
            $payment = match($method) {
                PaymentMethod::CASH => $this->paymentService->processCashPayment(
                    $order,
                    $validated['amount_received'] ?? $order->getRemainingAmount(),
                    $validated['notes'] ?? null
                ),
                PaymentMethod::CARD => $this->paymentService->processCardPayment(
                    $order,
                    $validated['transaction_id'] ?? null,
                    $validated['notes'] ?? null
                ),
                default => $this->paymentService->processMobilePayment(
                    $order,
                    $method,
                    $validated['transaction_id'] ?? null,
                    $validated['notes'] ?? null
                ),
            };

            $order->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Paiement enregistré avec succès',
                'payment' => $payment,
                'order' => [
                    'id' => $order->id,
                    'payment_status' => $order->payment_status,
                    'paid_amount' => $order->paid_amount,
                    'remaining' => $order->getRemainingAmount(),
                ],
                'change' => $payment->change_given ?? 0,
                'receipt_url' => route('admin.payments.receipt', [$tenantSlug, $payment->id]),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du paiement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les détails d'une commande pour le modal de paiement
     */
    public function getOrderForPayment(string $tenantSlug, int $orderId): JsonResponse
    {
        $user = auth()->user();
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        if (!$user->hasRole('SUPER_ADMIN') && $user->tenant_id != $tenant->id) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $order = Order::where('id', $orderId)
            ->where('tenant_id', $tenant->id)
            ->with(['table', 'items.dish', 'items.variant', 'payments'])
            ->firstOrFail();

        return response()->json([
            'order' => $order,
            'remaining' => $order->getRemainingAmount(),
            'formatted_total' => $order->getFormattedTotal(),
            'formatted_remaining' => $order->getFormattedRemainingAmount(),
            'payment_methods' => collect(PaymentMethod::cashierMethods())->map(fn($m) => [
                'value' => $m->value,
                'label' => $m->label(),
                'color' => $m->color(),
            ]),
        ]);
    }

    /**
     * Générer le reçu/ticket de paiement
     */
    public function receipt(string $tenantSlug, int $paymentId)
    {
        $user = auth()->user();
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        if (!$user->hasRole('SUPER_ADMIN') && $user->tenant_id != $tenant->id) {
            abort(403);
        }

        $payment = Payment::where('id', $paymentId)
            ->where('tenant_id', $tenant->id)
            ->with(['order.items.dish', 'order.items.variant', 'order.table', 'processedBy'])
            ->firstOrFail();

        return response()
            ->view('prints.payment-receipt', [
                'payment' => $payment,
                'order' => $payment->order,
                'tenant' => $tenant,
            ])
            ->header('Cache-Control', 'no-store');
    }

    /**
     * Statistiques des paiements (API)
     */
    public function stats(Request $request, string $tenantSlug): JsonResponse
    {
        $user = auth()->user();
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        if (!$user->hasRole('SUPER_ADMIN') && $user->tenant_id != $tenant->id) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $date = $request->input('date', now()->toDateString());
        $stats = $this->paymentService->getPaymentStats($tenant->id, $date);
        $totalUnpaid = $this->paymentService->getTotalUnpaid($tenant->id);

        return response()->json([
            'stats' => $stats,
            'total_unpaid' => $totalUnpaid,
        ]);
    }

    /**
     * Liste des commandes impayées (API)
     */
    public function unpaidOrders(string $tenantSlug): JsonResponse
    {
        $user = auth()->user();
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        if (!$user->hasRole('SUPER_ADMIN') && $user->tenant_id != $tenant->id) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $orders = $this->paymentService->getUnpaidOrders($tenant->id);

        return response()->json($orders);
    }
}
