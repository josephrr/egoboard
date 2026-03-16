@props([
    'note',
    'room',
])

<article {{ $attributes->class(['note-card', $note->color]) }}>
    <div class="absolute left-1/2 top-3 h-3.5 w-3.5 -translate-x-1/2 rounded-full bg-red-600/80 shadow-inner"></div>

    <div class="mt-4 flex flex-wrap gap-2">
        <span class="rounded-full bg-white/70 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-600">
            {{ \App\Models\Note::CATEGORIES[$note->category] ?? $note->category }}
        </span>
        @if ($note->votes_count > 0)
            <span class="rounded-full bg-slate-950 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-white">
                {{ $note->votes_count }} votos
            </span>
        @endif
    </div>

    <p class="mt-4 text-lg font-medium leading-7 text-slate-900">
        "{{ $note->message }}"
    </p>

    <div class="mt-8 flex items-center justify-between border-t border-black/10 pt-4">
        <span class="text-xs font-black uppercase tracking-[0.24em] text-slate-700">{{ $note->displayName() }}</span>
        <span class="text-xs text-slate-500">{{ $note->created_at->diffForHumans() }}</span>
    </div>

    @if ($room->allow_reactions)
        <div class="mt-4 space-y-2">
            @foreach (\App\Models\Note::REACTIONS as $reaction => $label)
                @php
                    $countField = $reaction.'_count';
                    $activeField = $reaction.'_active';
                    $isActive = (bool) ($note->{$activeField} ?? false);
                @endphp
                <form method="POST" action="{{ route('rooms.notes.react', [$room, $note]) }}" data-reaction-form>
                    @csrf
                    <input type="hidden" name="participant_key" data-participant-key>
                    <input type="hidden" name="reaction" value="{{ $reaction }}">
                    <button
                        type="submit"
                        class="flex w-full items-center justify-between rounded-2xl px-3 py-2 text-sm font-medium transition {{ $isActive ? 'bg-slate-950 text-white' : 'bg-white/70 text-slate-700 hover:bg-white' }}"
                    >
                        <span>{{ $label }}</span>
                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $isActive ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $note->{$countField} }}</span>
                    </button>
                </form>
            @endforeach
        </div>
    @endif
</article>
