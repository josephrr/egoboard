<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\Room;
use App\Services\RoomBoardService;
use App\Services\RoomExportService;
use App\Services\RoomService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RoomController extends Controller
{
    public function __construct(
        private readonly RoomService $roomService,
        private readonly RoomBoardService $roomBoardService,
        private readonly RoomExportService $roomExportService
    ) {
    }

    public function index(): View
    {
        $rooms = Room::query()
            ->withCount('notes')
            ->withCount('questions')
            ->withCount('canvasDrawings')
            ->latest()
            ->take(6)
            ->get();

        return view('rooms.index', compact('rooms'));
    }

    public function teacherIndex(): View
    {
        $rooms = Room::query()
            ->withCount('notes')
            ->withCount('questions')
            ->withCount('canvasDrawings')
            ->latest()
            ->get();

        return view('rooms.teacher-index', compact('rooms'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:240'],
            'room_type' => ['required', 'string', 'in:'.implode(',', array_keys(Room::TYPES))],
            'theme' => ['required', 'string', 'in:'.implode(',', array_keys(Room::THEMES))],
            'background_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:3072'],
        ]);

        if ($validated['room_type'] === 'canvas' && $request->hasFile('background_image')) {
            $validated['background_image_path'] = $request->file('background_image')->store('canvas-backgrounds', 'public');
        }

        $room = $this->roomService->create($validated);

        return redirect()
            ->route('rooms.teacher', $room->admin_token)
            ->with('status', 'Sala creada. Comparte el enlace publico y guarda tu enlace privado de docente.');
    }

    public function show(Request $request, Room $room): View
    {
        return view('rooms.show', $this->roomBoardService->publicPayload($request, $room));
    }

    public function board(Request $request, Room $room): JsonResponse
    {
        $payload = $this->roomBoardService->publicPayload($request, $room);

        $partial = match (true) {
            $room->isQuestionRoom() => 'rooms.partials.question-board',
            $room->isCanvasRoom() => 'rooms.partials.canvas-board',
            default => 'rooms.partials.board',
        };

        return response()->json([
            'signature' => $this->roomBoardService->boardSignature($room),
            'html' => view($partial, $payload)->render(),
        ]);
    }

    public function teacher(Room $room): View
    {
        return view('rooms.teacher', $this->roomService->teacherPayload($room));
    }

    public function qr(Room $room)
    {
        return response($this->roomExportService->qrSvg($room), 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function state(Room $room): JsonResponse
    {
        return response()->json([
            'signature' => $this->roomBoardService->boardSignature($room),
        ]);
    }

    public function updateSettings(Request $request, Room $room): RedirectResponse
    {
        $validated = $request->validate([
            'theme' => ['required', 'string', 'in:'.implode(',', array_keys(Room::THEMES))],
            'allow_anonymous' => ['nullable', 'boolean'],
            'allow_reactions' => ['nullable', 'boolean'],
            'allow_one_note_per_participant' => ['nullable', 'boolean'],
            'is_open' => ['nullable', 'boolean'],
            'closes_at' => ['nullable', 'date'],
        ]);

        $this->roomService->updateSettings($room, $validated, $request);

        return back()->with('status', 'Configuracion actualizada.');
    }

    public function clear(Room $room): RedirectResponse
    {
        $this->roomService->clear($room);

        return back()->with('status', 'El tablero fue limpiado.');
    }

    public function exportCsv(Room $room): StreamedResponse
    {
        $filename = 'egoboard-'.$room->slug.'.csv';

        return response()->streamDownload(function () use ($room) {
            $handle = fopen('php://output', 'w');

            if ($room->isQuestionRoom()) {
                fputcsv($handle, ['Pregunta', 'Tipo', 'Nombre', 'Respuesta', 'Fecha']);

                $this->roomExportService->exportableQuestionAnswers($room)
                    ->each(function (Question $question) use ($handle) {
                        $question->answers->each(function (QuestionAnswer $answer) use ($handle, $question) {
                            fputcsv($handle, [
                                $question->prompt,
                                Question::TYPES[$question->question_type] ?? $question->question_type,
                                $answer->author_name,
                                $answer->displayAnswer(),
                                $answer->created_at?->toDateTimeString(),
                            ]);
                        });
                    });
            } else {
                fputcsv($handle, ['Nombre', 'Categoria', 'Mensaje', 'Visible', 'Anonimo', 'Votos', 'Fecha']);

                $this->roomExportService->exportableNotes($room)
                    ->each(function (Note $note) use ($handle) {
                        fputcsv($handle, [
                            $note->author_name,
                            Note::CATEGORIES[$note->category] ?? $note->category,
                            $note->message,
                            $note->is_visible ? 'si' : 'no',
                            $note->is_anonymous ? 'si' : 'no',
                            $note->votes_count,
                            $note->created_at?->toDateTimeString(),
                        ]);
                    });
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportPrint(Room $room): View
    {
        return view('rooms.print', [
            'room' => $room,
            'notes' => $room->isNoteRoom() ? $this->roomExportService->exportableNotes($room) : collect(),
            'questions' => $room->isQuestionRoom() ? $this->roomExportService->exportableQuestionAnswers($room) : collect(),
            'theme' => $room->themeConfig(),
        ]);
    }
}
