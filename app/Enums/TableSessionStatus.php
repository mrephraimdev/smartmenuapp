<?php

namespace App\Enums;

enum TableSessionStatus: string
{
    case ACTIVE = 'ACTIVE';
    case CLOSED = 'CLOSED';
    case EXPIRED = 'EXPIRED';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE  => 'Active',
            self::CLOSED  => 'Fermée',
            self::EXPIRED => 'Expirée',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE  => 'green',
            self::CLOSED  => 'gray',
            self::EXPIRED => 'yellow',
        };
    }
}
