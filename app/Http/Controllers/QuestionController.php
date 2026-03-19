<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuestionController extends Controller
{
    public function store(Request $request, Room $room): RedirectResponse
    {
        abort_unless($room->isQuestionRoom(), 404);

        $validated = $request->validate([
            'prompt' => ['required', 'string', 'max:240'],
            'question_type' => ['required', Rule::in(array_keys(Question::TYPES))],
            'options_text' => ['nullable', 'string', 'max:1000'],
        ]);

        $options = $this->normalizeOptions($validated['question_type'], $validated['options_text'] ?? '');

        if ($validated['question_type'] === 'single_choice' && count($options) < 2) {
            return back()->withErrors([
                'options_text' => 'Ingresa al menos dos opciones para una pregunta de marcar con X.',
            ])->withInput();
        }

        $room->questions()->create([
            'prompt' => $validated['prompt'],
            'question_type' => $validated['question_type'],
            'options' => $options,
            'position' => (($room->questions()->max('position') ?? 0) + 1),
            'is_active' => true,
        ]);

        return back()->with('status', 'Pregunta creada correctamente.');
    }

    public function destroy(Room $room, Question $question): RedirectResponse
    {
        abort_unless($room->isQuestionRoom() && $question->room_id === $room->id, 404);

        $question->delete();

        return back()->with('status', 'Pregunta eliminada.');
    }

    private function normalizeOptions(string $type, string $optionsText): array
    {
        if ($type === 'true_false') {
            return ['Verdadero', 'Falso'];
        }

        if ($type !== 'single_choice') {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', $optionsText))
            ->map(fn ($option) => trim((string) $option))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
