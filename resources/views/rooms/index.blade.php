<x-layouts.app title="Salas interactivas para estudiantes" description="Crea salas publicas para recibir notas o preguntas de tus estudiantes.">
    <main class="mx-auto flex min-h-screen w-full max-w-7xl flex-col gap-10 px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
        <section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
            <div class="hero-card p-8 sm:p-10">
                <span class="inline-flex rounded-full bg-slate-950 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.28em] text-white">
                    Publico para estudiantes, privado para docente
                </span>
                <h1 class="mt-6 max-w-3xl font-[var(--font-display)] text-4xl font-bold tracking-tight text-slate-950 sm:text-6xl">
                    Crea salas compartibles para recibir notas o respuestas en segundos.
                </h1>
                <p class="mt-5 max-w-2xl text-base leading-7 text-slate-600 sm:text-lg">
                    Genera una sala, comparte el enlace publico con tu grupo y conserva un enlace secreto para moderar, exportar y administrar el contenido.
                </p>

                <div class="mt-8 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-3xl bg-slate-950 px-5 py-5 text-white">
                        <p class="text-xs uppercase tracking-[0.25em] text-white/70">1</p>
                        <p class="mt-3 text-lg font-semibold">Creas la sala</p>
                    </div>
                    <div class="rounded-3xl bg-white px-5 py-5 shadow-sm ring-1 ring-slate-200">
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-400">2</p>
                        <p class="mt-3 text-lg font-semibold text-slate-900">Compartes el link</p>
                    </div>
                    <div class="rounded-3xl bg-white px-5 py-5 shadow-sm ring-1 ring-slate-200">
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-400">3</p>
                        <p class="mt-3 text-lg font-semibold text-slate-900">Llegan respuestas</p>
                    </div>
                </div>
            </div>

            <div class="hero-card p-8">
                <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Nueva sala</p>
                <h2 class="mt-3 font-[var(--font-display)] text-3xl font-bold text-slate-950">Prepara tu enlace</h2>
                <p class="mt-3 text-sm leading-6 text-slate-500">Al crearla recibiras un link publico para tus estudiantes y otro privado para ti.</p>

                <form method="POST" action="{{ route('rooms.store') }}" class="mt-8 space-y-5">
                    @csrf
                    <div>
                        <label for="name" class="mb-2 block text-sm font-medium text-slate-700">Nombre de la sala</label>
                        <input id="name" name="name" type="text" class="field-input" placeholder="Ej. Muro de ideas 10B" value="{{ old('name') }}" required>
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="mb-2 block text-sm font-medium text-slate-700">Descripcion corta</label>
                        <textarea id="description" name="description" rows="3" class="field-input" placeholder="Ej. Problemas reales detectados por los estudiantes">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="room_type" class="mb-2 block text-sm font-medium text-slate-700">Tipo de sala</label>
                        <select id="room_type" name="room_type" class="field-input" required>
                            @foreach (\App\Models\Room::TYPES as $key => $label)
                                <option value="{{ $key }}" @selected(old('room_type', 'notes') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('room_type')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="theme" class="mb-2 block text-sm font-medium text-slate-700">Tema visual</label>
                        <select id="theme" name="theme" class="field-input" required>
                            <option value="sunrise" @selected(old('theme', 'sunrise') === 'sunrise')>Sunrise</option>
                            <option value="ocean" @selected(old('theme') === 'ocean')>Ocean</option>
                            <option value="forest" @selected(old('theme') === 'forest')>Forest</option>
                        </select>
                        @error('theme')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="btn-primary w-full">Crear sala y obtener enlace</button>
                </form>
            </div>
        </section>

        <section class="hero-card p-8 sm:p-10">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Salas recientes</p>
                    <h2 class="mt-2 font-[var(--font-display)] text-3xl font-bold text-slate-950">Continua donde te quedaste</h2>
                </div>
                <p class="text-sm text-slate-500">Las ultimas salas creadas aparecen aqui para volver a abrirlas rapido.</p>
            </div>

            @if ($rooms->isEmpty())
                <div class="mt-8 rounded-[2rem] border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
                    <p class="text-lg font-semibold text-slate-800">Todavia no hay salas creadas.</p>
                    <p class="mt-2 text-sm text-slate-500">Crea la primera y comparte el enlace con tu grupo.</p>
                </div>
            @else
                <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($rooms as $room)
                        <a href="{{ route('rooms.show', $room) }}" class="group rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Sala</p>
                                    <h3 class="mt-3 text-xl font-bold text-slate-950">{{ $room->name }}</h3>
                                </div>
                                <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-semibold text-orange-700">
                                    {{ $room->isQuestionRoom() ? $room->questions_count.' preguntas' : $room->notes_count.' notas' }}
                                </span>
                            </div>
                            @if ($room->description)
                                <p class="mt-4 line-clamp-3 text-sm leading-6 text-slate-600">{{ $room->description }}</p>
                            @endif
                            <div class="mt-6 flex items-center justify-between">
                                <p class="text-sm font-semibold text-slate-950 group-hover:text-orange-700">{{ $room->typeLabel() }}</p>
                                <span class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ \App\Models\Room::THEMES[$room->theme]['name'] ?? 'Theme' }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
    </main>
</x-layouts.app>
