<?php

use App\Models\WaiterCall;

beforeEach(function () {
    $this->tenant = createTenant();
    $this->admin = createUser($this->tenant, 'ADMIN');
    $this->serveur = createUser($this->tenant, 'SERVEUR');
    $data = createMenuStructure($this->tenant);
    $this->table = $data['table'];
});

test('un client peut appeler un serveur', function () {
    $response = $this->postJson('/api/waiter-calls', [
        'tenant_id' => $this->tenant->id,
        'table_id' => $this->table->id,
        'call_type' => 'SERVICE',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('waiter_calls', [
        'tenant_id' => $this->tenant->id,
        'table_id' => $this->table->id,
        'status' => 'PENDING',
    ]);
});

test('l\'appel sans table_id est rejeté', function () {
    $response = $this->postJson('/api/waiter-calls', [
        'tenant_id' => $this->tenant->id,
        'call_type' => 'SERVICE',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('table_id');
});

test('un serveur peut récupérer les appels en attente', function () {
    WaiterCall::create([
        'tenant_id' => $this->tenant->id,
        'table_id' => $this->table->id,
        'call_type' => 'SERVICE',
        'status' => 'PENDING',
    ]);

    $response = $this->actingAs($this->serveur)
        ->getJson('/api/waiter-calls?tenant_id=' . $this->tenant->id);

    $response->assertStatus(200)
        ->assertJsonPath('success', true);
});

test('un serveur peut acquitter un appel', function () {
    $call = WaiterCall::create([
        'tenant_id' => $this->tenant->id,
        'table_id' => $this->table->id,
        'call_type' => 'SERVICE',
        'status' => 'PENDING',
    ]);

    $response = $this->actingAs($this->serveur)
        ->patchJson("/api/waiter-calls/{$call->id}/acknowledge");

    $response->assertStatus(200);
    $this->assertDatabaseHas('waiter_calls', [
        'id' => $call->id,
        'status' => 'ACKNOWLEDGED',
    ]);
});

test('un serveur peut résoudre un appel', function () {
    $call = WaiterCall::create([
        'tenant_id' => $this->tenant->id,
        'table_id' => $this->table->id,
        'call_type' => 'QUESTION',
        'status' => 'ACKNOWLEDGED',
    ]);

    $response = $this->actingAs($this->serveur)
        ->patchJson("/api/waiter-calls/{$call->id}/resolve");

    $response->assertStatus(200);
    $this->assertDatabaseHas('waiter_calls', [
        'id' => $call->id,
        'status' => 'RESOLVED',
    ]);
});
