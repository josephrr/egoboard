@push('page_vite')
    @vite('resources/js/pages/room-teacher-canvas-edit.js')
@endpush

<x-layouts.app :title="'Editando dibujo | '.$room->name" :description="'Editor docente para el dibujo de '.$drawing->author_name">
    <main
        class="flex min-h-screen w-full flex-col bg-slate-100"
        data-canvas-edit
        data-room-slug="{{ $room->slug }}"
        data-drawing-id="{{ $drawing->id }}"
        data-background-url="{{ $room->backgroundImageUrl() ?? '' }}"
        data-fetch-url="{{ route('rooms.canvas.show', [$room->admin_token, $drawing]) }}"
        data-update-url="{{ route('rooms.canvas.update', [$room->admin_token, $drawing]) }}"
        data-back-url="{{ route('rooms.teacher', $room->admin_token) }}"
    >
        <header class="sticky top-0 z-20 flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-white/95 px-4 py-3 backdrop-blur sm:px-6">
            <div class="flex min-w-0 flex-1 items-center gap-3">
                <a href="{{ route('rooms.teacher', $room->admin_token) }}" class="btn-secondary whitespace-nowrap">&larr; Volver</a>
                <div class="min-w-0">
                    <p class="truncate text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Editando dibujo de</p>
                    <h1 class="truncate text-base font-bold text-slate-950 sm:text-lg" data-canvas-edit-title>{{ $drawing->author_name }}</h1>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="canvas-dirty-pill hidden" data-canvas-edit-dirty>Cambios sin guardar</span>
                <span class="text-xs text-slate-500" data-canvas-edit-saved>Ultimo guardado: {{ $drawing->updated_at?->diffForHumans() }}</span>
                <button type="button" class="btn-secondary" data-canvas-edit-download>Descargar PNG</button>
                <button type="button" class="btn-primary" data-canvas-edit-save>Guardar</button>
            </div>
        </header>

        <div class="sticky top-[4.5rem] z-10 border-b border-slate-200 bg-white/95 px-4 py-3 backdrop-blur sm:px-6">
            @include('rooms.partials._canvas-toolbar')
        </div>

        <div class="flex flex-1 items-start justify-center overflow-auto p-4 sm:p-6">
            <div class="canvas-stage" data-canvas-edit-wrapper>
                <canvas data-canvas-edit-stage width="1600" height="1000"></canvas>
            </div>
        </div>
    </main>
</x-layouts.app>
