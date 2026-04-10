<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Créer les rôles de base
        $roles = [
            ['name' => 'SUPER_ADMIN', 'description' => 'Super administrateur système'],
            ['name' => 'ADMIN', 'description' => 'Administrateur de tenant'],
            ['name' => 'CHEF', 'description' => 'Chef de cuisine'],
            ['name' => 'SERVEUR', 'description' => 'Serveur'],
            ['name' => 'CLIENT', 'description' => 'Client']
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate($roleData);
        }

        // Créer un tenant de démonstration
        $tenant = Tenant::firstOrCreate([
            'name' => 'Restaurant Demo',
            'slug' => 'restaurant-demo',
            'type' => 'restaurant',
            'currency' => 'FCFA',
            'locale' => 'fr',
            'is_active' => true
        ]);

        // Créer un super admin
        User::updateOrCreate([
            'email' => 'superadmin@smartmenu.com'
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('SmartMenu2026!'),
            'tenant_id' => null,
            'role' => 'SUPER_ADMIN',
        ]);

        // Créer un admin pour le tenant demo
        User::updateOrCreate([
            'email' => 'admin@demo.com'
        ], [
            'name' => 'Admin Demo',
            'password' => Hash::make('password'),
            'tenant_id' => $tenant->id,
            'role' => 'ADMIN',
        ]);

        // Lancer le seeder des tables
        $this->call(TableSeeder::class);

        // Lancer le seeder du menu
        $this->call(MenuSeeder::class);

        // Lancer le seeder des commandes
        $this->call(OrderSeeder::class);
    }
}
