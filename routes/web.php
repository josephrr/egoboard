<?php

use App\Http\Controllers\NoteController;
use App\Http\Controllers\QuestionAnswerController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RoomController::class, 'index'])->name('rooms.index');
Route::post('/salas', [RoomController::class, 'store'])->name('rooms.store');
Route::get('/salas/{room:slug}', [RoomController::class, 'show'])->name('rooms.show');
Route::get('/salas/{room:slug}/tablero', [RoomController::class, 'board'])->name('rooms.board');
Route::post('/salas/{room:slug}/notas', [NoteController::class, 'store'])->name('rooms.notes.store');
Route::post('/salas/{room:slug}/preguntas/{question}/respuestas', [QuestionAnswerController::class, 'store'])
    ->scopeBindings()
    ->name('rooms.questions.answers.store');
Route::post('/salas/{room:slug}/notas/{note}/reacciones', [NoteController::class, 'react'])
    ->scopeBindings()
    ->name('rooms.notes.react');
Route::get('/salas/{room:slug}/estado', [RoomController::class, 'state'])->name('rooms.state');
Route::get('/docente/{room:admin_token}', [RoomController::class, 'teacher'])->name('rooms.teacher');
Route::get('/docente/{room:admin_token}/qr.svg', [RoomController::class, 'qr'])
    ->name('rooms.qr');
Route::patch('/docente/{room:admin_token}/configuracion', [RoomController::class, 'updateSettings'])->name('rooms.settings.update');
Route::delete('/docente/{room:admin_token}/notas', [RoomController::class, 'clear'])->name('rooms.clear');
Route::post('/docente/{room:admin_token}/preguntas', [QuestionController::class, 'store'])->name('rooms.questions.store');
Route::delete('/docente/{room:admin_token}/preguntas/{question}', [QuestionController::class, 'destroy'])
    ->scopeBindings()
    ->name('rooms.questions.destroy');
Route::get('/docente/{room:admin_token}/exportar/csv', [RoomController::class, 'exportCsv'])->name('rooms.export.csv');
Route::get('/docente/{room:admin_token}/exportar/pdf', [RoomController::class, 'exportPrint'])->name('rooms.export.print');
Route::patch('/docente/{room:admin_token}/notas/{note}', [NoteController::class, 'toggleVisibility'])
    ->scopeBindings()
    ->name('rooms.notes.visibility');
Route::delete('/docente/{room:admin_token}/notas/{note}', [NoteController::class, 'destroy'])
    ->scopeBindings()
    ->name('rooms.notes.destroy');
