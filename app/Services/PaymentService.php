<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Traiter un paiement en espèces
     */
    public function processCashPayment(
        Order $order,
        float $amountReceived,
        ?string $notes = null
    ): Payment {
        return DB::transaction(function () use ($order, $amountReceived, $notes) {
            $amountToPay = $order->getRemainingAmount();
            $changeGiven = max(0, $amountReceived - $amountToPay);

            // Créer le paiement
            $payment = Payment::create([
                'order_id' => $order->id,
                'tenant_id' => $order->tenant_id,
                'amount' => $amountToPay,
                'method' => PaymentMethod::CASH->value,
                'status' => 'SUCCESS',
                'transaction_id' => Payment::generateTransactionId(),
                'amount_received' => $amountReceived,
                'change_given' => $changeGiven,
                'processed_by' => auth()->id(),
                'processed_at' => now(),
                'notes' => $notes,
            ]);

            // Mettre à jour la commande
            $this->updateOrderPaymentStatus($order, $amountToPay);

            return $payment;
        });
    }

    /**
     * Traiter un paiement par carte
     */
    public function processCardPayment(
        Order $order,
        ?string $transactionId = null,
        ?string $notes = null
    ): Payment {
        return DB::transaction(function () use ($order, $transactionId, $notes) {
            $amountToPay = $order->getRemainingAmount();

            $payment = Payment::create([
                'order_id' => $order->id,
                'tenant_id' => $order->tenant_id,
                'amount' => $amountToPay,
                'method' => PaymentMethod::CARD->value,
                'status' => 'SUCCESS',
                'transaction_id' => $transactionId ?? Payment::generateTransactionId(),
                'processed_by' => auth()->id(),
                'processed_at' => now(),
                'notes' => $notes,
            ]);

            $this->updateOrderPaymentStatus($order, $amountToPay);

            return $payment;
        });
    }

    /**
     * Traiter un paiement mobile money (enregistré manuellement par le caissier)
     */
    public function processMobilePayment(
        Order $order,
        PaymentMethod $method,
        ?string $transactionId = null,
        ?string $notes = null
    ): Payment {
        return DB::transaction(function () use ($order, $method, $transactionId, $notes) {
            $amountToPay = $order->getRemainingAmount();

            $payment = Payment::create([
                'order_id' => $order->id,
                'tenant_id' => $order->tenant_id,
                'amount' => $amountToPay,
                'method' => $method->value,
                'status' => 'SUCCESS',
                'transaction_id' => $transactionId ?? Payment::generateTransactionId(),
                'processed_by' => auth()->id(),
                'processed_at' => now(),
                'notes' => $notes,
            ]);

            $this->updateOrderPaymentStatus($order, $amountToPay);

            return $payment;
        });
    }

    /**
     * Mettre à jour le statut de paiement de la commande
     */
    protected function updateOrderPaymentStatus(Order $order, float $paidAmount): void
    {
        $newPaidAmount = $order->paid_amount + $paidAmount;
        $total = (float) $order->total;

        if ($newPaidAmount >= $total) {
            $order->update([
                'paid_amount' => $total,
                'payment_status' => PaymentStatus::PAID->value,
                'paid_at' => now(),
            ]);
        } else {
            $order->update([
                'paid_amount' => $newPaidAmount,
                'payment_status' => PaymentStatus::PARTIAL->value,
            ]);
        }
    }

    /**
     * Annuler/Rembourser un paiement
     */
    public function refundPayment(Payment $payment, ?string $reason = null): Payment
    {
        return DB::transaction(function () use ($payment, $reason) {
            $order = $payment->order;

            // Mettre à jour le paiement
            $payment->update([
                'status' => 'REFUNDED',
                'notes' => $payment->notes . "\n[REMBOURSEMENT] " . ($reason ?? 'Pas de raison spécifiée'),
            ]);

            // Mettre à jour le montant payé de la commande
            $newPaidAmount = max(0, $order->paid_amount - $payment->amount);
            $paymentStatus = $newPaidAmount <= 0
                ? PaymentStatus::REFUNDED->value
                : PaymentStatus::PARTIAL->value;

            $order->update([
                'paid_amount' => $newPaidAmount,
                'payment_status' => $paymentStatus,
                'paid_at' => null,
            ]);

            return $payment->fresh();
        });
    }

    /**
     * Obtenir les statistiques de paiement pour un tenant
     */
    public function getPaymentStats(int $tenantId, ?string $date = null): array
    {
        $query = Payment::where('tenant_id', $tenantId)
            ->where('status', 'SUCCESS');

        if ($date) {
            $query->whereDate('created_at', $date);
        } else {
            $query->whereDate('created_at', now());
        }

        $payments = $query->get();

        $byMethod = [];
        foreach (PaymentMethod::cashierMethods() as $method) {
            $byMethod[$method->value] = $payments
                ->where('method', $method->value)
                ->sum('amount');
        }

        return [
            'total' => $payments->sum('amount'),
            'count' => $payments->count(),
            'by_method' => $byMethod,
            'cash' => $byMethod[PaymentMethod::CASH->value] ?? 0,
            'card' => $byMethod[PaymentMethod::CARD->value] ?? 0,
            'mobile' => $payments->whereIn('method', [
                PaymentMethod::ORANGE_MONEY->value,
                PaymentMethod::MTN_MOMO->value,
                PaymentMethod::MOOV_MONEY->value,
                PaymentMethod::WAVE->value,
            ])->sum('amount'),
        ];
    }

    /**
     * Obtenir les commandes impayées pour un tenant
     */
    public function getUnpaidOrders(int $tenantId): \Illuminate\Database\Eloquent\Collection
    {
        return Order::where('tenant_id', $tenantId)
            ->whereIn('payment_status', [
                PaymentStatus::PENDING->value,
                PaymentStatus::PARTIAL->value,
            ])
            ->where('status', '!=', 'ANNULE')
            ->with(['table', 'items.dish'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Obtenir le montant total impayé pour un tenant
     */
    public function getTotalUnpaid(int $tenantId): float
    {
        return Order::where('tenant_id', $tenantId)
            ->whereIn('payment_status', [
                PaymentStatus::PENDING->value,
                PaymentStatus::PARTIAL->value,
            ])
            ->where('status', '!=', 'ANNULE')
            ->selectRaw('SUM(total - paid_amount) as unpaid')
            ->value('unpaid') ?? 0;
    }
}
