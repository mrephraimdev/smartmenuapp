<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Dish;
use App\Models\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MenuImport implements ToCollection, WithCustomCsvSettings, WithHeadingRow
{
    protected Tenant $tenant;

    protected int $menuId;

    protected int $imported = 0;

    protected int $skipped = 0;

    protected array $errors = [];

    public function __construct(Tenant $tenant, int $menuId)
    {
        $this->tenant = $tenant;
        $this->menuId = $menuId;
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ',',
            'input_encoding' => 'UTF-8',
        ];
    }

    protected function normalizeKey(string $key): string
    {
        return Str::slug($key, '_');
    }

    protected function buildLookup($row): array
    {
        $lookup = [];
        foreach ($row as $key => $value) {
            $lookup[$this->normalizeKey((string) $key)] = $value;
        }

        return $lookup;
    }

    /**
     * Process the collection of rows from the Excel file.
     * WithHeadingRow normalizes headers to lowercase with underscores,
     * so "Nom Plat" becomes "nom_plat", "Catégorie" becomes "categorie", etc.
     */
    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because row 1 is the heading

            try {
                // Normalize values — accepte plusieurs variantes de noms de colonnes
                $categorieName = trim((string) ($row['categorie'] ?? $row['category'] ?? ''));
                $nomPlat = trim((string) ($row['nom_plat'] ?? $row['name'] ?? $row['nom'] ?? ''));
                $description = trim((string) ($row['description'] ?? ''));
                $prix = (float) str_replace(',', '.', (string) ($row['prix'] ?? $row['price'] ?? 0));
                $actif = isset($row['actif']) && $row['actif'] !== '' ? (bool) (int) $row['actif'] : true;
                $imageUrl = trim((string) ($row['image_url'] ?? $row['image'] ?? ''));

                // Skip rows missing required fields
                if ($categorieName === '' || $nomPlat === '' || $prix <= 0) {
                    $this->skipped++;
                    continue;
                }

                // Find or create the category (bypassing global tenant scope since we query explicitly)
                $category = Category::withoutGlobalScope('tenant')
                    ->where('menu_id', $this->menuId)
                    ->where('name', $categorieName)
                    ->first();

                if (! $category) {
                    // Create without global scope interference (Category scopes via menu relation)
                    $category = new Category();
                    $category->menu_id = $this->menuId;
                    $category->name = $categorieName;
                    $category->sort_order = 0;
                    $category->saveQuietly();
                }

                // Skip if dish with same name already exists in this category
                $existingDish = Dish::withoutGlobalScope('tenant')
                    ->where('category_id', $category->id)
                    ->where('name', $nomPlat)
                    ->first();

                if ($existingDish) {
                    $this->skipped++;
                    continue;
                }

                // Create the dish — pass tenant_id explicitly so the creating hook doesn't need auth
                $dish = new Dish();
                $dish->tenant_id = $this->tenant->id;
                $dish->category_id = $category->id;
                $dish->name = $nomPlat;
                $dish->description = $description !== '' ? $description : null;
                $dish->price_base = $prix;
                $dish->active = $actif;
                $dish->photo_url = $imageUrl !== '' && filter_var($imageUrl, FILTER_VALIDATE_URL) ? $imageUrl : null;
                $dish->saveQuietly();

                $this->imported++;

            } catch (\Throwable $e) {
                $this->errors[] = "Ligne {$rowNumber} : " . $e->getMessage();
                $this->skipped++;
            }
        }
    }

    /**
     * Download image from URL and store it, then update the dish's photo_url.
     */
    protected function downloadAndStoreImage(Dish $dish, string $url): void
    {
        try {
            $contents = @file_get_contents($url);

            if ($contents === false || strlen($contents) === 0) {
                $this->errors[] = "Plat \"{$dish->name}\" : impossible de télécharger l'image depuis {$url}";

                return;
            }

            $filename = "dishes/{$this->tenant->id}/dish_{$dish->id}_" . time() . '.jpg';
            Storage::disk('public')->put($filename, $contents);

            $dish->update(['photo_url' => Storage::disk('public')->url($filename)]);

        } catch (\Throwable $e) {
            $this->errors[] = "Plat \"{$dish->name}\" : erreur image — " . $e->getMessage();
        }
    }

    // ── Getters ──────────────────────────────────────────────────────────────

    public function getImported(): int
    {
        return $this->imported;
    }

    public function getSkipped(): int
    {
        return $this->skipped;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
