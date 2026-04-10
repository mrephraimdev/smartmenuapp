<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Category;
use App\Models\Dish;
use App\Models\Variant;
use App\Models\Option;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MenuService
{
    /**
     * Get full menu with categories and dishes
     */
    public function getFullMenu(int $tenantId): ?Menu
    {
        return Menu::with([
            'categories' => function ($query) {
                $query->orderBy('sort_order');
            },
            'categories.dishes' => function ($query) {
                $query->where('active', true)->orderBy('name');
            },
            'categories.dishes.variants',
            'categories.dishes.options'
        ])
        ->where('tenant_id', $tenantId)
        ->where('active', true)
        ->first();
    }

    /**
     * Create a new dish
     */
    public function createDish(array $data): Dish
    {
        return DB::transaction(function () use ($data) {
            $dish = Dish::create([
                'tenant_id' => $data['tenant_id'],
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'price_base' => $data['price_base'],
                'photo_url' => $data['photo_url'] ?? null,
                'allergens' => $data['allergens'] ?? [],
                'tags' => $data['tags'] ?? [],
                'stock_quantity' => $data['stock_quantity'] ?? null,
                'preparation_time_minutes' => $data['preparation_time_minutes'] ?? null,
                'active' => $data['active'] ?? true,
            ]);

            if (!empty($data['variants'])) {
                foreach ($data['variants'] as $variantData) {
                    $dish->variants()->create($variantData);
                }
            }

            if (!empty($data['options'])) {
                foreach ($data['options'] as $optionData) {
                    $dish->options()->create($optionData);
                }
            }

            return $dish->load(['variants', 'options', 'category']);
        });
    }

    /**
     * Update a dish
     */
    public function updateDish(Dish $dish, array $data): Dish
    {
        return DB::transaction(function () use ($dish, $data) {
            $dish->update([
                'category_id' => $data['category_id'] ?? $dish->category_id,
                'name' => $data['name'] ?? $dish->name,
                'description' => $data['description'] ?? $dish->description,
                'price_base' => $data['price_base'] ?? $dish->price_base,
                'photo_url' => $data['photo_url'] ?? $dish->photo_url,
                'allergens' => $data['allergens'] ?? $dish->allergens,
                'tags' => $data['tags'] ?? $dish->tags,
                'stock_quantity' => $data['stock_quantity'] ?? $dish->stock_quantity,
                'preparation_time_minutes' => $data['preparation_time_minutes'] ?? $dish->preparation_time_minutes,
                'active' => $data['active'] ?? $dish->active,
            ]);

            if (isset($data['variants'])) {
                $dish->variants()->delete();
                foreach ($data['variants'] as $variantData) {
                    $dish->variants()->create($variantData);
                }
            }

            if (isset($data['options'])) {
                $dish->options()->delete();
                foreach ($data['options'] as $optionData) {
                    $dish->options()->create($optionData);
                }
            }

            return $dish->fresh(['variants', 'options', 'category']);
        });
    }

    /**
     * Delete a dish
     */
    public function deleteDish(Dish $dish): bool
    {
        return DB::transaction(function () use ($dish) {
            $dish->variants()->delete();
            $dish->options()->delete();
            return $dish->delete();
        });
    }

    /**
     * Duplicate a dish
     */
    public function duplicateDish(Dish $dish): Dish
    {
        return DB::transaction(function () use ($dish) {
            $newDish = $dish->replicate();
            $newDish->name = $dish->name . ' (copie)';
            $newDish->save();

            foreach ($dish->variants as $variant) {
                $newVariant = $variant->replicate();
                $newVariant->dish_id = $newDish->id;
                $newVariant->save();
            }

            foreach ($dish->options as $option) {
                $newOption = $option->replicate();
                $newOption->dish_id = $newDish->id;
                $newOption->save();
            }

            return $newDish->load(['variants', 'options', 'category']);
        });
    }

    /**
     * Update dish availability
     */
    public function updateAvailability(Dish $dish, bool $available): Dish
    {
        $dish->update(['active' => $available]);
        return $dish->fresh();
    }

    /**
     * Update stock quantity
     */
    public function updateStock(Dish $dish, ?int $quantity): Dish
    {
        $dish->update(['stock_quantity' => $quantity]);
        return $dish->fresh();
    }

    /**
     * Decrement stock after order
     */
    public function decrementStock(Dish $dish, int $quantity = 1): Dish
    {
        if ($dish->stock_quantity !== null) {
            $newStock = max(0, $dish->stock_quantity - $quantity);
            $dish->update(['stock_quantity' => $newStock]);
        }
        return $dish->fresh();
    }

    /**
     * Get dishes by category
     */
    public function getDishesByCategory(int $categoryId): Collection
    {
        return Dish::with(['variants', 'options'])
            ->where('category_id', $categoryId)
            ->where('active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get available dishes for a tenant
     */
    public function getAvailableDishes(int $tenantId): Collection
    {
        return Dish::with(['variants', 'options', 'category'])
            ->where('tenant_id', $tenantId)
            ->where('active', true)
            ->where(function ($query) {
                $query->whereNull('stock_quantity')
                      ->orWhere('stock_quantity', '>', 0);
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * Search dishes
     */
    public function searchDishes(int $tenantId, string $query): Collection
    {
        return Dish::with(['variants', 'options', 'category'])
            ->where('tenant_id', $tenantId)
            ->where('active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%");
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a category
     */
    public function createCategory(array $data): Category
    {
        return Category::create([
            'menu_id' => $data['menu_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
    }

    /**
     * Update category sort order
     */
    public function updateCategorySortOrder(array $categoryIds): void
    {
        foreach ($categoryIds as $index => $categoryId) {
            Category::where('id', $categoryId)->update(['sort_order' => $index]);
        }
    }
}
