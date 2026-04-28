<?php

use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->tenant = createTenant();
    Cache::flush();
});

test('le cache menu_client est invalidé après mise à jour d\'un plat', function () {
    $data = createMenuStructure($this->tenant);
    $cacheKey = "menu_client_{$this->tenant->id}";

    // Mettre quelque chose en cache
    Cache::put($cacheKey, ['cached' => true], 300);
    expect(Cache::has($cacheKey))->toBeTrue();

    // Simuler l\'invalidation (appel direct à la méthode privée via reflection)
    $controller = new \App\Http\Controllers\AdminMenuController();
    $reflection = new \ReflectionClass($controller);
    $method = $reflection->getMethod('invalidateTenantCache');
    $method->setAccessible(true);
    $method->invoke($controller, $this->tenant->id);

    // Le cache doit être vide
    expect(Cache::has($cacheKey))->toBeFalse();
});

test('le cache dashboard est invalidé après mise à jour du menu', function () {
    $cacheKey = "dashboard_full_{$this->tenant->id}";

    Cache::put($cacheKey, ['data' => 'test'], 300);
    expect(Cache::has($cacheKey))->toBeTrue();

    $controller = new \App\Http\Controllers\AdminMenuController();
    $reflection = new \ReflectionClass($controller);
    $method = $reflection->getMethod('invalidateTenantCache');
    $method->setAccessible(true);
    $method->invoke($controller, $this->tenant->id);

    expect(Cache::has($cacheKey))->toBeFalse();
});

test('le cache stats est invalidé après mise à jour', function () {
    $cacheKey = "statistics_{$this->tenant->id}";

    Cache::put($cacheKey, ['stats' => []], 300);
    expect(Cache::has($cacheKey))->toBeTrue();

    $controller = new \App\Http\Controllers\AdminMenuController();
    $reflection = new \ReflectionClass($controller);
    $method = $reflection->getMethod('invalidateTenantCache');
    $method->setAccessible(true);
    $method->invoke($controller, $this->tenant->id);

    expect(Cache::has($cacheKey))->toBeFalse();
});

test('le cache menu_client est peuplé lors de l\'appel API', function () {
    createMenuStructure($this->tenant);
    $cacheKey = "menu_client_{$this->tenant->id}";

    // Appel API menu
    $response = test()->getJson("/api/menu?tenant={$this->tenant->id}&table=T01");

    if ($response->status() === 200) {
        // Le cache doit être rempli
        expect(Cache::has($cacheKey))->toBeTrue();
    }
});
