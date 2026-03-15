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
        'theme' => 'sunrise',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('rooms', [
        'name' => 'Muro de clase',
        'theme' => 'sunrise',
    ]);
});

test('note can be added to a room', function () {
    $room = Room::create([
        'name' => 'Sala publica',
        'slug' => 'sala-publica',
        'description' => 'Sala de ejemplo',
        'admin_token' => 'teacher-secret-token',
        'theme' => 'sunrise',
        'is_open' => true,
        'allow_anonymous' => true,
        'allow_reactions' => true,
        'allow_one_note_per_participant' => false,
    ]);

    $response = $this->post(route('rooms.notes.store', $room), [
        'author_name' => 'Ana',
        'message' => 'Es dificil encontrar transporte despues de clases.',
        'category' => 'problema',
        'participant_key' => 'participant-1',
    ]);

    $response->assertRedirect(route('rooms.show', $room));

    $this->assertDatabaseHas('notes', [
        'room_id' => $room->id,
        'author_name' => 'Ana',
        'message' => 'Es dificil encontrar transporte despues de clases.',
        'category' => 'problema',
        'participant_key' => 'participant-1',
    ]);
});

test('teacher private view loads with admin token', function () {
    $room = Room::create([
        'name' => 'Sala privada',
        'slug' => 'sala-privada',
        'description' => 'Sala de ejemplo',
        'admin_token' => 'private-room-token',
        'theme' => 'ocean',
        'is_open' => true,
        'allow_anonymous' => true,
        'allow_reactions' => true,
        'allow_one_note_per_participant' => false,
    ]);

    $response = $this->get(route('rooms.teacher', $room->admin_token));

    $response->assertOk();
    $response->assertSee('Panel privado');
});

test('room state endpoint returns json for polling', function () {
    $room = Room::create([
        'name' => 'Sala estado',
        'slug' => 'sala-estado',
        'description' => 'Sala de ejemplo',
        'admin_token' => 'state-room-token',
        'theme' => 'sunrise',
        'is_open' => true,
        'allow_anonymous' => true,
        'allow_reactions' => true,
        'allow_one_note_per_participant' => false,
    ]);

    $response = $this->getJson(route('rooms.state', $room));

    $response->assertOk();
    $response->assertJsonStructure([
        'room_updated_at',
        'note_count',
        'last_note_at',
        'last_vote_at',
    ]);
});

test('teacher print export view loads', function () {
    $room = Room::create([
        'name' => 'Sala exportable',
        'slug' => 'sala-exportable',
        'description' => 'Sala de ejemplo',
        'admin_token' => 'print-room-token',
        'theme' => 'sunrise',
        'is_open' => true,
        'allow_anonymous' => true,
        'allow_reactions' => true,
        'allow_one_note_per_participant' => false,
    ]);

    $response = $this->get(route('rooms.export.print', $room->admin_token));

    $response->assertOk();
    $response->assertSee('Guardar o imprimir como PDF');
});

test('anonymous note can be added when room allows it', function () {
    $room = Room::create([
        'name' => 'Sala anonima',
        'slug' => 'sala-anonima',
        'description' => 'Sala de ejemplo',
        'admin_token' => 'anon-room-token',
        'theme' => 'forest',
        'is_open' => true,
        'allow_anonymous' => true,
        'allow_reactions' => true,
        'allow_one_note_per_participant' => false,
    ]);

    $response = $this->post(route('rooms.notes.store', $room), [
        'author_name' => '',
        'message' => 'No quiero poner mi nombre.',
        'category' => 'queja',
        'participant_key' => 'participant-2',
        'is_anonymous' => '1',
    ]);

    $response->assertRedirect(route('rooms.show', $room));

    $this->assertDatabaseHas('notes', [
        'room_id' => $room->id,
        'author_name' => 'Anonimo',
        'is_anonymous' => true,
    ]);
});

test('room can limit one note per participant', function () {
    $room = Room::create([
        'name' => 'Sala limitada',
        'slug' => 'sala-limitada',
        'description' => 'Sala de ejemplo',
        'admin_token' => 'limit-room-token',
        'theme' => 'sunrise',
        'is_open' => true,
        'allow_anonymous' => true,
        'allow_reactions' => true,
        'allow_one_note_per_participant' => true,
    ]);

    $room->notes()->create([
        'author_name' => 'Luis',
        'message' => 'Primera nota',
        'color' => 'note-yellow',
        'participant_key' => 'participant-3',
        'category' => 'idea',
        'is_anonymous' => false,
        'is_visible' => true,
    ]);

    $response = $this->from(route('rooms.show', $room))->post(route('rooms.notes.store', $room), [
        'author_name' => 'Luis',
        'message' => 'Segunda nota',
        'category' => 'idea',
        'participant_key' => 'participant-3',
    ]);

    $response->assertRedirect(route('rooms.show', $room));
    $response->assertSessionHasErrors('message');
});
