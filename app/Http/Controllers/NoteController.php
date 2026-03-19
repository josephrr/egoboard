<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Room;
use App\Services\NoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class NoteController extends Controller
{
    public function __construct(
        private readonly NoteService $noteService
    ) {
    }

    public function store(Request $request, Room $room): RedirectResponse|JsonResponse
    {
        abort_unless($room->isNoteRoom(), 404);

        if ($room->isClosed()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Esta sala esta cerrada y ya no recibe nuevas notas.'], 422)
                : back()->with('status', 'Esta sala esta cerrada y ya no recibe nuevas notas.');
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
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Escribe tu nombre o activa el modo anonimo.',
                    'errors' => ['author_name' => ['Escribe tu nombre o activa el modo anonimo.']],
                ], 422);
            }

            return back()->withErrors([
                'author_name' => 'Escribe tu nombre o activa el modo anonimo.',
            ])->withInput();
        }

        if (
            ! $this->noteService->participantCanSubmit($room, $validated['participant_key'])
        ) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Esta sala permite una sola nota por participante.',
                    'errors' => ['message' => ['Esta sala permite una sola nota por participante.']],
                ], 422);
            }

            return back()->withErrors([
                'message' => 'Esta sala permite una sola nota por participante.',
            ])->withInput();
        }

        $this->noteService->create($room, $validated, $isAnonymous);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Nota publicada correctamente.',
            ]);
        }

        return redirect()
            ->route('rooms.show', $room)
            ->with('status', 'Nota publicada correctamente.');
    }

    public function react(Request $request, Room $room, Note $note): RedirectResponse|JsonResponse
    {
        abort_unless($room->isNoteRoom(), 404);
        abort_unless($note->room_id === $room->id, 404);

        if (! $room->allow_reactions || ! $note->is_visible) {
            return $request->expectsJson()
                ? response()->json(['ok' => false], 422)
                : back();
        }

        $validated = $request->validate([
            'participant_key' => ['required', 'string', 'max:100'],
            'reaction' => ['required', Rule::in(array_keys(Note::REACTIONS))],
        ]);

        $active = $this->noteService->toggleReaction(
            $note,
            $validated['participant_key'],
            $validated['reaction']
        );

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'active' => $active,
            ]);
        }

        return back();
    }

    public function toggleVisibility(Room $room, Note $note): RedirectResponse
    {
        abort_unless($room->isNoteRoom(), 404);
        abort_unless($note->room_id === $room->id, 404);

        $note->update([
            'is_visible' => ! $note->is_visible,
        ]);

        return back()->with('status', 'Visibilidad actualizada.');
    }

    public function destroy(Room $room, Note $note): RedirectResponse
    {
        abort_unless($room->isNoteRoom(), 404);
        abort_unless($note->room_id === $room->id, 404);

        $note->delete();

        return back()->with('status', 'Nota eliminada.');
    }
}
