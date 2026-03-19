<?php

use App\Models\Room;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('home page loads', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Crea salas compartibles');
});

test('room can be created', function () {
    $response = $this->post('/salas', [
        'name' => 'Muro de clase',
        'description' => 'Notas para detectar problemas reales',
        'room_type' => 'notes',
        'theme' => 'sunrise',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('rooms', [
        'name' => 'Muro de clase',
        'room_type' => 'notes',
        'theme' => 'sunrise',
    ]);
});

test('note can be added to a room', function () {
    $room = Room::create([
        'name' => 'Sala publica',
        'slug' => 'sala-publica',
        'description' => 'Sala de ejemplo',
        'admin_token' => 'teacher-secret-token',
        'room_type' => 'notes',
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
        'room_type' => 'notes',
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
        'room_type' => 'notes',
        'theme' => 'sunrise',
        'is_open' => true,
        'allow_anonymous' => true,
        'allow_reactions' => true,
        'allow_one_note_per_participant' => false,
    ]);

    $response = $this->getJson(route('rooms.state', $room));

    $response->assertOk();
    $response->assertJsonStructure(['signature']);
});

test('teacher print export view loads', function () {
    $room = Room::create([
        'name' => 'Sala exportable',
        'slug' => 'sala-exportable',
        'description' => 'Sala de ejemplo',
        'admin_token' => 'print-room-token',
        'room_type' => 'notes',
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

test('room board endpoint returns partial html for polling', function () {
    $room = Room::create([
        'name' => 'Sala tablero',
        'slug' => 'sala-tablero',
        'description' => 'Sala de ejemplo',
        'admin_token' => 'board-room-token',
        'room_type' => 'notes',
        'theme' => 'sunrise',
        'is_open' => true,
        'allow_anonymous' => true,
        'allow_reactions' => true,
        'allow_one_note_per_participant' => false,
    ]);

    $room->notes()->create([
        'author_name' => 'Marta',
        'message' => 'Necesitamos mas espacios de estudio.',
        'color' => 'note-yellow',
        'participant_key' => 'participant-board',
        'category' => 'oportunidad',
        'is_anonymous' => false,
        'is_visible' => true,
    ]);

    $response = $this->getJson(route('rooms.board', $room));

    $response->assertOk();
    $response->assertJsonStructure(['signature', 'html']);
    $response->assertJsonFragment([
        'signature' => $response->json('signature'),
    ]);
});

test('teacher qr endpoint returns svg locally', function () {
    $room = Room::create([
        'name' => 'Sala qr',
        'slug' => 'sala-qr',
        'description' => 'Sala de ejemplo',
        'admin_token' => 'qr-room-token',
        'room_type' => 'notes',
        'theme' => 'sunrise',
        'is_open' => true,
        'allow_anonymous' => true,
        'allow_reactions' => true,
        'allow_one_note_per_participant' => false,
    ]);

    $response = $this->get(route('rooms.qr', $room->admin_token));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'image/svg+xml');
    $response->assertSee('<svg', false);
});

test('note reaction can be toggled with json request', function () {
    $room = Room::create([
        'name' => 'Sala reacciones',
        'slug' => 'sala-reacciones',
        'description' => 'Sala de ejemplo',
        'admin_token' => 'reaction-room-token',
        'room_type' => 'notes',
        'theme' => 'sunrise',
        'is_open' => true,
        'allow_anonymous' => true,
        'allow_reactions' => true,
        'allow_one_note_per_participant' => false,
    ]);

    $note = $room->notes()->create([
        'author_name' => 'Eva',
        'message' => 'Hace falta mejor comunicacion entre cursos.',
        'color' => 'note-blue',
        'participant_key' => 'participant-react',
        'category' => 'problema',
        'is_anonymous' => false,
        'is_visible' => true,
    ]);

    $response = $this->postJson(route('rooms.notes.react', [$room, $note]), [
        'participant_key' => 'participant-voter',
        'reaction' => 'me_pasa',
    ]);

    $response->assertOk();
    $response->assertJson(['ok' => true, 'active' => true]);

    $this->assertDatabaseHas('note_votes', [
        'note_id' => $note->id,
        'participant_key' => 'participant-voter',
        'reaction' => 'me_pasa',
    ]);
});

test('anonymous note can be added when room allows it', function () {
    $room = Room::create([
        'name' => 'Sala anonima',
        'slug' => 'sala-anonima',
        'description' => 'Sala de ejemplo',
        'admin_token' => 'anon-room-token',
        'room_type' => 'notes',
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
        'room_type' => 'notes',
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

test('question room can be created from home form', function () {
    $response = $this->post('/salas', [
        'name' => 'Control de lectura',
        'description' => 'Preguntas para el curso',
        'room_type' => 'questions',
        'theme' => 'ocean',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('rooms', [
        'name' => 'Control de lectura',
        'room_type' => 'questions',
        'theme' => 'ocean',
    ]);
});

test('teacher can create a question in a question room', function () {
    $room = Room::create([
        'name' => 'Cuestionario',
        'slug' => 'cuestionario',
        'description' => 'Sala de preguntas',
        'admin_token' => 'question-room-token',
        'room_type' => 'questions',
        'theme' => 'forest',
        'is_open' => true,
        'allow_anonymous' => false,
        'allow_reactions' => false,
        'allow_one_note_per_participant' => false,
    ]);

    $response = $this->post(route('rooms.questions.store', $room->admin_token), [
        'prompt' => 'Que tema te resulto mas complejo?',
        'question_type' => 'single_choice',
        'options_text' => "Funciones\nVectores\nProbabilidad",
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('questions', [
        'room_id' => $room->id,
        'prompt' => 'Que tema te resulto mas complejo?',
        'question_type' => 'single_choice',
    ]);
});

test('participant can answer a question room question', function () {
    $room = Room::create([
        'name' => 'Encuesta rapida',
        'slug' => 'encuesta-rapida',
        'description' => 'Sala de preguntas',
        'admin_token' => 'question-answer-room-token',
        'room_type' => 'questions',
        'theme' => 'sunrise',
        'is_open' => true,
        'allow_anonymous' => false,
        'allow_reactions' => false,
        'allow_one_note_per_participant' => false,
    ]);

    $question = $room->questions()->create([
        'prompt' => 'La clase fue clara?',
        'question_type' => 'true_false',
        'options' => ['Verdadero', 'Falso'],
        'position' => 1,
        'is_active' => true,
    ]);

    $response = $this->postJson(route('rooms.questions.answers.store', [$room, $question]), [
        'author_name' => 'Camila Rojas',
        'participant_key' => 'participant-question-1',
        'selected_option' => 'Verdadero',
    ]);

    $response->assertOk();
    $response->assertJson(['ok' => true]);

    $this->assertDatabaseHas('question_answers', [
        'room_id' => $room->id,
        'question_id' => $question->id,
        'author_name' => 'Camila Rojas',
        'participant_key' => 'participant-question-1',
        'selected_option' => 'Verdadero',
    ]);
});

test('question public board shows all answers and marks current participant answer', function () {
    $room = Room::create([
        'name' => 'Debate publico',
        'slug' => 'debate-publico',
        'description' => 'Sala de preguntas',
        'admin_token' => 'public-question-room-token',
        'room_type' => 'questions',
        'theme' => 'sunrise',
        'is_open' => true,
        'allow_anonymous' => false,
        'allow_reactions' => false,
        'allow_one_note_per_participant' => false,
    ]);

    $question = $room->questions()->create([
        'prompt' => 'Que conclusion sacaste de la clase?',
        'question_type' => 'open',
        'options' => [],
        'position' => 1,
        'is_active' => true,
    ]);

    $question->answers()->create([
        'room_id' => $room->id,
        'author_name' => 'Ana Soto',
        'participant_key' => 'participant-a',
        'answer_text' => 'Aprendi a comparar fuentes.',
    ]);

    $question->answers()->create([
        'room_id' => $room->id,
        'author_name' => 'Bruno Diaz',
        'participant_key' => 'participant-b',
        'answer_text' => 'Entendi mejor el tema central.',
    ]);

    $response = $this->get(route('rooms.show', $room).'?participant_key=participant-a');

    $response->assertOk();
    $response->assertSee('Ana Soto');
    $response->assertSee('Bruno Diaz');
    $response->assertSee('Tu respuesta');
    $response->assertSee('Actualizar respuesta');
    $response->assertSee('Aprendi a comparar fuentes.');
});
