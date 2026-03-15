<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class NoteController extends Controller
{
    private const COLORS = [
        'note-yellow',
        'note-blue',
        'note-green',
        'note-rose',
        'note-orange',
    ];

    public function store(Request $request, Room $room): RedirectResponse
    {
        $validated = $request->validate([
            'author_name' => ['required', 'string', 'max:80'],
            'message' => ['required', 'string', 'max:500'],
        ]);

        $room->notes()->create([
            'author_name' => $validated['author_name'],
            'message' => $validated['message'],
            'color' => self::COLORS[array_rand(self::COLORS)],
        ]);

        return redirect()
            ->route('rooms.show', $room)
            ->with('status', 'Nota publicada correctamente.');
    }
}
