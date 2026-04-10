<?php

namespace App\Enums;

enum ThemeCategory: string
{
    case RESTAURANT = 'RESTAURANT';
    case WEDDING = 'WEDDING';
    case CORPORATE = 'CORPORATE';
    case CAFE = 'CAFE';
    case BAR = 'BAR';
    case FAST_FOOD = 'FAST_FOOD';
    case FINE_DINING = 'FINE_DINING';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match($this) {
            self::RESTAURANT => 'Restaurant',
            self::WEDDING => 'Mariage',
            self::CORPORATE => 'Entreprise',
            self::CAFE => 'Café',
            self::BAR => 'Bar',
            self::FAST_FOOD => 'Fast-Food',
            self::FINE_DINING => 'Gastronomique',
        };
    }

    /**
     * Get description.
     */
    public function description(): string
    {
        return match($this) {
            self::RESTAURANT => 'Thèmes pour restaurants traditionnels',
            self::WEDDING => 'Thèmes élégants pour mariages et événements',
            self::CORPORATE => 'Thèmes professionnels pour événements d\'entreprise',
            self::CAFE => 'Thèmes chaleureux pour cafés et salons de thé',
            self::BAR => 'Thèmes modernes pour bars et lounges',
            self::FAST_FOOD => 'Thèmes dynamiques pour restauration rapide',
            self::FINE_DINING => 'Thèmes luxueux pour restaurants gastronomiques',
        };
    }

    /**
     * Get icon name.
     */
    public function icon(): string
    {
        return match($this) {
            self::RESTAURANT => 'heroicon-o-building-storefront',
            self::WEDDING => 'heroicon-o-heart',
            self::CORPORATE => 'heroicon-o-briefcase',
            self::CAFE => 'heroicon-o-cup-soda',
            self::BAR => 'heroicon-o-beaker',
            self::FAST_FOOD => 'heroicon-o-bolt',
            self::FINE_DINING => 'heroicon-o-sparkles',
        };
    }

    /**
     * Get default colors for this category.
     */
    public function defaultColors(): array
    {
        return match($this) {
            self::RESTAURANT => [
                'primary' => '#C1440E',
                'secondary' => '#1A1A1A',
                'accent' => '#F4B942',
            ],
            self::WEDDING => [
                'primary' => '#F4E4C1',
                'secondary' => '#FAFAF8',
                'accent' => '#D4AF37',
            ],
            self::CORPORATE => [
                'primary' => '#1E3A5F',
                'secondary' => '#F5F5F5',
                'accent' => '#3B82F6',
            ],
            self::CAFE => [
                'primary' => '#6B4423',
                'secondary' => '#FFF8F0',
                'accent' => '#D4A574',
            ],
            self::BAR => [
                'primary' => '#1A1A2E',
                'secondary' => '#16213E',
                'accent' => '#E94560',
            ],
            self::FAST_FOOD => [
                'primary' => '#FF5722',
                'secondary' => '#FFF3E0',
                'accent' => '#FFC107',
            ],
            self::FINE_DINING => [
                'primary' => '#0A0A0A',
                'secondary' => '#1A1A1A',
                'accent' => '#D4AF37',
            ],
        };
    }

    /**
     * Get all categories as array for forms.
     */
    public static function toSelectArray(): array
    {
        return collect(self::cases())->mapWithKeys(function ($case) {
            return [$case->value => $case->label()];
        })->toArray();
    }

    /**
     * Get all values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
