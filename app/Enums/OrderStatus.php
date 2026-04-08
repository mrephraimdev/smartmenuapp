<?php

namespace App\Enums;

enum OrderStatus: string
{
    case RECEIVED = 'RECU';
    case PREPARING = 'PREP';
    case READY = 'PRET';
    case SERVED = 'SERVI';
    case CANCELLED = 'ANNULE';

    public function label(): string
    {
        return match($this) {
            self::RECEIVED => 'Reçue',
            self::PREPARING => 'En préparation',
            self::READY => 'Prête',
            self::SERVED => 'Servie',
            self::CANCELLED => 'Annulée',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::RECEIVED => 'blue',
            self::PREPARING => 'yellow',
            self::READY => 'green',
            self::SERVED => 'gray',
            self::CANCELLED => 'red',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::RECEIVED => 'heroicon-o-inbox',
            self::PREPARING => 'heroicon-o-fire',
            self::READY => 'heroicon-o-check-circle',
            self::SERVED => 'heroicon-o-check',
            self::CANCELLED => 'heroicon-o-x-circle',
        };
    }

    public static function activeStatuses(): array
    {
        return [
            self::RECEIVED,
            self::PREPARING,
            self::READY,
        ];
    }

    public static function activeValues(): array
    {
        return array_map(fn($status) => $status->value, self::activeStatuses());
    }

    public function nextStatus(): ?self
    {
        return match($this) {
            self::RECEIVED => self::PREPARING,
            self::PREPARING => self::READY,
            self::READY => self::SERVED,
            default => null,
        };
    }
}
