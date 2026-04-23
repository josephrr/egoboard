<?php

namespace App\Http\Controllers;

use App\Models\CanvasDrawing;
use App\Models\Room;
use App\Services\CanvasService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CanvasDrawingController extends Controller
{
    public function __construct(private readonly CanvasService $canvasService)
    {
    }

    public function editView(Room $room, CanvasDrawing $drawing): View
    {
        abort_unless($room->isCanvasRoom() && $drawing->room_id === $room->id, 404);

        return view('rooms.canvas-edit', [
            'room' => $room,
            'drawing' => $drawing,
            'theme' => $room->themeConfig(),
        ]);
    }

    public function mine(Request $request, Room $room): JsonResponse
    {
        abort_unless($room->isCanvasRoom(), 404);

        $participantKey = trim((string) $request->string('participant_key'));
        $drawing = $this->canvasService->getForParticipant($room, $participantKey);

        return response()->json([
            'canvas_data' => $drawing?->canvas_data,
            'updated_at' => $drawing?->updated_at?->toIso8601String(),
        ]);
    }

    public function store(Request $request, Room $room): JsonResponse
    {
        abort_unless($room->isCanvasRoom(), 404);

        if ($room->isClosed()) {
            return response()->json(['message' => 'Esta sala esta cerrada y ya no recibe dibujos.'], 422);
        }

        $validated = $request->validate([
            'author_name' => ['required', 'string', 'max:80'],
            'participant_key' => ['required', 'string', 'max:100'],
            'canvas_data' => ['required', 'string', 'max:5000000'],
            'preview_png' => ['nullable', 'string', 'max:800000'],
        ]);

        try {
            json_decode($validated['canvas_data'], true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return response()->json(['message' => 'El dibujo enviado no es valido.'], 422);
        }

        $drawing = $this->canvasService->save($room, $validated);

        return response()->json([
            'ok' => true,
            'drawingId' => $drawing->id,
            'savedAt' => $drawing->updated_at?->toIso8601String(),
            'message' => 'Dibujo guardado correctamente.',
        ]);
    }

    public function show(Room $room, CanvasDrawing $drawing): JsonResponse
    {
        abort_unless($room->isCanvasRoom() && $drawing->room_id === $room->id, 404);

        return response()->json([
            'id' => $drawing->id,
            'author_name' => $drawing->author_name,
            'canvas_data' => $drawing->canvas_data,
            'preview_png' => $drawing->preview_png,
            'updated_at' => $drawing->updated_at?->toIso8601String(),
        ]);
    }

    public function teacherUpdate(Request $request, Room $room, CanvasDrawing $drawing): JsonResponse
    {
        abort_unless($room->isCanvasRoom() && $drawing->room_id === $room->id, 404);

        $validated = $request->validate([
            'canvas_data' => ['required', 'string', 'max:5000000'],
            'preview_png' => ['nullable', 'string', 'max:800000'],
        ]);

        try {
            json_decode($validated['canvas_data'], true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return response()->json(['message' => 'El dibujo enviado no es valido.'], 422);
        }

        $this->canvasService->teacherUpdate($drawing, $validated);

        return response()->json([
            'ok' => true,
            'drawingId' => $drawing->id,
            'savedAt' => $drawing->updated_at?->toIso8601String(),
            'message' => 'Dibujo actualizado.',
        ]);
    }

    public function destroy(Room $room, CanvasDrawing $drawing): JsonResponse
    {
        abort_unless($room->isCanvasRoom() && $drawing->room_id === $room->id, 404);

        $drawing->delete();

        return response()->json(['ok' => true]);
    }
}
