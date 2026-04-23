@push('page_vite')
    @vite('resources/js/pages/room-teacher.js')
    @if ($room->isCanvasRoom())
        @vite('resources/js/pages/room-teacher-canvas.js')
    @endif
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
                    <p class="mt-3 text-sm font-semibold uppercase tracking-[0.24em] text-slate-500">{{ $room->typeLabel() }}</p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('rooms.show', $room) }}" class="btn-primary">Abrir vista publica</a>
                    @if (! $room->isCanvasRoom())
                        <a href="{{ route('rooms.export.csv', $room->admin_token) }}" class="btn-secondary">Exportar CSV</a>
                        <a href="{{ route('rooms.export.print', $room->admin_token) }}" class="btn-secondary" target="_blank" rel="noopener noreferrer">Vista PDF</a>
                    @endif
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
                        @if ($room->isQuestionRoom())
                            <x-ui.stat-card label="Preguntas" :value="$room->questions->count()" />
                            <x-ui.stat-card label="Respuestas" :value="$room->questions->sum('answers_count')" />
                        @elseif ($room->isCanvasRoom())
                            <x-ui.stat-card label="Dibujos" :value="$room->canvasDrawings->count()" />
                            <x-ui.stat-card label="Ultima actualizacion" :value="optional($room->canvasDrawings->first())->updated_at?->diffForHumans() ?? '—'" />
                        @else
                            <x-ui.stat-card label="Notas" :value="$room->notes->count()" />
                            <x-ui.stat-card label="Visibles" :value="$room->notes->where('is_visible', true)->count()" />
                        @endif
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

                    @php
                        $openLabel = match (true) {
                            $room->isQuestionRoom() => 'Sala abierta para nuevas respuestas',
                            $room->isCanvasRoom() => 'Sala abierta para nuevos dibujos',
                            default => 'Sala abierta para nuevas notas',
                        };
                    @endphp
                    <x-rooms.setting-toggle name="is_open" :label="$openLabel" :checked="$room->is_open" />
                    @if ($room->isNoteRoom())
                        <x-rooms.setting-toggle name="allow_anonymous" label="Permitir notas anonimas" :checked="$room->allow_anonymous" />
                        <x-rooms.setting-toggle name="allow_reactions" label="Permitir reacciones" :checked="$room->allow_reactions" />
                        <x-rooms.setting-toggle name="allow_one_note_per_participant" label="Una nota por participante" :checked="$room->allow_one_note_per_participant" />
                    @endif

                    <button type="submit" class="btn-primary w-full">Guardar configuracion</button>
                </form>

                @php
                    $clearMessage = match (true) {
                        $room->isQuestionRoom() => 'Esto eliminara todas las preguntas y respuestas de la sala.',
                        $room->isCanvasRoom() => 'Esto eliminara todos los dibujos de la sala.',
                        default => 'Esto eliminara todas las notas de la sala.',
                    };
                    $clearLabel = match (true) {
                        $room->isQuestionRoom() => 'Eliminar preguntas y respuestas',
                        $room->isCanvasRoom() => 'Eliminar todos los dibujos',
                        default => 'Limpiar tablero',
                    };
                @endphp
                <form method="POST" action="{{ route('rooms.clear', $room->admin_token) }}" class="mt-4" data-confirm-message="{{ $clearMessage }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-secondary w-full border-rose-200 text-rose-700 hover:bg-rose-50">{{ $clearLabel }}</button>
                </form>
            </div>
        </section>

        @if ($room->isCanvasRoom())
            @include('rooms.partials.canvas-gallery', ['room' => $room])
        @elseif ($room->isQuestionRoom())
            <section class="grid gap-6 xl:grid-cols-[0.42fr_0.58fr]">
                <div class="hero-card p-8">
                    <x-ui.section-heading eyebrow="Nueva pregunta" title="Construye tu actividad" />
                    <form method="POST" action="{{ route('rooms.questions.store', $room->admin_token) }}" class="mt-8 space-y-5">
                        @csrf
                        <div>
                            <label for="prompt" class="mb-2 block text-sm font-medium text-slate-700">Pregunta</label>
                            <textarea id="prompt" name="prompt" rows="3" class="field-input" placeholder="Ej. Que tema te costo mas entender hoy?" required>{{ old('prompt') }}</textarea>
                            @error('prompt')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="question_type" class="mb-2 block text-sm font-medium text-slate-700">Tipo</label>
                            <select id="question_type" name="question_type" class="field-input" required>
                                @foreach (\App\Models\Question::TYPES as $key => $label)
                                    <option value="{{ $key }}" @selected(old('question_type', 'open') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="options_text" class="mb-2 block text-sm font-medium text-slate-700">Opciones</label>
                            <textarea id="options_text" name="options_text" rows="4" class="field-input" placeholder="Solo para marcar con X. Escribe una opcion por linea.">{{ old('options_text') }}</textarea>
                            <p class="mt-2 text-sm text-slate-500">Para verdadero o falso se crean automaticamente las opciones.</p>
                            @error('options_text')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="btn-primary w-full">Crear pregunta</button>
                    </form>
                </div>

                <div class="hero-card p-8">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <x-ui.section-heading eyebrow="Preguntas" title="Respuestas recibidas" />
                        <p class="text-sm text-slate-500">Puedes revisar el detalle de cada respuesta debajo de cada pregunta.</p>
                    </div>

                    @if ($room->questions->isEmpty())
                        <div class="mt-8 rounded-[2rem] border border-dashed border-slate-300 bg-slate-50 px-6 py-16 text-center">
                            <p class="text-lg font-semibold text-slate-800">Aun no hay preguntas en esta sala.</p>
                        </div>
                    @else
                        <div class="mt-8 grid gap-4">
                            @foreach ($room->questions as $question)
                                <article class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">{{ \App\Models\Question::TYPES[$question->question_type] ?? $question->question_type }}</p>
                                            <h3 class="mt-3 text-xl font-bold text-slate-950">{{ $question->prompt }}</h3>
                                            <p class="mt-2 text-sm text-slate-500">{{ $question->answers_count }} respuestas guardadas</p>
                                        </div>
                                        <form method="POST" action="{{ route('rooms.questions.destroy', [$room->admin_token, $question]) }}" data-confirm-message="Esta accion eliminara la pregunta y sus respuestas.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-secondary border-rose-200 text-rose-700 hover:bg-rose-50">Eliminar</button>
                                        </form>
                                    </div>

                                    @if ($question->question_type !== 'open')
                                        <div class="mt-4 flex flex-wrap gap-2">
                                            @foreach ($question->resolvedOptions() as $option)
                                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $option }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="mt-6 grid gap-3">
                                        @forelse ($question->answers as $answer)
                                            <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                                <p class="text-sm font-semibold text-slate-900">{{ $answer->author_name }}</p>
                                                <p class="mt-1 text-sm text-slate-600">{{ $answer->displayAnswer() }}</p>
                                            </div>
                                        @empty
                                            <div class="rounded-2xl border border-dashed border-slate-200 px-4 py-5 text-sm text-slate-500">
                                                Aun no hay respuestas en esta pregunta.
                                            </div>
                                        @endforelse
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        @else
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
        @endif
    </main>
</x-layouts.app>
