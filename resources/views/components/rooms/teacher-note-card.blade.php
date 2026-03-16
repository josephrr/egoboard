@props([
    'room',
    'note',
])

<article {{ $attributes->class('rounded-[1.8rem] border border-slate-200 bg-white p-5 shadow-sm') }}>
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap gap-2">
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600">
                    {{ \App\Models\Note::CATEGORIES[$note->category] ?? $note->category }}
                </span>
                <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] {{ $note->is_visible ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                    {{ $note->is_visible ? 'Visible' : 'Oculta' }}
                </span>
                @if ($note->is_anonymous)
                    <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-sky-700">Anonima</span>
                @endif
            </div>
            <p class="mt-4 text-lg leading-7 text-slate-900">{{ $note->message }}</p>
            <div class="mt-4 flex flex-wrap gap-4 text-sm text-slate-500">
                <span>{{ $note->displayName() }}</span>
                <span>{{ $note->votes_count }} votos</span>
                <span>{{ $note->created_at->diffForHumans() }}</span>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <form method="POST" action="{{ route('rooms.notes.visibility', [$room->admin_token, $note]) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn-secondary">{{ $note->is_visible ? 'Ocultar' : 'Mostrar' }}</button>
            </form>

            <form method="POST" action="{{ route('rooms.notes.destroy', [$room->admin_token, $note]) }}" data-confirm-message="Esta accion eliminara la nota.">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-secondary border-rose-200 text-rose-700 hover:bg-rose-50">Eliminar</button>
            </form>
        </div>
    </div>
</article>
