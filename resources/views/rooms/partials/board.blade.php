<section class="board-shell" data-board-signature="{{ $boardSignature }}">
    <div class="flex flex-col gap-3 px-2 pb-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Muro</p>
            <h2 class="mt-2 font-[var(--font-display)] text-3xl font-bold text-slate-950 sm:text-4xl">Notas compartidas</h2>
        </div>
        <p class="text-sm text-slate-500">{{ $notes->count() }} resultados visibles</p>
    </div>

    <div class="board-grid">
        @if ($notes->isEmpty())
            <div class="rounded-[2rem] border border-dashed border-slate-300 bg-white/70 px-6 py-24 text-center">
                <p class="text-lg font-semibold text-slate-800">No hay notas para mostrar.</p>
                <p class="mt-2 text-sm text-slate-500">Prueba cambiar los filtros o agrega una nueva nota.</p>
            </div>
        @else
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                @foreach ($notes as $note)
                    <x-rooms.note-card :note="$note" :room="$room" />
                @endforeach
            </div>
        @endif
    </div>
</section>
