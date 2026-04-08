<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'PENDING';       // En attente de paiement
    case PAID = 'PAID';             // Payé intégralement
    case PARTIAL = 'PARTIAL';       // Paiement partiel
    case REFUNDED = 'REFUNDED';     // Remboursé
    case CANCELLED = 'CANCELLED';   // Annulé

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'En attente',
            self::PAID => 'Payé',
            self::PARTIAL => 'Partiel',
            self::REFUNDED => 'Remboursé',
            self::CANCELLED => 'Annulé',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'yellow',
            self::PAID => 'green',
            self::PARTIAL => 'orange',
            self::REFUNDED => 'blue',
            self::CANCELLED => 'red',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::PENDING => 'bg-yellow-100 text-yellow-800',
            self::PAID => 'bg-green-100 text-green-800',
            self::PARTIAL => 'bg-orange-100 text-orange-800',
            self::REFUNDED => 'bg-blue-100 text-blue-800',
            self::CANCELLED => 'bg-red-100 text-red-800',
        };
    }

    public static function unpaidStatuses(): array
    {
        return [self::PENDING, self::PARTIAL];
    }
}
