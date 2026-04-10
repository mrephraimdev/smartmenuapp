<?php

namespace App\Enums;

enum TenantType: string
{
    case RESTAURANT = 'restaurant';
    case WEDDING = 'wedding';
    case EVENT = 'event';
    case HOTEL = 'hotel';
    case CAFE = 'cafe';

    public function label(): string
    {
        return match($this) {
            self::RESTAURANT => 'Restaurant',
            self::WEDDING => 'Mariage',
            self::EVENT => 'Événement',
            self::HOTEL => 'Hôtel',
            self::CAFE => 'Café',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::RESTAURANT => 'heroicon-o-building-storefront',
            self::WEDDING => 'heroicon-o-heart',
            self::EVENT => 'heroicon-o-calendar',
            self::HOTEL => 'heroicon-o-building-office',
            self::CAFE => 'heroicon-o-cup-soda',
        };
    }

    public function defaultTheme(): string
    {
        return match($this) {
            self::RESTAURANT => 'bistrot-moderne',
            self::WEDDING => 'mariage-elegant',
            self::EVENT => 'corporate-clean',
            self::HOTEL => 'luxe-gastronomique',
            self::CAFE => 'bistrot-moderne',
        };
    }
}
