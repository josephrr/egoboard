<?php

use App\Http\Controllers\NoteController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RoomController::class, 'index'])->name('rooms.index');
Route::post('/salas', [RoomController::class, 'store'])->name('rooms.store');
Route::get('/salas/{room:slug}', [RoomController::class, 'show'])->name('rooms.show');
Route::post('/salas/{room:slug}/notas', [NoteController::class, 'store'])->name('rooms.notes.store');
