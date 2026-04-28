<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Helpers partagés entre tous les tests Pest
|--------------------------------------------------------------------------
*/

function createTenant(array $overrides = []): \App\Models\Tenant
{
    return \App\Models\Tenant::create(array_merge([
        'name' => 'Restaurant Test',
        'slug' => 'restaurant-test-' . uniqid(),
        'type' => 'RESTAURANT',
        'currency' => 'XOF',
        'locale' => 'fr',
        'is_active' => true,
    ], $overrides));
}

function createUser(\App\Models\Tenant $tenant, string $role, array $overrides = []): \App\Models\User
{
    return \App\Models\User::create(array_merge([
        'name' => ucfirst(strtolower($role)),
        'email' => strtolower($role) . '_' . uniqid() . '@test.com',
        'password' => bcrypt('password'),
        'tenant_id' => $tenant->id,
        'role' => $role,
    ], $overrides));
}

function createSuperAdmin(): \App\Models\User
{
    return \App\Models\User::create([
        'name' => 'Super Admin',
        'email' => 'superadmin_' . uniqid() . '@test.com',
        'password' => bcrypt('password'),
        'role' => 'SUPER_ADMIN',
    ]);
}

function createMenuStructure(\App\Models\Tenant $tenant): array
{
    $menu = \App\Models\Menu::create([
        'tenant_id' => $tenant->id,
        'title' => 'Menu Principal',
        'active' => true,
    ]);

    $category = \App\Models\Category::create([
        'menu_id' => $menu->id,
        'name' => 'Plats Principaux',
        'sort_order' => 1,
    ]);

    $dish = \App\Models\Dish::create([
        'tenant_id' => $tenant->id,
        'category_id' => $category->id,
        'name' => 'Poulet Braisé',
        'price_base' => 5000,
        'active' => true,
    ]);

    $table = \App\Models\Table::create([
        'tenant_id' => $tenant->id,
        'code' => 'T' . substr(uniqid(), -4),
        'label' => 'Table 1',
        'capacity' => 4,
        'is_active' => true,
    ]);

    return compact('menu', 'category', 'dish', 'table');
}
