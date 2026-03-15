<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\NoteVote;
use App\Models\Room;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

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
        if ($room->isClosed()) {
            return back()->with('status', 'Esta sala esta cerrada y ya no recibe nuevas notas.');
        }

        $validated = $request->validate([
            'author_name' => [$room->allow_anonymous ? 'nullable' : 'required', 'string', 'max:80'],
            'message' => ['required', 'string', 'max:500'],
            'category' => ['required', Rule::in(array_keys(Note::CATEGORIES))],
            'participant_key' => ['required', 'string', 'max:100'],
            'is_anonymous' => ['nullable', 'boolean'],
        ]);

        $isAnonymous = $room->allow_anonymous && $request->boolean('is_anonymous');

        if (! $isAnonymous && blank($validated['author_name'])) {
            return back()->withErrors([
                'author_name' => 'Escribe tu nombre o activa el modo anonimo.',
            ])->withInput();
        }

        if (
            $room->allow_one_note_per_participant &&
            $room->notes()->where('participant_key', $validated['participant_key'])->exists()
        ) {
            return back()->withErrors([
                'message' => 'Esta sala permite una sola nota por participante.',
            ])->withInput();
        }

        $room->notes()->create([
            'author_name' => $isAnonymous ? 'Anonimo' : $validated['author_name'],
            'message' => $validated['message'],
            'color' => self::COLORS[array_rand(self::COLORS)],
            'participant_key' => $validated['participant_key'],
            'category' => $validated['category'],
            'is_anonymous' => $isAnonymous,
            'is_visible' => true,
        ]);

        return redirect()
            ->route('rooms.show', $room)
            ->with('status', 'Nota publicada correctamente.');
    }

    public function react(Request $request, Room $room, Note $note): RedirectResponse
    {
        abort_unless($note->room_id === $room->id, 404);

        if (! $room->allow_reactions || ! $note->is_visible) {
            return back();
        }

        $validated = $request->validate([
            'participant_key' => ['required', 'string', 'max:100'],
            'reaction' => ['required', Rule::in(array_keys(Note::REACTIONS))],
        ]);

        $vote = NoteVote::query()->where([
            'note_id' => $note->id,
            'participant_key' => $validated['participant_key'],
            'reaction' => $validated['reaction'],
        ])->first();

        if ($vote) {
            $vote->delete();
        } else {
            NoteVote::create([
                'note_id' => $note->id,
                'participant_key' => $validated['participant_key'],
                'reaction' => $validated['reaction'],
            ]);
        }

        return back();
    }

    public function toggleVisibility(Room $room, Note $note): RedirectResponse
    {
        abort_unless($note->room_id === $room->id, 404);

        $note->update([
            'is_visible' => ! $note->is_visible,
        ]);

        return back()->with('status', 'Visibilidad actualizada.');
    }

    public function destroy(Room $room, Note $note): RedirectResponse
    {
        abort_unless($note->room_id === $room->id, 404);

        $note->delete();

        return back()->with('status', 'Nota eliminada.');
    }
}
