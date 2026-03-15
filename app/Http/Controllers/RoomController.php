<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Room;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            'theme' => ['required', 'string', 'in:'.implode(',', array_keys(Room::THEMES))],
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
            'admin_token' => Str::lower(Str::random(32)),
            'theme' => $validated['theme'],
            'is_open' => true,
            'allow_anonymous' => true,
            'allow_reactions' => true,
            'allow_one_note_per_participant' => false,
        ]);

        return redirect()
            ->route('rooms.teacher', $room->admin_token)
            ->with('status', 'Sala creada. Comparte el enlace publico y guarda tu enlace privado de docente.');
    }

    public function show(Request $request, Room $room): View
    {
        $filters = [
            'q' => trim((string) $request->string('q')),
            'category' => (string) $request->string('category'),
            'sort' => (string) $request->string('sort', 'recent'),
        ];

        $notesQuery = $room->visibleNotes()
            ->withCount('votes')
            ->withCount([
                'votes as me_pasa_count' => fn (Builder $query) => $query->where('reaction', 'me_pasa'),
                'votes as importante_count' => fn (Builder $query) => $query->where('reaction', 'importante'),
                'votes as quiero_resolverlo_count' => fn (Builder $query) => $query->where('reaction', 'quiero_resolverlo'),
            ]);

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

        $notes = $notesQuery->get();

        return view('rooms.show', [
            'room' => $room,
            'notes' => $notes,
            'filters' => $filters,
            'theme' => $room->themeConfig(),
        ]);
    }

    public function teacher(Room $room): View
    {
        $room->load([
            'notes' => fn ($query) => $query->withCount('votes')->latest(),
        ]);

        return view('rooms.teacher', [
            'room' => $room,
            'theme' => $room->themeConfig(),
        ]);
    }

    public function state(Room $room): JsonResponse
    {
        $lastNote = $room->notes()->latest('updated_at')->first();
        $lastVote = $room->notes()
            ->join('note_votes', 'notes.id', '=', 'note_votes.note_id')
            ->latest('note_votes.updated_at')
            ->value('note_votes.updated_at');

        return response()->json([
            'room_updated_at' => optional($room->updated_at)->toIso8601String(),
            'note_count' => $room->visibleNotes()->count(),
            'last_note_at' => optional($lastNote?->updated_at)->toIso8601String(),
            'last_vote_at' => $lastVote ? Carbon::parse($lastVote)->toIso8601String() : null,
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

        $room->update([
            'theme' => $validated['theme'],
            'allow_anonymous' => $request->boolean('allow_anonymous'),
            'allow_reactions' => $request->boolean('allow_reactions'),
            'allow_one_note_per_participant' => $request->boolean('allow_one_note_per_participant'),
            'is_open' => $request->boolean('is_open'),
            'closes_at' => filled($validated['closes_at'] ?? null) ? Carbon::parse($validated['closes_at']) : null,
        ]);

        return back()->with('status', 'Configuracion actualizada.');
    }

    public function clear(Room $room): RedirectResponse
    {
        $room->notes()->delete();

        return back()->with('status', 'El tablero fue limpiado.');
    }

    public function exportCsv(Room $room): StreamedResponse
    {
        $filename = 'egoboard-'.$room->slug.'.csv';

        return response()->streamDownload(function () use ($room) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Nombre', 'Categoria', 'Mensaje', 'Visible', 'Anonimo', 'Votos', 'Fecha']);

            $room->notes()
                ->withCount('votes')
                ->latest()
                ->get()
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

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportPrint(Room $room): View
    {
        $notes = $room->notes()
            ->withCount('votes')
            ->latest()
            ->get();

        return view('rooms.print', [
            'room' => $room,
            'notes' => $notes,
            'theme' => $room->themeConfig(),
        ]);
    }
}
