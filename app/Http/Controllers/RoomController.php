<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class RoomController extends Controller
{
    public function index(): View
    {
        $rooms = Room::query()
            ->withCount('notes')
            ->latest()
            ->take(6)
            ->get();

        return view('rooms.index', compact('rooms'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:240'],
        ]);

        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug !== '' ? $baseSlug : 'sala';
        $candidate = $slug;

        while (Room::where('slug', $candidate)->exists()) {
            $candidate = $slug.'-'.Str::lower(Str::random(4));
        }

        $room = Room::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?: null,
            'slug' => $candidate,
        ]);

        return redirect()
            ->route('rooms.show', $room)
            ->with('status', 'Sala creada. Ya puedes compartir este enlace con tus estudiantes.');
    }

    public function show(Room $room): View
    {
        $room->load([
            'notes' => fn ($query) => $query->latest(),
        ]);

        return view('rooms.show', compact('room'));
    }
}
