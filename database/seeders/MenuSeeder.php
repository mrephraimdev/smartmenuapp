<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\Menu;
use App\Models\Category;
use App\Models\Dish;
use App\Models\Variant;
use App\Models\Option;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un menu pour le tenant de démonstration
        $tenant = Tenant::where('slug', 'restaurant-demo')->first();

        if ($tenant) {
            // Créer le menu principal
            $menu = Menu::firstOrCreate([
                'tenant_id' => $tenant->id,
                'title' => 'Menu Principal'
            ], [
                'description' => 'Notre carte complète avec tous nos plats',
                'active' => true
            ]);

            // Créer les catégories
            $categories = [
                ['name' => 'Entrées', 'sort_order' => 1],
                ['name' => 'Plats Principaux', 'sort_order' => 2],
                ['name' => 'Desserts', 'sort_order' => 3],
                ['name' => 'Boissons', 'sort_order' => 4],
            ];

            foreach ($categories as $categoryData) {
                $category = Category::firstOrCreate([
                    'menu_id' => $menu->id,
                    'name' => $categoryData['name']
                ], $categoryData);

                // Créer des plats pour chaque catégorie
                $this->createDishesForCategory($category);
            }
        }
    }

    private function createDishesForCategory($category)
    {
        $dishesData = [];

        switch ($category->name) {
            case 'Entrées':
                $dishesData = [
                    [
                        'name' => 'Salade César',
                        'description' => 'Laitue romaine, croûtons, parmesan, sauce César',
                        'price_base' => 4500,
                        'active' => true,
                        'options' => [
                            ['name' => 'Ajouter poulet grillé', 'kind' => 'toggle', 'extra_price' => 2000],
                            ['name' => 'Ajouter crevettes', 'kind' => 'toggle', 'extra_price' => 3500],
                        ]
                    ],
                    [
                        'name' => 'Soupe du jour',
                        'description' => 'Soupe fraîchement préparée selon la saison',
                        'price_base' => 3500,
                        'active' => true,
                        'options' => []
                    ],
                    [
                        'name' => 'Bruschetta',
                        'description' => 'Pain grillé avec tomates, basilic et mozzarella',
                        'price_base' => 4000,
                        'active' => true,
                        'options' => [
                            ['name' => 'Ajouter jambon', 'kind' => 'toggle', 'extra_price' => 1500],
                        ]
                    ],
                ];
                break;

            case 'Plats Principaux':
                $dishesData = [
                    [
                        'name' => 'Steak Frites',
                        'description' => 'Steak de bœuf grillé avec frites maison',
                        'price_base' => 12000,
                        'active' => true,
                        'variants' => [
                            ['name' => 'Bleu', 'extra_price' => 0],
                            ['name' => 'Saignant', 'extra_price' => 0],
                            ['name' => 'À point', 'extra_price' => 0],
                            ['name' => 'Bien cuit', 'extra_price' => 0],
                        ],
                        'options' => [
                            ['name' => 'Sauce au poivre', 'kind' => 'toggle', 'extra_price' => 1000],
                            ['name' => 'Sauce béarnaise', 'kind' => 'toggle', 'extra_price' => 1500],
                        ]
                    ],
                    [
                        'name' => 'Poulet Rôti',
                        'description' => 'Poulet fermier rôti aux herbes avec légumes',
                        'price_base' => 9500,
                        'active' => true,
                        'options' => [
                            ['name' => 'Ajouter frites', 'kind' => 'toggle', 'extra_price' => 1500],
                            ['name' => 'Ajouter riz', 'kind' => 'toggle', 'extra_price' => 1000],
                        ]
                    ],
                    [
                        'name' => 'Pâtes Carbonara',
                        'description' => 'Spaghetti à la crème, lardons et parmesan',
                        'price_base' => 8500,
                        'active' => true,
                        'options' => [
                            ['name' => 'Ajouter champignons', 'kind' => 'toggle', 'extra_price' => 1200],
                            ['name' => 'Ajouter poulet', 'kind' => 'toggle', 'extra_price' => 2000],
                        ]
                    ],
                    [
                        'name' => 'Poisson du Jour',
                        'description' => 'Poisson frais grillé selon arrivage',
                        'price_base' => 11000,
                        'active' => true,
                        'options' => [
                            ['name' => 'Accompagnement riz', 'kind' => 'toggle', 'extra_price' => 1000],
                            ['name' => 'Accompagnement légumes', 'kind' => 'toggle', 'extra_price' => 1500],
                        ]
                    ],
                ];
                break;

            case 'Desserts':
                $dishesData = [
                    [
                        'name' => 'Tiramisu',
                        'description' => 'Classique italien au café et mascarpone',
                        'price_base' => 4500,
                        'active' => true,
                        'options' => []
                    ],
                    [
                        'name' => 'Crème Brûlée',
                        'description' => 'Crème vanille avec caramel croquant',
                        'price_base' => 4000,
                        'active' => true,
                        'options' => []
                    ],
                    [
                        'name' => 'Fondant au Chocolat',
                        'description' => 'Cœur coulant de chocolat noir',
                        'price_base' => 5000,
                        'active' => true,
                        'options' => [
                            ['name' => 'Ajouter boule de glace', 'kind' => 'toggle', 'extra_price' => 1000],
                        ]
                    ],
                ];
                break;

            case 'Boissons':
                $dishesData = [
                    [
                        'name' => 'Eau Minérale',
                        'description' => 'Eau plate ou gazeuse',
                        'price_base' => 1500,
                        'active' => true,
                        'variants' => [
                            ['name' => 'Plate', 'extra_price' => 0],
                            ['name' => 'Gazeuse', 'extra_price' => 0],
                        ],
                        'options' => []
                    ],
                    [
                        'name' => 'Jus de Fruits',
                        'description' => 'Orange, pomme, ananas, mangue',
                        'price_base' => 2500,
                        'active' => true,
                        'variants' => [
                            ['name' => 'Orange', 'extra_price' => 0],
                            ['name' => 'Pomme', 'extra_price' => 0],
                            ['name' => 'Ananas', 'extra_price' => 0],
                            ['name' => 'Mangue', 'extra_price' => 0],
                        ],
                        'options' => []
                    ],
                    [
                        'name' => 'Café',
                        'description' => 'Expresso, café au lait, cappuccino',
                        'price_base' => 2000,
                        'active' => true,
                        'variants' => [
                            ['name' => 'Expresso', 'extra_price' => 0],
                            ['name' => 'Café au lait', 'extra_price' => 500],
                            ['name' => 'Cappuccino', 'extra_price' => 800],
                        ],
                        'options' => []
                    ],
                    [
                        'name' => 'Vin Rouge',
                        'description' => 'Verre de vin rouge de la maison',
                        'price_base' => 3500,
                        'active' => true,
                        'options' => []
                    ],
                ];
                break;
        }

        foreach ($dishesData as $dishData) {
            $dish = Dish::firstOrCreate([
                'category_id' => $category->id,
                'name' => $dishData['name']
            ], [
                'description' => $dishData['description'],
                'price_base' => $dishData['price_base'],
                'active' => $dishData['active']
            ]);

            // Créer les variantes si elles existent
            if (isset($dishData['variants'])) {
                foreach ($dishData['variants'] as $variantData) {
                    Variant::firstOrCreate([
                        'dish_id' => $dish->id,
                        'name' => $variantData['name']
                    ], $variantData);
                }
            }

            // Créer les options si elles existent
            if (isset($dishData['options'])) {
                foreach ($dishData['options'] as $optionData) {
                    Option::firstOrCreate([
                        'dish_id' => $dish->id,
                        'name' => $optionData['name']
                    ], $optionData);
                }
            }
        }
    }
}
