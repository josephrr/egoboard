<?php

namespace App\Services;

use App\Models\Note;
use App\Models\NoteVote;
use App\Models\Room;

class NoteService
{
    private const COLORS = [
        'note-yellow',
        'note-blue',
        'note-green',
        'note-rose',
        'note-orange',
    ];

    public function create(Room $room, array $validated, bool $isAnonymous): Note
    {
        return $room->notes()->create([
            'author_name' => $isAnonymous ? 'Anonimo' : $validated['author_name'],
            'message' => $validated['message'],
            'color' => self::COLORS[array_rand(self::COLORS)],
            'participant_key' => $validated['participant_key'],
            'category' => $validated['category'],
            'is_anonymous' => $isAnonymous,
            'is_visible' => true,
        ]);
    }

    public function participantCanSubmit(Room $room, string $participantKey): bool
    {
        if (! $room->allow_one_note_per_participant) {
            return true;
        }

        return ! $room->notes()->where('participant_key', $participantKey)->exists();
    }

    public function toggleReaction(Note $note, string $participantKey, string $reaction): bool
    {
        $vote = NoteVote::query()->where([
            'note_id' => $note->id,
            'participant_key' => $participantKey,
            'reaction' => $reaction,
        ])->first();

        if ($vote) {
            $vote->delete();

            return false;
        }

        NoteVote::create([
            'note_id' => $note->id,
            'participant_key' => $participantKey,
            'reaction' => $reaction,
        ]);

        return true;
    }
}
