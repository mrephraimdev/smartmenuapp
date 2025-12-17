<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $themes = [
            [
                'name' => 'Mariage Élégant',
                'slug' => 'wedding-elegant',
                'description' => 'Thème romantique avec tons pastel et touches dorées',
                'colors' => json_encode([
                    'primary' => '#C9A227',
                    'secondary' => '#1A1A1A',
                    'accent' => '#D4AF37',
                    'background' => '#FFFFFF',
                    'text' => '#333333'
                ]),
                'fonts' => json_encode([
                    'heading' => 'Playfair Display',
                    'body' => 'Inter'
                ]),
                'category' => 'wedding',
                'is_default' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Restaurant Moderne',
                'slug' => 'restaurant-modern',
                'description' => 'Design contemporain avec couleurs vives',
                'colors' => json_encode([
                    'primary' => '#FF6B35',
                    'secondary' => '#2D3748',
                    'accent' => '#4FD1C4',
                    'background' => '#F7FAFC',
                    'text' => '#1A202C'
                ]),
                'fonts' => json_encode([
                    'heading' => 'Montserrat',
                    'body' => 'Roboto'
                ]),
                'category' => 'restaurant',
                'is_default' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Mariage Luxe',
                'slug' => 'wedding-luxury',
                'description' => 'Thème premium avec or et noir',
                'colors' => json_encode([
                    'primary' => '#D4AF37',
                    'secondary' => '#000000',
                    'accent' => '#C9A227',
                    'background' => '#FFFFFF',
                    'text' => '#333333'
                ]),
                'fonts' => json_encode([
                    'heading' => 'Cinzel',
                    'body' => 'Lato'
                ]),
                'category' => 'wedding',
                'is_default' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Restaurant Classique',
                'slug' => 'restaurant-classic',
                'description' => 'Style traditionnel avec bois et tons chauds',
                'colors' => json_encode([
                    'primary' => '#8B4513',
                    'secondary' => '#2F4F4F',
                    'accent' => '#DAA520',
                    'background' => '#F5F5DC',
                    'text' => '#333333'
                ]),
                'fonts' => json_encode([
                    'heading' => 'Times New Roman',
                    'body' => 'Georgia'
                ]),
                'category' => 'restaurant',
                'is_default' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('themes')->insert($themes);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('themes')->whereIn('slug', [
            'wedding-elegant',
            'restaurant-modern',
            'wedding-luxury',
            'restaurant-classic'
        ])->delete();
    }
};
