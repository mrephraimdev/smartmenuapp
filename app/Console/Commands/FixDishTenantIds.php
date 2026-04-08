<?php

namespace App\Console\Commands;

use App\Models\Dish;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixDishTenantIds extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fix:dish-tenant-ids';

    /**
     * The console command description.
     */
    protected $description = 'Fix dishes with null tenant_id by assigning them to their category\'s menu tenant';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Recherche des plats sans tenant_id...');

        $dishesWithoutTenant = Dish::withoutGlobalScope('tenant')
            ->whereNull('tenant_id')
            ->count();

        if ($dishesWithoutTenant === 0) {
            $this->info('✅ Tous les plats ont déjà un tenant_id assigné!');
            return Command::SUCCESS;
        }

        $this->warn("⚠️ Trouvé {$dishesWithoutTenant} plats sans tenant_id");

        if (!$this->confirm('Voulez-vous corriger ces plats?', true)) {
            $this->info('❌ Opération annulée');
            return Command::FAILURE;
        }

        $this->info('🔧 Correction en cours...');

        // Corriger les plats en assignant le tenant_id de leur menu
        $updated = DB::table('dishes as d')
            ->join('categories as c', 'd.category_id', '=', 'c.id')
            ->join('menus as m', 'c.menu_id', '=', 'm.id')
            ->whereNull('d.tenant_id')
            ->whereNotNull('m.tenant_id')
            ->update(['d.tenant_id' => DB::raw('m.tenant_id')]);

        $this->info("✅ {$updated} plats mis à jour avec succès!");

        // Vérifier s'il reste des plats sans tenant
        $remaining = Dish::withoutGlobalScope('tenant')
            ->whereNull('tenant_id')
            ->count();

        if ($remaining > 0) {
            $this->error("⚠️ {$remaining} plats n'ont pas pu être corrigés (catégories orphelines?)");

            $orphanDishes = Dish::withoutGlobalScope('tenant')
                ->whereNull('tenant_id')
                ->with('category')
                ->get();

            $this->table(
                ['ID', 'Nom', 'Category ID', 'Category'],
                $orphanDishes->map(fn($dish) => [
                    $dish->id,
                    $dish->name,
                    $dish->category_id,
                    $dish->category?->name ?? 'N/A'
                ])
            );

            return Command::FAILURE;
        }

        $this->info('🎉 Tous les plats ont maintenant un tenant_id valide!');
        return Command::SUCCESS;
    }
}
