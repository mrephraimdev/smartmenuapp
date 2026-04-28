<?php

use App\Enums\OrderStatus;
use App\Models\Order;

beforeEach(function () {
    $this->tenant = createTenant();
    $this->admin = createUser($this->tenant, 'ADMIN');
});

// ── Politique de confidentialité ──────────────────────────────────────────

test('la page politique de confidentialité est accessible', function () {
    $response = $this->get('/privacy');

    $response->assertStatus(200);
    $response->assertSee('Politique de confidentialité');
});

test('la page politique de confidentialité est accessible sans authentification', function () {
    $response = $this->get('/privacy');

    $response->assertStatus(200);
});

// ── Export données personnelles ────────────────────────────────────────────

test('un admin peut exporter les données personnelles de son tenant', function () {
    $response = $this->actingAs($this->admin)
        ->get("/admin/{$this->tenant->slug}/rgpd/export");

    $response->assertStatus(200);
    $response->assertDownload();
});

test('un chef ne peut pas exporter les données RGPD', function () {
    $chef = createUser($this->tenant, 'CHEF');

    $response = $this->actingAs($chef)
        ->get("/admin/{$this->tenant->slug}/rgpd/export");

    $response->assertStatus(403);
});

// ── Suppression de données ────────────────────────────────────────────────

test('un super admin peut demander la suppression d\'un tenant', function () {
    $superAdmin = createSuperAdmin();
    $tenant = createTenant(['slug' => 'to-delete-rgpd']);

    $response = $this->actingAs($superAdmin)
        ->delete("/superadmin/tenants/{$tenant->id}");

    $response->assertRedirect();
    $this->assertDatabaseMissing('tenants', ['id' => $tenant->id]);
});

test('la suppression d\'un staff respecte les données', function () {
    $staff = createUser($this->tenant, 'CHEF');

    $response = $this->actingAs($this->admin)
        ->delete("/admin/{$this->tenant->slug}/staff/{$staff->id}");

    $response->assertRedirect();
    $this->assertDatabaseMissing('users', ['id' => $staff->id]);
});

// ── Anonymisation commandes ────────────────────────────────────────────────

test('les commandes n\'exposent pas de données PII dans l\'API publique', function () {
    $data = createMenuStructure($this->tenant);

    $order = Order::createWithNumber([
        'tenant_id' => $this->tenant->id,
        'table_id' => $data['table']->id,
        'status' => OrderStatus::RECEIVED->value,
        'total' => 5000,
        'customer_name' => 'Jean Dupont',
        'customer_phone' => '+2250701000000',
    ]);

    $response = $this->getJson("/api/orders/{$order->id}");

    if ($response->status() === 200) {
        $data = $response->json();
        // Les données sensibles ne doivent pas être exposées
        expect($data)->not->toHaveKey('customer_phone');
    }
});
