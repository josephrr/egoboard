<x-layouts.app :title="$room->name.' | Muro de notas'" :description="$room->description ?: 'Sala publica para notas compartidas.'">
    @php
        $hasErrors = $errors->any();
    @endphp

    <main
        class="mx-auto flex min-h-screen w-full max-w-[96rem] flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8 lg:py-12"
        data-note-wall
        data-open-on-load="{{ $hasErrors ? 'true' : 'false' }}"
        data-state-url="{{ route('rooms.state', $room) }}"
    >
        <section class="hero-card overflow-hidden bg-gradient-to-br {{ $theme['hero'] }} p-8 sm:p-10">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-4xl">
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('rooms.index') }}" class="text-sm font-semibold text-slate-500 transition hover:text-slate-900">Volver al inicio</a>
                        <span class="rounded-full {{ $theme['badge'] }} px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em]">Sala publica</span>
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

                @if (! $room->isClosed())
                    <div class="flex flex-wrap gap-3">
                        <button type="button" class="btn-primary" data-open-note-modal>Dejar una nota</button>
                    </div>
                @endif
            </div>
        </section>

        @if (session('status'))
            <div class="rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <section class="hero-card p-5 sm:p-6">
            <form method="GET" action="{{ route('rooms.show', $room) }}" class="grid gap-4 md:grid-cols-[1fr_220px_180px]">
                <div>
                    <label for="q" class="mb-2 block text-sm font-medium text-slate-700">Buscar nota</label>
                    <input id="q" name="q" type="text" class="field-input" value="{{ $filters['q'] }}" placeholder="Buscar por texto o nombre">
                </div>
                <div>
                    <label for="category" class="mb-2 block text-sm font-medium text-slate-700">Categoria</label>
                    <select id="category" name="category" class="field-input">
                        <option value="">Todas</option>
                        @foreach (\App\Models\Note::CATEGORIES as $key => $label)
                            <option value="{{ $key }}" @selected($filters['category'] === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="sort" class="mb-2 block text-sm font-medium text-slate-700">Orden</label>
                    <select id="sort" name="sort" class="field-input">
                        <option value="recent" @selected($filters['sort'] === 'recent')>Mas recientes</option>
                        <option value="top" @selected($filters['sort'] === 'top')>Mas votadas</option>
                    </select>
                </div>
                <div class="md:col-span-3 flex flex-wrap gap-3">
                    <button type="submit" class="btn-secondary">Aplicar filtros</button>
                    <a href="{{ route('rooms.show', $room) }}" class="btn-secondary">Limpiar</a>
                </div>
            </form>
        </section>

        <section class="board-shell">
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
                            <article class="note-card {{ $note->color }}">
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
                                            @endphp
                                            <form method="POST" action="{{ route('rooms.notes.react', [$room, $note]) }}">
                                                @csrf
                                                <input type="hidden" name="participant_key" data-participant-key>
                                                <input type="hidden" name="reaction" value="{{ $reaction }}">
                                                <button type="submit" class="flex w-full items-center justify-between rounded-2xl bg-white/70 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-white">
                                                    <span>{{ $label }}</span>
                                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">{{ $note->{$countField} }}</span>
                                                </button>
                                            </form>
                                        @endforeach
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        @if (! $room->isClosed())
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

                    <form method="POST" action="{{ route('rooms.notes.store', $room) }}" class="mt-8 space-y-5">
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const wall = document.querySelector('[data-note-wall]');

            if (!wall) {
                return;
            }

            const roomSlug = @json($room->slug);
            const modal = wall.querySelector('[data-note-modal]');
            const openButtons = wall.querySelectorAll('[data-open-note-modal]');
            const closeButtons = wall.querySelectorAll('[data-close-note-modal]');
            const authorInput = wall.querySelector('[data-author-name]');
            const anonymousToggle = wall.querySelector('[data-anonymous-toggle]');
            const participantInputs = wall.querySelectorAll('[data-participant-key]');
            const storageNameKey = `egoboard.author.${roomSlug}`;
            const storageParticipantKey = `egoboard.participant.${roomSlug}`;
            const stateUrl = wall.dataset.stateUrl;
            let lastStateSignature = null;

            const ensureParticipantKey = () => {
                let key = localStorage.getItem(storageParticipantKey);

                if (!key) {
                    key = window.crypto?.randomUUID?.() ?? `participant-${Date.now()}-${Math.random().toString(36).slice(2, 10)}`;
                    localStorage.setItem(storageParticipantKey, key);
                }

                participantInputs.forEach((input) => {
                    input.value = key;
                });
            };

            const syncAnonymousState = () => {
                if (!authorInput || !anonymousToggle) {
                    return;
                }

                authorInput.disabled = anonymousToggle.checked;

                if (anonymousToggle.checked) {
                    authorInput.removeAttribute('required');
                } else {
                    authorInput.setAttribute('required', 'required');
                }
            };

            const openModal = () => {
                if (!modal) {
                    return;
                }

                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            };

            const closeModal = () => {
                if (!modal) {
                    return;
                }

                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            };

            if (authorInput) {
                const savedName = localStorage.getItem(storageNameKey);

                if (!authorInput.value && savedName) {
                    authorInput.value = savedName;
                }

                authorInput.addEventListener('input', () => {
                    localStorage.setItem(storageNameKey, authorInput.value);
                });
            }

            ensureParticipantKey();

            if (anonymousToggle) {
                anonymousToggle.addEventListener('change', syncAnonymousState);
                syncAnonymousState();
            }

            openButtons.forEach((button) => button.addEventListener('click', openModal));
            closeButtons.forEach((button) => button.addEventListener('click', closeModal));

            window.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeModal();
                }
            });

            if (wall.dataset.openOnLoad === 'true') {
                openModal();
            }

            const buildSignature = (payload) => JSON.stringify([
                payload.room_updated_at,
                payload.note_count,
                payload.last_note_at,
                payload.last_vote_at,
            ]);

            const pollState = async () => {
                if (!stateUrl || document.hidden || modal?.classList.contains('flex')) {
                    return;
                }

                try {
                    const response = await fetch(stateUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();
                    const nextSignature = buildSignature(payload);

                    if (lastStateSignature && nextSignature !== lastStateSignature) {
                        window.location.reload();
                        return;
                    }

                    lastStateSignature = nextSignature;
                } catch (error) {
                    console.error('No se pudo actualizar el estado del muro.', error);
                }
            };

            pollState();
            window.setInterval(pollState, 15000);
        });
    </script>
</x-layouts.app>
