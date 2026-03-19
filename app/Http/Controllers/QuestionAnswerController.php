<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class QuestionAnswerController extends Controller
{
    public function store(Request $request, Room $room, Question $question): RedirectResponse|JsonResponse
    {
        abort_unless($room->isQuestionRoom() && $question->room_id === $room->id, 404);

        if ($room->isClosed()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Esta sala esta cerrada y ya no recibe respuestas.'], 422)
                : back()->with('status', 'Esta sala esta cerrada y ya no recibe respuestas.');
        }

        $validated = $request->validate([
            'author_name' => ['required', 'string', 'max:80'],
            'participant_key' => ['required', 'string', 'max:100'],
            'answer_text' => ['nullable', 'string', 'max:1000'],
            'selected_option' => ['nullable', 'string', 'max:120'],
        ]);

        if ($question->question_type === 'open' && blank($validated['answer_text'] ?? '')) {
            return $this->validationResponse($request, 'Escribe una respuesta antes de enviar.');
        }

        if ($question->question_type !== 'open' && ! in_array($validated['selected_option'] ?? '', $question->resolvedOptions(), true)) {
            return $this->validationResponse($request, 'Selecciona una opcion valida.');
        }

        $question->answers()->updateOrCreate(
            ['participant_key' => $validated['participant_key']],
            [
                'room_id' => $room->id,
                'author_name' => trim($validated['author_name']),
                'answer_text' => $question->question_type === 'open' ? trim((string) $validated['answer_text']) : null,
                'selected_option' => $question->question_type === 'open' ? null : $validated['selected_option'],
            ]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Respuesta guardada correctamente.',
            ]);
        }

        return back()->with('status', 'Respuesta guardada correctamente.');
    }

    private function validationResponse(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'errors' => ['answer' => [$message]],
            ], 422);
        }

        return back()->withErrors(['answer' => $message])->withInput();
    }
}
