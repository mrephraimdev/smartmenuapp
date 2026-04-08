<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates 3 predefined professional themes for Phase 2.
     */
    public function run(): void
    {
        $themes = [
            // Theme 1: Mariage Elegant
            [
                'name' => 'Mariage Élégant',
                'description' => 'Un thème raffiné avec des tons dorés et ivoire, parfait pour les mariages et événements élégants.',
                'category' => 'wedding',
                'primary_color' => '#D4AF37',      // Or brillant
                'secondary_color' => '#F4E4C1',    // Or pâle
                'background_color' => '#FAFAF8',   // Blanc cassé
                'text_color' => '#2C2C2C',         // Noir doux
                'accent_color' => '#8B7355',       // Bronze
                'font_family_heading' => 'Playfair Display',
                'font_family_body' => 'Lato',
                'border_radius' => '8px',
                'is_active' => true,
                'settings' => json_encode([
                    'card_shadow' => '0 4px 6px rgba(212, 175, 55, 0.1)',
                    'button_style' => 'rounded',
                    'header_style' => 'centered',
                    'menu_layout' => 'elegant',
                    'icon_style' => 'outlined',
                    'animations' => true,
                ]),
            ],

            // Theme 2: Bistrot Moderne
            [
                'name' => 'Bistrot Moderne',
                'description' => 'Un look contemporain avec des accents rouge brique et noir, idéal pour les bistrots et restaurants modernes.',
                'category' => 'restaurant',
                'primary_color' => '#C1440E',      // Rouge brique
                'secondary_color' => '#F5F0E8',    // Beige clair
                'background_color' => '#FFFFFF',   // Blanc
                'text_color' => '#1A1A1A',         // Noir
                'accent_color' => '#2D2D2D',       // Gris foncé
                'font_family_heading' => 'Bebas Neue',
                'font_family_body' => 'Open Sans',
                'border_radius' => '4px',
                'is_active' => true,
                'settings' => json_encode([
                    'card_shadow' => '0 2px 8px rgba(0, 0, 0, 0.08)',
                    'button_style' => 'sharp',
                    'header_style' => 'left-aligned',
                    'menu_layout' => 'grid',
                    'icon_style' => 'solid',
                    'animations' => true,
                ]),
            ],

            // Theme 3: Luxe Gastronomique
            [
                'name' => 'Luxe Gastronomique',
                'description' => 'Un thème premium avec noir profond et or, pour les restaurants gastronomiques haut de gamme.',
                'category' => 'fine_dining',
                'primary_color' => '#D4AF37',      // Or brillant
                'secondary_color' => '#1A1A1A',    // Noir
                'background_color' => '#0A0A0A',   // Noir profond
                'text_color' => '#F5F5F5',         // Blanc cassé
                'accent_color' => '#B8860B',       // Or foncé
                'font_family_heading' => 'Cormorant Garamond',
                'font_family_body' => 'Raleway',
                'border_radius' => '0px',
                'is_active' => true,
                'settings' => json_encode([
                    'card_shadow' => '0 4px 20px rgba(212, 175, 55, 0.15)',
                    'button_style' => 'outlined',
                    'header_style' => 'centered',
                    'menu_layout' => 'luxury',
                    'icon_style' => 'thin',
                    'animations' => true,
                    'dark_mode' => true,
                ]),
            ],

            // Theme 4: Café Chaleureux
            [
                'name' => 'Café Chaleureux',
                'description' => 'Ambiance cosy avec des tons café et crème, parfait pour les cafés et salons de thé.',
                'category' => 'cafe',
                'primary_color' => '#6F4E37',      // Café
                'secondary_color' => '#F5E6D3',    // Crème
                'background_color' => '#FFF8F0',   // Blanc chaud
                'text_color' => '#3C2415',         // Brun foncé
                'accent_color' => '#D4A574',       // Caramel
                'font_family_heading' => 'Abril Fatface',
                'font_family_body' => 'Nunito',
                'border_radius' => '12px',
                'is_active' => true,
                'settings' => json_encode([
                    'card_shadow' => '0 3px 10px rgba(111, 78, 55, 0.1)',
                    'button_style' => 'rounded',
                    'header_style' => 'cozy',
                    'menu_layout' => 'cards',
                    'icon_style' => 'filled',
                    'animations' => true,
                ]),
            ],

            // Theme 5: Fast Food Dynamique
            [
                'name' => 'Fast Food Dynamique',
                'description' => 'Design vibrant et énergique avec rouge et jaune, idéal pour les fast-foods et snacks.',
                'category' => 'fast_food',
                'primary_color' => '#E31837',      // Rouge vif
                'secondary_color' => '#FFC72C',    // Jaune doré
                'background_color' => '#FFFFFF',   // Blanc
                'text_color' => '#1A1A1A',         // Noir
                'accent_color' => '#FF6B35',       // Orange
                'font_family_heading' => 'Fredoka One',
                'font_family_body' => 'Poppins',
                'border_radius' => '16px',
                'is_active' => true,
                'settings' => json_encode([
                    'card_shadow' => '0 4px 12px rgba(227, 24, 55, 0.15)',
                    'button_style' => 'pill',
                    'header_style' => 'bold',
                    'menu_layout' => 'compact',
                    'icon_style' => 'fun',
                    'animations' => true,
                ]),
            ],

            // Theme 6: Corporate Professionnel
            [
                'name' => 'Corporate Professionnel',
                'description' => 'Design sobre et professionnel avec bleu marine, pour les cantines d\'entreprise et événements corporate.',
                'category' => 'corporate',
                'primary_color' => '#1E3A5F',      // Bleu marine
                'secondary_color' => '#E8ECF0',    // Gris clair
                'background_color' => '#F8F9FA',   // Gris très clair
                'text_color' => '#2C3E50',         // Bleu gris
                'accent_color' => '#3498DB',       // Bleu clair
                'font_family_heading' => 'Montserrat',
                'font_family_body' => 'Source Sans Pro',
                'border_radius' => '6px',
                'is_active' => true,
                'settings' => json_encode([
                    'card_shadow' => '0 2px 4px rgba(30, 58, 95, 0.08)',
                    'button_style' => 'professional',
                    'header_style' => 'minimal',
                    'menu_layout' => 'list',
                    'icon_style' => 'outlined',
                    'animations' => false,
                ]),
            ],
        ];

        foreach ($themes as $themeData) {
            Theme::updateOrCreate(
                ['name' => $themeData['name']],
                $themeData
            );
        }

        $this->command->info('Created ' . count($themes) . ' professional themes.');
    }
}
