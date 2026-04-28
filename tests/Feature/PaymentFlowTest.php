<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;

beforeEach(function () {
    $this->tenant = createTenant();
    $this->caissier = createUser($this->tenant, 'CAISSIER');
    $this->admin = createUser($this->tenant, 'ADMIN');
    $data = createMenuStructure($this->tenant);
    $this->dish = $data['dish'];
    $this->table = $data['table'];
});

test('caissier peut accéder à la page paiements', function () {
    $response = $this->actingAs($this->caissier)
        ->get("/caisse/{$this->tenant->slug}/payments");

    $response->assertStatus(200);
});

test('admin peut accéder à la page paiements', function () {
    $response = $this->actingAs($this->admin)
        ->get("/admin/{$this->tenant->slug}/payments");

    $response->assertStatus(200);
});

test('caissier peut traiter un paiement en espèces', function () {
    $order = Order::createWithNumber([
        'tenant_id' => $this->tenant->id,
        'table_id' => $this->table->id,
        'status' => OrderStatus::SERVED->value,
        'total' => 5000,
    ]);

    $response = $this->actingAs($this->caissier)
        ->postJson("/caisse/{$this->tenant->slug}/payments/order/{$order->id}/pay", [
            'method' => PaymentMethod::CASH->value,
            'amount_received' => 6000,
        ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('payments', [
        'order_id' => $order->id,
        'method' => PaymentMethod::CASH->value,
    ]);
});

test('paiement insuffisant est rejeté', function () {
    $order = Order::createWithNumber([
        'tenant_id' => $this->tenant->id,
        'table_id' => $this->table->id,
        'status' => OrderStatus::SERVED->value,
        'total' => 5000,
    ]);

    $response = $this->actingAs($this->caissier)
        ->postJson("/caisse/{$this->tenant->slug}/payments/order/{$order->id}/pay", [
            'method' => PaymentMethod::CASH->value,
            'amount_received' => 3000,
        ]);

    $response->assertStatus(422);
});

test('paiement d\'une commande déjà payée est rejeté', function () {
    $order = Order::createWithNumber([
        'tenant_id' => $this->tenant->id,
        'table_id' => $this->table->id,
        'status' => OrderStatus::SERVED->value,
        'total' => 5000,
    ]);

    Payment::create([
        'tenant_id' => $this->tenant->id,
        'order_id' => $order->id,
        'amount' => 5000,
        'method' => PaymentMethod::CASH->value,
        'status' => 'SUCCESS',
        'transaction_id' => 'TXN-TEST',
        'amount_received' => 5000,
        'change_given' => 0,
        'processed_by' => $this->caissier->id,
        'processed_at' => now(),
    ]);
    // Mark order as paid so isPaid() returns true
    $order->update(['payment_status' => PaymentStatus::PAID->value]);

    $response = $this->actingAs($this->caissier)
        ->postJson("/caisse/{$this->tenant->slug}/payments/order/{$order->id}/pay", [
            'method' => PaymentMethod::CASH->value,
            'amount_received' => 5000,
        ]);

    $response->assertStatus(422);
});

test('la monnaie est calculée correctement', function () {
    $order = Order::createWithNumber([
        'tenant_id' => $this->tenant->id,
        'table_id' => $this->table->id,
        'status' => OrderStatus::SERVED->value,
        'total' => 4500,
    ]);

    $response = $this->actingAs($this->caissier)
        ->postJson("/caisse/{$this->tenant->slug}/payments/order/{$order->id}/pay", [
            'method' => PaymentMethod::CASH->value,
            'amount_received' => 5000,
        ]);

    $response->assertStatus(200);

    $payment = Payment::where('order_id', $order->id)->first();
    expect($payment)->not->toBeNull();
    expect((int) $payment->change_given)->toBe(500);
});

test('serveur sans permission ne peut pas traiter un paiement', function () {
    $serveur = createUser($this->tenant, 'SERVEUR');

    $order = Order::createWithNumber([
        'tenant_id' => $this->tenant->id,
        'table_id' => $this->table->id,
        'status' => OrderStatus::SERVED->value,
        'total' => 5000,
    ]);

    $response = $this->actingAs($serveur)
        ->postJson("/caisse/{$this->tenant->slug}/payments/order/{$order->id}/pay", [
            'method' => PaymentMethod::CASH->value,
            'amount_received' => 5000,
        ]);

    $response->assertStatus(403);
});
