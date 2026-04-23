@push('page_vite')
    @vite('resources/js/pages/room-show.js')
@endpush

<x-layouts.app :title="$room->name.' | '.$room->typeLabel()" :description="$room->description ?: 'Sala publica para participacion compartida.'">
    @php
        $hasErrors = $errors->any();
    @endphp

    <main
        class="mx-auto flex min-h-screen w-full max-w-[96rem] flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8 lg:py-12"
        @if ($room->isCanvasRoom())
            data-canvas-room
        @elseif ($room->isNoteRoom())
            data-note-wall
        @else
            data-question-room
        @endif
        data-room-slug="{{ $room->slug }}"
        data-open-on-load="{{ $hasErrors ? 'true' : 'false' }}"
        data-state-url="{{ route('rooms.state', $room) }}"
        data-board-url="{{ route('rooms.board', $room) }}"
        data-board-signature="{{ $boardSignature }}"
    >
        <section class="hero-card overflow-hidden bg-gradient-to-br {{ $theme['hero'] }} p-8 sm:p-10">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-4xl">
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('rooms.index') }}" class="text-sm font-semibold text-slate-500 transition hover:text-slate-900">Volver al inicio</a>
                        <span class="rounded-full {{ $theme['badge'] }} px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em]">{{ $room->typeLabel() }}</span>
                        @if ($room->isClosed())
                            <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-rose-700">Cerrada</span>
                        @endif
                    </div>
                    <h1 class="mt-4 font-[var(--font-display)] text-4xl font-bold tracking-tight text-slate-950 sm:text-6xl">{{ $room->name }}</h1>
                    @if ($room->description)
                        <p class="mt-4 max-w-3xl text-base leading-7 text-slate-600 sm:text-lg">{{ $room->description }}</p>
                    @endif
                    @if ($room->closes_at)
                        <p class="mt-4 text-sm font-medium text-slate-500">Disponible hasta {{ $room->closes_at->format('d/m/Y H:i') }}</p>
                    @endif
                </div>

                @if (! $room->isClosed() && $room->isNoteRoom())
                    <div class="flex flex-wrap gap-3">
                        <button type="button" class="btn-primary" data-open-note-modal>Dejar una nota</button>
                    </div>
                @endif
            </div>
        </section>

        @if (session('status'))
            <x-ui.flash-message :message="session('status')" />
        @endif

        <div class="hidden rounded-3xl border px-5 py-4 text-sm font-medium" data-status-banner></div>

        @if ($room->isNoteRoom())
            <x-rooms.filters :room="$room" :filters="$filters" />
        @endif

        <div data-board-region>
            @if ($room->isCanvasRoom())
                @include('rooms.partials.canvas-board', ['room' => $room, 'myDrawing' => $myDrawing ?? null, 'participantKey' => $participantKey ?? ''])
            @elseif ($room->isQuestionRoom())
                @include('rooms.partials.question-board', ['room' => $room, 'questions' => $questions, 'boardSignature' => $boardSignature])
            @else
                @include('rooms.partials.board', ['room' => $room, 'notes' => $notes, 'boardSignature' => $boardSignature])
            @endif
        </div>

        @if (($room->isQuestionRoom() || $room->isCanvasRoom()) && ! $room->isClosed())
            <div class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm" data-name-modal>
                <div class="absolute inset-0" data-close-name-modal></div>

                <div class="modal-panel relative">
                    <button
                        type="button"
                        class="absolute right-4 top-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-900"
                        data-close-name-modal
                    >
                        X
                    </button>

                    <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Antes de responder</p>
                    <h2 class="mt-3 font-[var(--font-display)] text-3xl font-bold text-slate-950">Nombre completo</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-500">Escribe tu nombre una sola vez para identificar tus respuestas dentro de esta sala.</p>

                    <form class="mt-8 space-y-5" data-name-form>
                        <div>
                            <label for="question_author_name" class="mb-2 block text-sm font-medium text-slate-700">Nombre completo</label>
                            <input id="question_author_name" type="text" class="field-input" placeholder="Ej. Camila Rojas Perez" data-author-name required>
                        </div>

                        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                            <button type="button" class="btn-secondary" data-close-name-modal>Continuar despues</button>
                            <button type="submit" class="btn-primary">Guardar nombre</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @if (! $room->isClosed() && $room->isNoteRoom())
            <div class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm" data-note-modal>
                <div class="absolute inset-0" data-close-note-modal></div>

                <div class="modal-panel relative">
                    <button
                        type="button"
                        class="absolute right-4 top-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-900"
                        data-close-note-modal
                    >
                        X
                    </button>

                    <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Tu aporte</p>
                    <h2 class="mt-3 font-[var(--font-display)] text-3xl font-bold text-slate-950">Deja tu nota</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-500">Tu nombre se guarda localmente para no tener que escribirlo cada vez.</p>

                    <form method="POST" action="{{ route('rooms.notes.store', $room) }}" class="mt-8 space-y-5" data-note-form>
                        @csrf
                        <input type="hidden" name="participant_key" data-participant-key>

                        <div>
                            <label for="author_name" class="mb-2 block text-sm font-medium text-slate-700">Nombre</label>
                            <input id="author_name" name="author_name" type="text" class="field-input" placeholder="Tu nombre" value="{{ old('author_name') }}" data-author-name>
                            @error('author_name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="category" class="mb-2 block text-sm font-medium text-slate-700">Categoria</label>
                            <select id="category" name="category" class="field-input" required>
                                @foreach (\App\Models\Note::CATEGORIES as $key => $label)
                                    <option value="{{ $key }}" @selected(old('category', 'idea') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if ($room->allow_anonymous)
                            <label class="flex items-center gap-3 rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                <input type="checkbox" name="is_anonymous" value="1" class="h-4 w-4 rounded border-slate-300 text-slate-950" data-anonymous-toggle @checked(old('is_anonymous'))>
                                Publicar como anonimo
                            </label>
                        @endif

                        <div>
                            <label for="message" class="mb-2 block text-sm font-medium text-slate-700">Nota</label>
                            <textarea id="message" name="message" rows="5" class="field-input" placeholder="Escribe tu idea, problema o comentario" required>{{ old('message') }}</textarea>
                            @error('message')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                            <button type="button" class="btn-secondary" data-close-note-modal>Cerrar</button>
                            <button type="submit" class="btn-primary">Compartir nota</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </main>
</x-layouts.app>
