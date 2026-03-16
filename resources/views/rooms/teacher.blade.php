@push('page_vite')
    @vite('resources/js/pages/room-teacher.js')
@endpush

<x-layouts.app :title="$room->name.' | Panel docente'" :description="'Panel privado para administrar la sala '.$room->name">
    <main class="mx-auto flex min-h-screen w-full max-w-[96rem] flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8 lg:py-12" data-teacher-panel>
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
            <x-ui.flash-message :message="session('status')" />
        @endif

        <section class="grid gap-6 xl:grid-cols-[0.65fr_0.35fr]">
            <div class="hero-card p-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <x-ui.section-heading eyebrow="Enlaces" title="Comparte y administra" />
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <x-ui.stat-card label="Notas" :value="$room->notes->count()" />
                        <x-ui.stat-card label="Visibles" :value="$room->notes->where('is_visible', true)->count()" />
                    </div>
                </div>

                <div class="mt-8 grid gap-4 lg:grid-cols-[1fr_220px]">
                    <div class="space-y-4">
                        <x-rooms.link-field id="public-room-link" label="Enlace publico" :value="route('rooms.show', $room)" />
                        <x-rooms.link-field id="teacher-room-link" label="Enlace privado del docente" :value="route('rooms.teacher', $room->admin_token)" />
                    </div>

                    <div class="rounded-[2rem] bg-white p-4 shadow-sm ring-1 ring-slate-200">
                        <img
                            src="{{ route('rooms.qr', $room->admin_token) }}"
                            alt="QR del enlace publico"
                            class="mx-auto h-auto w-full max-w-[220px] rounded-2xl"
                        >
                        <p class="mt-3 text-center text-xs uppercase tracking-[0.2em] text-slate-400">QR publico</p>
                    </div>
                </div>
            </div>

            <div class="hero-card p-8">
                <x-ui.section-heading eyebrow="Configuracion" title="Sala y reglas" />

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

                    <x-rooms.setting-toggle name="is_open" label="Sala abierta para nuevas notas" :checked="$room->is_open" />
                    <x-rooms.setting-toggle name="allow_anonymous" label="Permitir notas anonimas" :checked="$room->allow_anonymous" />
                    <x-rooms.setting-toggle name="allow_reactions" label="Permitir reacciones" :checked="$room->allow_reactions" />
                    <x-rooms.setting-toggle name="allow_one_note_per_participant" label="Una nota por participante" :checked="$room->allow_one_note_per_participant" />

                    <button type="submit" class="btn-primary w-full">Guardar configuracion</button>
                </form>

                <form method="POST" action="{{ route('rooms.clear', $room->admin_token) }}" class="mt-4" data-confirm-message="Esto eliminara todas las notas de la sala.">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-secondary w-full border-rose-200 text-rose-700 hover:bg-rose-50">Limpiar tablero</button>
                </form>
            </div>
        </section>

        <section class="hero-card p-8">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <x-ui.section-heading eyebrow="Moderacion" title="Gestion de notas" />
                <p class="text-sm text-slate-500">Oculta o elimina contenido segun lo necesites.</p>
            </div>

            @if ($room->notes->isEmpty())
                <div class="mt-8 rounded-[2rem] border border-dashed border-slate-300 bg-slate-50 px-6 py-16 text-center">
                    <p class="text-lg font-semibold text-slate-800">Aun no hay notas en esta sala.</p>
                </div>
            @else
                <div class="mt-8 grid gap-4">
                    @foreach ($room->notes as $note)
                        <x-rooms.teacher-note-card :room="$room" :note="$note" />
                    @endforeach
                </div>
            @endif
        </section>
    </main>
</x-layouts.app>
