<?php

use App\Models\Reservation;

beforeEach(function () {
    $this->tenant = createTenant();
    $this->admin = createUser($this->tenant, 'ADMIN');
    $data = createMenuStructure($this->tenant);
    $this->table = $data['table'];
});

test('le formulaire de réservation publique est accessible', function () {
    $response = $this->get("/reservation/{$this->tenant->slug}");

    $response->assertStatus(200);
});

test('un client peut soumettre une réservation', function () {
    $response = $this->postJson("/reservation/{$this->tenant->slug}", [
        'customer_name' => 'Jean Dupont',
        'customer_email' => 'jean@example.com',
        'customer_phone' => '+2250701234567',
        'reservation_date' => now()->addDays(3)->format('Y-m-d'),
        'reservation_time' => '19:00',
        'party_size' => 4,
        'special_requests' => 'Table près de la fenêtre svp',
    ]);

    $response->assertStatus(200)->assertJson(['success' => true]);

    $this->assertDatabaseHas('reservations', [
        'tenant_id' => $this->tenant->id,
        'customer_name' => 'Jean Dupont',
        'party_size' => 4,
    ]);
});

test('la réservation sans nom est rejetée', function () {
    $response = $this->postJson("/reservation/{$this->tenant->slug}", [
        'customer_email' => 'jean@example.com',
        'customer_phone' => '+2250701234567',
        'reservation_date' => now()->addDays(3)->format('Y-m-d'),
        'reservation_time' => '19:00',
        'party_size' => 2,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('customer_name');
});

test('la réservation dans le passé est rejetée', function () {
    $response = $this->postJson("/reservation/{$this->tenant->slug}", [
        'customer_name' => 'Jean Dupont',
        'customer_email' => 'jean@example.com',
        'customer_phone' => '+2250701234567',
        'reservation_date' => now()->subDays(1)->format('Y-m-d'),
        'reservation_time' => '19:00',
        'party_size' => 2,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('reservation_date');
});

test('l\'admin peut confirmer une réservation', function () {
    $reservation = Reservation::create([
        'tenant_id' => $this->tenant->id,
        'table_id' => $this->table->id,
        'customer_name' => 'Jean Dupont',
        'customer_email' => 'jean@example.com',
        'customer_phone' => '+2250701234567',
        'reservation_date' => now()->addDays(3)->toDateString(),
        'reservation_time' => '19:00',
        'party_size' => 4,
        'status' => 'PENDING',
        'confirmation_code' => 'TEST001',
    ]);

    $response = $this->actingAs($this->admin)
        ->postJson("/admin/{$this->tenant->slug}/reservations/{$reservation->id}/confirm");

    $response->assertStatus(200)->assertJson(['success' => true]);
    $this->assertDatabaseHas('reservations', [
        'id' => $reservation->id,
        'status' => 'CONFIRMED',
    ]);
});

test('l\'admin peut annuler une réservation', function () {
    $reservation = Reservation::create([
        'tenant_id' => $this->tenant->id,
        'table_id' => $this->table->id,
        'customer_name' => 'Marie Martin',
        'customer_email' => 'marie@example.com',
        'customer_phone' => '+2250701234568',
        'reservation_date' => now()->addDays(5)->toDateString(),
        'reservation_time' => '20:00',
        'party_size' => 2,
        'status' => 'CONFIRMED',
        'confirmation_code' => 'TEST002',
    ]);

    $response = $this->actingAs($this->admin)
        ->postJson("/admin/{$this->tenant->slug}/reservations/{$reservation->id}/cancel");

    $response->assertStatus(200)->assertJson(['success' => true]);
    $this->assertDatabaseHas('reservations', [
        'id' => $reservation->id,
        'status' => 'CANCELLED',
    ]);
});
