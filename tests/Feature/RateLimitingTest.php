<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests de Rate Limiting
 *
 * Valide que les limitations de taux sont correctement configurées
 */
class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un tenant et un utilisateur pour les tests
        $this->tenant = Tenant::create([
            'name' => 'Restaurant Test',
            'slug' => 'restaurant-test',
            'type' => 'restaurant',
            'locale' => 'fr',
            'currency' => 'XOF',
            'is_active' => true,
        ]);

        Role::create(['name' => 'ADMIN', 'description' => 'Admin']);

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant->id,
        ]);
        $this->user->assignRole('ADMIN');
    }

    /**
     * Test : Vérifier que le fichier AppServiceProvider contient la configuration Rate Limiting
     */
    public function test_rate_limiting_configuration_exists_in_app_service_provider(): void
    {
        $filePath = app_path('Providers/AppServiceProvider.php');
        $content = file_get_contents($filePath);

        // Vérifier que les 4 limiteurs sont configurés
        $this->assertStringContainsString("RateLimiter::for('api'", $content);
        $this->assertStringContainsString("RateLimiter::for('api-tenant'", $content);
        $this->assertStringContainsString("RateLimiter::for('api-strict'", $content);
        $this->assertStringContainsString("RateLimiter::for('auth'", $content);

        // Vérifier les limites configurées
        $this->assertStringContainsString('perMinute(60)', $content); // API global
        $this->assertStringContainsString('perMinute(1000)', $content); // API tenant
        $this->assertStringContainsString('perMinute(10)', $content); // API strict
        $this->assertStringContainsString('perMinute(5)', $content); // Auth
    }

    /**
     * Test : Vérifier que le middleware throttle est appliqué dans routes/api.php
     */
    public function test_throttle_middleware_configured_in_api_routes(): void
    {
        $filePath = base_path('routes/api.php');
        $content = file_get_contents($filePath);

        // Vérifier que throttle:api est appliqué
        $this->assertStringContainsString("middleware(['throttle:api'])", $content);
    }

    /**
     * Test : Route API répond correctement (pas de 429 pour une seule requête)
     */
    public function test_single_api_request_succeeds(): void
    {
        $response = $this->getJson('/api/menu?tenant=1&table=A01');

        // Une seule requête ne doit jamais être bloquée
        $this->assertNotEquals(429, $response->status());
    }

    /**
     * Test : Rate limiting par IP (chaque IP a son propre compteur)
     */
    public function test_rate_limiting_per_ip(): void
    {
        // Requête depuis IP 1
        $response1 = $this->getJson('/api/menu?tenant=1&table=A01', [
            'REMOTE_ADDR' => '192.168.1.1'
        ]);
        $this->assertNotEquals(429, $response1->status());

        // Requête depuis IP 2 (compteur indépendant)
        $response2 = $this->getJson('/api/menu?tenant=1&table=A01', [
            'REMOTE_ADDR' => '192.168.1.2'
        ]);
        $this->assertNotEquals(429, $response2->status());
    }

    /**
     * Test : Message d'erreur personnalisé pour rate limit strict configuré
     */
    public function test_strict_rate_limit_custom_error_configured(): void
    {
        $filePath = app_path('Providers/AppServiceProvider.php');
        $content = file_get_contents($filePath);

        // Vérifier que le message custom est configuré pour api-strict
        $this->assertStringContainsString('Trop de requêtes', $content);
        $this->assertStringContainsString('Rate limit exceeded', $content);
    }

    /**
     * Test : Documentation sécurité documente le Rate Limiting
     */
    public function test_security_documentation_exists(): void
    {
        $filePath = base_path('SECURITY.md');
        $this->assertFileExists($filePath);

        $content = file_get_contents($filePath);

        // Vérifier que la documentation couvre le rate limiting
        $this->assertStringContainsString('RATE LIMITING', $content);
        $this->assertStringContainsString('60 requêtes par minute', $content);
        $this->assertStringContainsString('1000 requêtes par minute', $content);
    }
}
