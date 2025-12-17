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
        $superAdmin = User::firstOrCreate([
            'email' => 'superadmin@example.com'
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
            'tenant_id' => null
        ]);

        $superAdmin->assignRole('SUPER_ADMIN');

        // Créer un admin pour le tenant demo
        $admin = User::firstOrCreate([
            'email' => 'admin@demo.com'
        ], [
            'name' => 'Admin Demo',
            'password' => Hash::make('password'),
            'tenant_id' => $tenant->id
        ]);

        $admin->assignRole('ADMIN');

        // Lancer le seeder des tables
        $this->call(TableSeeder::class);

        // Lancer le seeder du menu
        $this->call(MenuSeeder::class);

        // Lancer le seeder des commandes
        $this->call(OrderSeeder::class);
    }
}
