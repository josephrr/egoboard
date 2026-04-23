<x-layouts.app title="Listado de salas" description="Consulta todas las salas creadas y entra rapido a su vista publica o privada de docente.">
    <main class="mx-auto flex min-h-screen w-full max-w-7xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
        <section class="hero-card p-8 sm:p-10">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Panel docente</p>
                    <h1 class="mt-3 font-[var(--font-display)] text-4xl font-bold tracking-tight text-slate-950 sm:text-5xl">Listado completo de salas</h1>
                    <p class="mt-4 max-w-3xl text-base leading-7 text-slate-600 sm:text-lg">
                        Aqui puedes ver todas las salas creadas y abrir tanto la vista publica como el panel privado del docente.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('rooms.index') }}" class="btn-secondary">Volver al inicio</a>
                    <span class="inline-flex items-center rounded-full bg-slate-950 px-4 py-2 text-sm font-semibold text-white">
                        {{ $rooms->count() }} salas
                    </span>
                </div>
            </div>
        </section>

        <section class="hero-card p-8 sm:p-10">
            @if ($rooms->isEmpty())
                <div class="rounded-[2rem] border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
                    <p class="text-lg font-semibold text-slate-800">Todavia no hay salas creadas.</p>
                    <p class="mt-2 text-sm text-slate-500">Cuando crees una sala aparecera en este listado.</p>
                </div>
            @else
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($rooms as $room)
                        <article class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Sala</p>
                                    <h2 class="mt-3 text-xl font-bold text-slate-950">{{ $room->name }}</h2>
                                </div>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ \App\Models\Room::THEMES[$room->theme]['badge'] ?? 'bg-slate-100 text-slate-700' }}">
                                    {{ \App\Models\Room::THEMES[$room->theme]['name'] ?? 'Theme' }}
                                </span>
                            </div>

                            @if ($room->description)
                                <p class="mt-4 line-clamp-3 text-sm leading-6 text-slate-600">{{ $room->description }}</p>
                            @endif

                            <div class="mt-6 grid gap-3 text-sm text-slate-600">
                                <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                                    <span>{{ $room->typeLabel() }}</span>
                                    <span class="font-semibold text-slate-900">
                                        @if ($room->isQuestionRoom())
                                            {{ $room->questions_count }} preguntas
                                        @elseif ($room->isCanvasRoom())
                                            {{ $room->canvas_drawings_count ?? 0 }} dibujos
                                        @else
                                            {{ $room->notes_count }} notas
                                        @endif
                                    </span>
                                </div>
                                <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                                    <span>Estado</span>
                                    <span class="font-semibold {{ $room->isClosed() ? 'text-rose-700' : 'text-emerald-700' }}">
                                        {{ $room->isClosed() ? 'Cerrada' : 'Abierta' }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-6 flex flex-wrap gap-3">
                                <a href="{{ route('rooms.show', $room) }}" class="btn-secondary">Vista publica</a>
                                <a href="{{ route('rooms.teacher', $room->admin_token) }}" class="btn-primary">Panel docente</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </main>
</x-layouts.app>
