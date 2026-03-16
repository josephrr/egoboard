<?php

namespace App\Services;

use App\Models\Note;
use App\Models\Room;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RoomBoardService
{
    public function publicPayload(Request $request, Room $room): array
    {
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
}
