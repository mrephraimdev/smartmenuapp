<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\Table;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Dish;
use App\Models\Variant;
use App\Models\Option;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'restaurant-demo')->first();

        if ($tenant) {
            $tables = Table::where('tenant_id', $tenant->id)->get();

            // Créer quelques commandes de test
            $ordersData = [
                [
                    'table_code' => 'A01',
                    'status' => 'RECU',
                    'items' => [
                        ['dish_name' => 'Salade César', 'quantity' => 1, 'unit_price' => 4500],
                        ['dish_name' => 'Steak Frites', 'quantity' => 2, 'unit_price' => 12000, 'variant_name' => 'À point'],
                        ['dish_name' => 'Café', 'quantity' => 1, 'unit_price' => 2000, 'variant_name' => 'Expresso'],
                    ]
                ],
                [
                    'table_code' => 'A02',
                    'status' => 'PREP',
                    'items' => [
                        ['dish_name' => 'Poulet Rôti', 'quantity' => 1, 'unit_price' => 9500],
                        ['dish_name' => 'Tiramisu', 'quantity' => 2, 'unit_price' => 4500],
                        ['dish_name' => 'Eau Minérale', 'quantity' => 1, 'unit_price' => 1500, 'variant_name' => 'Gazeuse'],
                    ]
                ],
                [
                    'table_code' => 'A03',
                    'status' => 'PRET',
                    'items' => [
                        ['dish_name' => 'Pâtes Carbonara', 'quantity' => 1, 'unit_price' => 8500],
                        ['dish_name' => 'Fondant au Chocolat', 'quantity' => 1, 'unit_price' => 5000],
                        ['dish_name' => 'Jus de Fruits', 'quantity' => 1, 'unit_price' => 2500, 'variant_name' => 'Orange'],
                    ]
                ],
                [
                    'table_code' => 'B01',
                    'status' => 'SERVI',
                    'items' => [
                        ['dish_name' => 'Poisson du Jour', 'quantity' => 1, 'unit_price' => 11000],
                        ['dish_name' => 'Crème Brûlée', 'quantity' => 1, 'unit_price' => 4000],
                        ['dish_name' => 'Vin Rouge', 'quantity' => 2, 'unit_price' => 3500],
                    ]
                ],
            ];

            foreach ($ordersData as $orderData) {
                $table = $tables->where('code', $orderData['table_code'])->first();

                if ($table) {
                    $total = 0;
                    foreach ($orderData['items'] as $item) {
                        $total += $item['quantity'] * $item['unit_price'];
                    }

                    $order = Order::create([
                        'tenant_id' => $tenant->id,
                        'table_id' => $table->id,
                        'status' => $orderData['status'],
                        'total' => $total,
                        'notes' => null,
                    ]);

                    // Créer les items de commande
                    foreach ($orderData['items'] as $itemData) {
                        $dish = Dish::whereHas('category.menu', function($query) use ($tenant) {
                            $query->where('tenant_id', $tenant->id);
                        })->where('name', $itemData['dish_name'])->first();

                        if ($dish) {
                            $variantId = null;
                            if (isset($itemData['variant_name'])) {
                                $variant = Variant::where('dish_id', $dish->id)
                                                 ->where('name', $itemData['variant_name'])
                                                 ->first();
                                $variantId = $variant ? $variant->id : null;
                            }

                            OrderItem::create([
                                'order_id' => $order->id,
                                'dish_id' => $dish->id,
                                'variant_id' => $variantId,
                                'options' => null,
                                'quantity' => $itemData['quantity'],
                                'unit_price' => $itemData['unit_price'],
                                'notes' => null,
                            ]);
                        }
                    }
                }
            }
        }
    }
}
