<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'CASH';                     // Espèces
    case CARD = 'CARD';                     // Carte bancaire
    case ORANGE_MONEY = 'ORANGE_MONEY';     // Orange Money CI
    case MTN_MOMO = 'MTN_MOMO';             // MTN Mobile Money
    case MOOV_MONEY = 'MOOV_MONEY';         // Moov Money
    case WAVE = 'WAVE';                     // Wave

    public function label(): string
    {
        return match($this) {
            self::CASH => 'Espèces',
            self::CARD => 'Carte bancaire',
            self::ORANGE_MONEY => 'Orange Money',
            self::MTN_MOMO => 'MTN MoMo',
            self::MOOV_MONEY => 'Moov Money',
            self::WAVE => 'Wave',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::CASH => 'banknotes',
            self::CARD => 'credit-card',
            self::ORANGE_MONEY => 'device-phone-mobile',
            self::MTN_MOMO => 'device-phone-mobile',
            self::MOOV_MONEY => 'device-phone-mobile',
            self::WAVE => 'device-phone-mobile',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::CASH => 'green',
            self::CARD => 'blue',
            self::ORANGE_MONEY => 'orange',
            self::MTN_MOMO => 'yellow',
            self::MOOV_MONEY => 'blue',
            self::WAVE => 'cyan',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::CASH => 'bg-green-100 text-green-800',
            self::CARD => 'bg-blue-100 text-blue-800',
            self::ORANGE_MONEY => 'bg-orange-100 text-orange-800',
            self::MTN_MOMO => 'bg-yellow-100 text-yellow-800',
            self::MOOV_MONEY => 'bg-indigo-100 text-indigo-800',
            self::WAVE => 'bg-cyan-100 text-cyan-800',
        };
    }

    /**
     * Méthodes disponibles pour le paiement en caisse (admin)
     */
    public static function cashierMethods(): array
    {
        return [
            self::CASH,
            self::CARD,
            self::ORANGE_MONEY,
            self::MTN_MOMO,
            self::MOOV_MONEY,
            self::WAVE,
        ];
    }

    /**
     * Méthodes disponibles pour le paiement client (mobile)
     * Pour l'instant désactivé - sera activé plus tard
     */
    public static function clientMethods(): array
    {
        return [
            // self::ORANGE_MONEY,
            // self::MTN_MOMO,
            // self::WAVE,
        ];
    }

    /**
     * Vérifie si la méthode nécessite une intégration API externe
     */
    public function requiresApiIntegration(): bool
    {
        return match($this) {
            self::CASH, self::CARD => false,
            default => true,
        };
    }
}
