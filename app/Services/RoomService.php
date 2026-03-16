<?php

namespace App\Services;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class RoomService
{
    public function create(array $validated): Room
    {
        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug !== '' ? $baseSlug : 'sala';
        $candidate = $slug;

        while (Room::where('slug', $candidate)->exists()) {
            $candidate = $slug.'-'.Str::lower(Str::random(4));
        }

        return Room::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?: null,
            'slug' => $candidate,
            'admin_token' => Str::lower(Str::random(32)),
            'theme' => $validated['theme'],
            'is_open' => true,
            'allow_anonymous' => true,
            'allow_reactions' => true,
            'allow_one_note_per_participant' => false,
        ]);
    }

    public function teacherPayload(Room $room): array
    {
        $room->load([
            'notes' => fn ($query) => $query->withCount('votes')->latest(),
        ]);

        return [
            'room' => $room,
            'theme' => $room->themeConfig(),
        ];
    }

    public function updateSettings(Room $room, array $validated, Request $request): void
    {
        $room->update([
            'theme' => $validated['theme'],
            'allow_anonymous' => $request->boolean('allow_anonymous'),
            'allow_reactions' => $request->boolean('allow_reactions'),
            'allow_one_note_per_participant' => $request->boolean('allow_one_note_per_participant'),
            'is_open' => $request->boolean('is_open'),
            'closes_at' => filled($validated['closes_at'] ?? null) ? Carbon::parse($validated['closes_at']) : null,
        ]);
    }

    public function clear(Room $room): void
    {
        $room->notes()->delete();
    }
}
