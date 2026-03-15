<?php

use App\Models\Room;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('home page loads', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Crea un muro compartible');
});

test('room can be created', function () {
    $response = $this->post('/salas', [
        'name' => 'Muro de clase',
        'description' => 'Notas para detectar problemas reales',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('rooms', [
        'name' => 'Muro de clase',
    ]);
});

test('note can be added to a room', function () {
    $room = Room::create([
        'name' => 'Sala publica',
        'slug' => 'sala-publica',
        'description' => 'Sala de ejemplo',
    ]);

    $response = $this->post(route('rooms.notes.store', $room), [
        'author_name' => 'Ana',
        'message' => 'Es dificil encontrar transporte despues de clases.',
    ]);

    $response->assertRedirect(route('rooms.show', $room));

    $this->assertDatabaseHas('notes', [
        'room_id' => $room->id,
        'author_name' => 'Ana',
        'message' => 'Es dificil encontrar transporte despues de clases.',
    ]);
});