<x-layouts.app :title="$room->name.' | Muro de notas'" :description="$room->description ?: 'Sala publica para recibir notas de estudiantes.'">
    @php
        $hasErrors = $errors->any();
    @endphp

    <main
        class="mx-auto flex min-h-screen w-full max-w-[92rem] flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8 lg:py-12"
        data-note-wall
        data-open-on-load="{{ $hasErrors ? 'true' : 'false' }}"
    >
        <section class="grid gap-6 xl:grid-cols-[0.72fr_0.28fr]">
            <div class="hero-card p-8 sm:p-10">
                <a href="{{ route('rooms.index') }}" class="text-sm font-semibold text-slate-500 transition hover:text-slate-900">Ver inicio</a>
                <div class="mt-4 flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                    <div>
                        <h1 class="mt-3 font-[var(--font-display)] text-4xl font-bold tracking-tight text-slate-950 sm:text-6xl">{{ $room->name }}</h1>
                        @if ($room->description)
                            <p class="mt-4 max-w-3xl text-base leading-7 text-slate-600 sm:text-lg">{{ $room->description }}</p>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button type="button" class="btn-primary" data-open-note-modal>Dejar una nota</button>
                        <button type="button" class="btn-secondary" onclick="navigator.clipboard.writeText('{{ route('rooms.show', $room) }}')">Copiar enlace</button>
                    </div>
                </div>
            </div>

            <div class="hero-card p-6 sm:p-7">
                <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Participa</p>
                <div class="mt-4 rounded-[1.6rem] bg-slate-950 p-4 text-white">
                    <p class="text-xs uppercase tracking-[0.25em] text-white/60">Enlace del muro</p>
                    <p class="mt-3 break-all text-sm leading-6 text-white/90">{{ route('rooms.show', $room) }}</p>
                </div>
                <div class="mt-5 grid grid-cols-2 gap-3">
                    <div class="rounded-[1.4rem] bg-orange-50 p-4">
                        <p class="text-xs uppercase tracking-[0.25em] text-orange-500">Publicadas</p>
                        <p class="mt-2 text-2xl font-bold text-slate-950">{{ $room->notes->count() }}</p>
                    </div>
                    <div class="rounded-[1.4rem] bg-teal-50 p-4">
                        <p class="text-xs uppercase tracking-[0.25em] text-teal-600">Dinamica</p>
                        <p class="mt-2 text-sm font-semibold text-slate-950">Abierto para el grupo</p>
                    </div>
                </div>
            </div>
        </section>

        @if (session('status'))
            <div class="rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <section class="board-shell">
            <div class="flex flex-col gap-3 px-2 pb-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Muro</p>
                    <h2 class="mt-2 font-[var(--font-display)] text-3xl font-bold text-slate-950 sm:text-4xl">Notas compartidas</h2>
                </div>
                <p class="text-sm text-slate-500">Un espacio abierto para ideas, comentarios y hallazgos del grupo.</p>
            </div>

            <div class="board-grid">
                @if ($room->notes->isEmpty())
                    <div class="rounded-[2rem] border border-dashed border-slate-300 bg-white/70 px-6 py-24 text-center">
                        <p class="text-lg font-semibold text-slate-800">Todavia no hay notas en esta sala.</p>
                        <p class="mt-2 text-sm text-slate-500">Haz clic en "Dejar una nota" para escribir la primera.</p>
                    </div>
                @else
                    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                        @foreach ($room->notes as $note)
                            <article class="note-card {{ $note->color }}">
                                <div class="absolute left-1/2 top-3 h-3.5 w-3.5 -translate-x-1/2 rounded-full bg-red-600/80 shadow-inner"></div>
                                <p class="mt-4 text-lg font-medium leading-7 text-slate-900">
                                    "{{ $note->message }}"
                                </p>
                                <div class="mt-8 flex items-center justify-between border-t border-black/10 pt-4">
                                    <span class="text-xs font-black uppercase tracking-[0.24em] text-slate-700">{{ $note->author_name }}</span>
                                    <span class="text-xs text-slate-500">{{ $note->created_at->diffForHumans() }}</span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

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
                <p class="mt-3 text-sm leading-6 text-slate-500">Escribe tu nombre y tu mensaje para sumarlo al muro compartido.</p>

                <form method="POST" action="{{ route('rooms.notes.store', $room) }}" class="mt-8 space-y-5">
                    @csrf
                    <div>
                        <label for="author_name" class="mb-2 block text-sm font-medium text-slate-700">Nombre</label>
                        <input id="author_name" name="author_name" type="text" class="field-input" placeholder="Tu nombre" value="{{ old('author_name') }}" required>
                        @error('author_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

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
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const wall = document.querySelector('[data-note-wall]');

            if (!wall) {
                return;
            }

            const modal = wall.querySelector('[data-note-modal]');
            const openButtons = wall.querySelectorAll('[data-open-note-modal]');
            const closeButtons = wall.querySelectorAll('[data-close-note-modal]');

            const openModal = () => {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            };

            const closeModal = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            };

            openButtons.forEach((button) => {
                button.addEventListener('click', openModal);
            });

            closeButtons.forEach((button) => {
                button.addEventListener('click', closeModal);
            });

            window.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeModal();
                }
            });

            if (wall.dataset.openOnLoad === 'true') {
                openModal();
            }
        });
    </script>
</x-layouts.app>
