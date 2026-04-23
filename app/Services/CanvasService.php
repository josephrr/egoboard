<?php

namespace App\Services;

use App\Models\CanvasDrawing;
use App\Models\Room;
use Illuminate\Support\Collection;

class CanvasService
{
    public function save(Room $room, array $data): CanvasDrawing
    {
        return CanvasDrawing::updateOrCreate(
            [
                'room_id' => $room->id,
                'participant_key' => $data['participant_key'],
            ],
            [
                'author_name' => trim($data['author_name']),
                'canvas_data' => $data['canvas_data'],
                'preview_png' => $data['preview_png'] ?? null,
            ]
        );
    }

    public function teacherUpdate(CanvasDrawing $drawing, array $data): CanvasDrawing
    {
        $drawing->update([
            'canvas_data' => $data['canvas_data'],
            'preview_png' => $data['preview_png'] ?? $drawing->preview_png,
        ]);

        return $drawing;
    }

    public function getForParticipant(Room $room, string $participantKey): ?CanvasDrawing
    {
        if ($participantKey === '') {
            return null;
        }

        return CanvasDrawing::where('room_id', $room->id)
            ->where('participant_key', $participantKey)
            ->first();
    }

    public function allForRoom(Room $room): Collection
    {
        return CanvasDrawing::where('room_id', $room->id)
            ->select(['id', 'room_id', 'author_name', 'participant_key', 'preview_png', 'updated_at'])
            ->latest('updated_at')
            ->get();
    }

    public function clearAll(Room $room): void
    {
        $room->canvasDrawings()->delete();
    }
}
