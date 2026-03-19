<?php

namespace App\Services;

use App\Models\Room;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Collection;

class RoomExportService
{
    public function exportableNotes(Room $room): Collection
    {
        return $room->notes()
            ->withCount('votes')
            ->latest()
            ->get();
    }

    public function exportableQuestionAnswers(Room $room): Collection
    {
        return $room->questions()
            ->with(['answers' => fn ($query) => $query->latest()])
            ->get();
    }

    public function qrSvg(Room $room): string
    {
        $qrCode = new QrCode(
            data: route('rooms.show', $room),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 220,
            margin: 8,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(15, 23, 42),
            backgroundColor: new Color(255, 255, 255)
        );

        return (new SvgWriter())->write($qrCode)->getString();
    }
}
