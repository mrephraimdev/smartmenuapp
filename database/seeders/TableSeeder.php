<?php

namespace Database\Seeders;

use App\Models\Table;
use App\Models\Tenant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer des tables pour le tenant de démonstration
        $tenant = Tenant::where('slug', 'restaurant-demo')->first();

        if ($tenant) {
            $tables = [
                ['code' => 'A01', 'label' => 'Table A1', 'capacity' => 4, 'is_active' => true],
                ['code' => 'A02', 'label' => 'Table A2', 'capacity' => 4, 'is_active' => true],
                ['code' => 'A03', 'label' => 'Table A3', 'capacity' => 6, 'is_active' => true],
                ['code' => 'A04', 'label' => 'Table A4', 'capacity' => 4, 'is_active' => true],
                ['code' => 'A05', 'label' => 'Table A5', 'capacity' => 2, 'is_active' => true],
                ['code' => 'B01', 'label' => 'Table B1', 'capacity' => 8, 'is_active' => true],
                ['code' => 'B02', 'label' => 'Table B2', 'capacity' => 6, 'is_active' => true],
                ['code' => 'B03', 'label' => 'Table B3', 'capacity' => 4, 'is_active' => true],
                ['code' => 'C01', 'label' => 'Terrasse 1', 'capacity' => 4, 'is_active' => true],
                ['code' => 'C02', 'label' => 'Terrasse 2', 'capacity' => 6, 'is_active' => true],
            ];

            foreach ($tables as $tableData) {
                Table::firstOrCreate([
                    'tenant_id' => $tenant->id,
                    'code' => $tableData['code']
                ], $tableData);
            }
        }
    }
}
