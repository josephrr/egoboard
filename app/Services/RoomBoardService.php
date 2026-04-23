<?php

namespace App\Services;

use App\Models\CanvasDrawing;
use App\Models\Note;
use App\Models\Room;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RoomBoardService
{
    public function publicPayload(Request $request, Room $room): array
    {
        if ($room->isQuestionRoom()) {
            return $this->questionPayload($room, $request);
        }

        if ($room->isCanvasRoom()) {
            return $this->canvasPayload($room, $request);
        }

        $filters = [
            'q' => trim((string) $request->string('q')),
            'category' => (string) $request->string('category'),
            'sort' => (string) $request->string('sort', 'recent'),
        ];
        $participantKey = trim((string) $request->string('participant_key'));

        $notesQuery = $room->visibleNotes()
            ->withCount('votes')
            ->withCount([
                'votes as me_pasa_count' => fn (Builder $query) => $query->where('reaction', 'me_pasa'),
                'votes as importante_count' => fn (Builder $query) => $query->where('reaction', 'importante'),
                'votes as quiero_resolverlo_count' => fn (Builder $query) => $query->where('reaction', 'quiero_resolverlo'),
            ]);

        if ($participantKey !== '') {
            $notesQuery->withExists([
                'votes as me_pasa_active' => fn (Builder $query) => $query
                    ->where('reaction', 'me_pasa')
                    ->where('participant_key', $participantKey),
                'votes as importante_active' => fn (Builder $query) => $query
                    ->where('reaction', 'importante')
                    ->where('participant_key', $participantKey),
                'votes as quiero_resolverlo_active' => fn (Builder $query) => $query
                    ->where('reaction', 'quiero_resolverlo')
                    ->where('participant_key', $participantKey),
            ]);
        }

        if ($filters['q'] !== '') {
            $notesQuery->where(function (Builder $query) use ($filters) {
                $query->where('message', 'like', '%'.$filters['q'].'%')
                    ->orWhere('author_name', 'like', '%'.$filters['q'].'%');
            });
        }

        if ($filters['category'] !== '' && array_key_exists($filters['category'], Note::CATEGORIES)) {
            $notesQuery->where('category', $filters['category']);
        }

        if ($filters['sort'] === 'top') {
            $notesQuery->orderByDesc('votes_count')->latest();
        } else {
            $notesQuery->latest();
        }

        return [
            'room' => $room,
            'notes' => $notesQuery->get(),
            'filters' => $filters,
            'theme' => $room->themeConfig(),
            'boardSignature' => $this->boardSignature($room),
            'participantKey' => $participantKey,
        ];
    }

    public function boardSignature(Room $room): string
    {
        if ($room->isCanvasRoom()) {
            $count = $room->canvasDrawings()->count();
            $lastUpdated = $room->canvasDrawings()->latest('updated_at')->value('updated_at');

            return sha1(json_encode([
                optional($room->updated_at)->toIso8601String(),
                $count,
                $lastUpdated ? Carbon::parse($lastUpdated)->toIso8601String() : null,
            ]));
        }

        if ($room->isQuestionRoom()) {
            $lastQuestion = $room->questions()->latest('updated_at')->first();
            $lastAnswer = $room->questions()
                ->join('question_answers', 'questions.id', '=', 'question_answers.question_id')
                ->latest('question_answers.updated_at')
                ->value('question_answers.updated_at');

            return sha1(json_encode([
                optional($room->updated_at)->toIso8601String(),
                $room->questions()->count(),
                optional($lastQuestion?->updated_at)->toIso8601String(),
                $lastAnswer ? Carbon::parse($lastAnswer)->toIso8601String() : null,
            ]));
        }

        $lastNote = $room->notes()->latest('updated_at')->first();
        $lastVote = $room->notes()
            ->join('note_votes', 'notes.id', '=', 'note_votes.note_id')
            ->latest('note_votes.updated_at')
            ->value('note_votes.updated_at');

        return sha1(json_encode([
            optional($room->updated_at)->toIso8601String(),
            $room->visibleNotes()->count(),
            optional($lastNote?->updated_at)->toIso8601String(),
            $lastVote ? Carbon::parse($lastVote)->toIso8601String() : null,
        ]));
    }

    private function canvasPayload(Room $room, Request $request): array
    {
        $participantKey = trim((string) $request->string('participant_key'));

        $myDrawing = $participantKey !== ''
            ? CanvasDrawing::where('room_id', $room->id)
                ->where('participant_key', $participantKey)
                ->first()
            : null;

        return [
            'room' => $room,
            'theme' => $room->themeConfig(),
            'boardSignature' => $this->boardSignature($room),
            'participantKey' => $participantKey,
            'myDrawing' => $myDrawing,
        ];
    }

    private function questionPayload(Room $room, Request $request): array
    {
        $participantKey = trim((string) $request->string('participant_key'));

        $questions = $room->questions()
            ->where('is_active', true)
            ->withCount('answers')
            ->with([
                'answers' => function ($query) {
                    $query->latest();
                },
            ])
            ->get();

        return [
            'room' => $room,
            'questions' => $questions,
            'theme' => $room->themeConfig(),
            'boardSignature' => $this->boardSignature($room),
            'participantKey' => $participantKey,
        ];
    }
}
