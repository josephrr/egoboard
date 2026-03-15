<x-layouts.app :title="$room->name.' | Panel docente'" :description="'Panel privado para administrar la sala '.$room->name">
    <main class="mx-auto flex min-h-screen w-full max-w-[96rem] flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
        <section class="hero-card overflow-hidden bg-gradient-to-br {{ $theme['hero'] }} p-8 sm:p-10">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-4xl">
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('rooms.index') }}" class="text-sm font-semibold text-slate-500 transition hover:text-slate-900">Volver al inicio</a>
                        <span class="rounded-full bg-slate-950 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-white">Panel privado</span>
                        <span class="rounded-full {{ $theme['badge'] }} px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em]">{{ $theme['name'] }}</span>
                    </div>
                    <h1 class="mt-4 font-[var(--font-display)] text-4xl font-bold tracking-tight text-slate-950 sm:text-6xl">{{ $room->name }}</h1>
                    <p class="mt-4 max-w-3xl text-base leading-7 text-slate-600 sm:text-lg">{{ $room->description ?: 'Gestiona esta sala, comparte el enlace publico y modera el contenido.' }}</p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('rooms.show', $room) }}" class="btn-primary">Abrir vista publica</a>
                    <a href="{{ route('rooms.export.csv', $room->admin_token) }}" class="btn-secondary">Exportar CSV</a>
                    <a href="{{ route('rooms.export.print', $room->admin_token) }}" class="btn-secondary" target="_blank" rel="noopener noreferrer">Vista PDF</a>
                </div>
            </div>
        </section>

        @if (session('status'))
            <div class="rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <section class="grid gap-6 xl:grid-cols-[0.65fr_0.35fr]">
            <div class="hero-card p-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Enlaces</p>
                        <h2 class="mt-2 font-[var(--font-display)] text-3xl font-bold text-slate-950">Comparte y administra</h2>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-2xl bg-slate-100 px-4 py-3">
                            <p class="text-slate-500">Notas</p>
                            <p class="mt-1 text-2xl font-bold text-slate-950">{{ $room->notes->count() }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-100 px-4 py-3">
                            <p class="text-slate-500">Visibles</p>
                            <p class="mt-1 text-2xl font-bold text-slate-950">{{ $room->notes->where('is_visible', true)->count() }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 grid gap-4 lg:grid-cols-[1fr_220px]">
                    <div class="space-y-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Enlace publico</label>
                            <div class="flex flex-col gap-3 sm:flex-row">
                                <input type="text" readonly value="{{ route('rooms.show', $room) }}" class="field-input">
                                <button type="button" class="btn-secondary" onclick="navigator.clipboard.writeText('{{ route('rooms.show', $room) }}')">Copiar</button>
                            </div>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Enlace privado del docente</label>
                            <div class="flex flex-col gap-3 sm:flex-row">
                                <input type="text" readonly value="{{ route('rooms.teacher', $room->admin_token) }}" class="field-input">
                                <button type="button" class="btn-secondary" onclick="navigator.clipboard.writeText('{{ route('rooms.teacher', $room->admin_token) }}')">Copiar</button>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2rem] bg-white p-4 shadow-sm ring-1 ring-slate-200">
                        <img
                            src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode(route('rooms.show', $room)) }}"
                            alt="QR del enlace publico"
                            class="mx-auto h-auto w-full max-w-[220px] rounded-2xl"
                        >
                        <p class="mt-3 text-center text-xs uppercase tracking-[0.2em] text-slate-400">QR publico</p>
                    </div>
                </div>
            </div>

            <div class="hero-card p-8">
                <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Configuracion</p>
                <h2 class="mt-2 font-[var(--font-display)] text-3xl font-bold text-slate-950">Sala y reglas</h2>

                <form method="POST" action="{{ route('rooms.settings.update', $room->admin_token) }}" class="mt-8 space-y-5">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="theme" class="mb-2 block text-sm font-medium text-slate-700">Tema visual</label>
                        <select id="theme" name="theme" class="field-input">
                            @foreach (\App\Models\Room::THEMES as $key => $config)
                                <option value="{{ $key }}" @selected($room->theme === $key)>{{ $config['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="closes_at" class="mb-2 block text-sm font-medium text-slate-700">Fecha de cierre</label>
                        <input id="closes_at" name="closes_at" type="datetime-local" class="field-input" value="{{ $room->closes_at?->format('Y-m-d\TH:i') }}">
                    </div>

                    <label class="flex items-center gap-3 rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-700">
                        <input type="hidden" name="is_open" value="0">
                        <input type="checkbox" name="is_open" value="1" class="h-4 w-4" @checked($room->is_open)>
                        Sala abierta para nuevas notas
                    </label>

                    <label class="flex items-center gap-3 rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-700">
                        <input type="hidden" name="allow_anonymous" value="0">
                        <input type="checkbox" name="allow_anonymous" value="1" class="h-4 w-4" @checked($room->allow_anonymous)>
                        Permitir notas anonimas
                    </label>

                    <label class="flex items-center gap-3 rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-700">
                        <input type="hidden" name="allow_reactions" value="0">
                        <input type="checkbox" name="allow_reactions" value="1" class="h-4 w-4" @checked($room->allow_reactions)>
                        Permitir reacciones
                    </label>

                    <label class="flex items-center gap-3 rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-700">
                        <input type="hidden" name="allow_one_note_per_participant" value="0">
                        <input type="checkbox" name="allow_one_note_per_participant" value="1" class="h-4 w-4" @checked($room->allow_one_note_per_participant)>
                        Una nota por participante
                    </label>

                    <button type="submit" class="btn-primary w-full">Guardar configuracion</button>
                </form>

                <form method="POST" action="{{ route('rooms.clear', $room->admin_token) }}" class="mt-4" onsubmit="return confirm('Esto eliminara todas las notas de la sala.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-secondary w-full border-rose-200 text-rose-700 hover:bg-rose-50">Limpiar tablero</button>
                </form>
            </div>
        </section>

        <section class="hero-card p-8">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Moderacion</p>
                    <h2 class="mt-2 font-[var(--font-display)] text-3xl font-bold text-slate-950">Gestion de notas</h2>
                </div>
                <p class="text-sm text-slate-500">Oculta o elimina contenido segun lo necesites.</p>
            </div>

            @if ($room->notes->isEmpty())
                <div class="mt-8 rounded-[2rem] border border-dashed border-slate-300 bg-slate-50 px-6 py-16 text-center">
                    <p class="text-lg font-semibold text-slate-800">Aun no hay notas en esta sala.</p>
                </div>
            @else
                <div class="mt-8 grid gap-4">
                    @foreach ($room->notes as $note)
                        <article class="rounded-[1.8rem] border border-slate-200 bg-white p-5 shadow-sm">
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

                                    <form method="POST" action="{{ route('rooms.notes.destroy', [$room->admin_token, $note]) }}" onsubmit="return confirm('Esta accion eliminara la nota.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-secondary border-rose-200 text-rose-700 hover:bg-rose-50">Eliminar</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </main>
</x-layouts.app>
