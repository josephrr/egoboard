<section class="space-y-6">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-500">Participacion</p>
            <h2 class="mt-2 font-[var(--font-display)] text-3xl font-bold text-slate-950">Responde las preguntas</h2>
        </div>
        <p class="text-sm text-slate-500">{{ $questions->count() }} preguntas activas</p>
    </div>

    <div class="hero-card p-6 sm:p-8">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-900">Participas con tu nombre completo</p>
                <p class="mt-1 text-sm text-slate-500">Tu nombre se guarda en este dispositivo para no pedirlo cada vez que entras a la sala.</p>
            </div>
            <button type="button" class="btn-secondary" data-open-name-modal>Cambiar nombre</button>
        </div>
    </div>

    @if ($questions->isEmpty())
        <div class="rounded-[2rem] border border-dashed border-slate-300 bg-slate-50 px-6 py-16 text-center">
            <p class="text-lg font-semibold text-slate-800">Todavia no hay preguntas disponibles.</p>
            <p class="mt-2 text-sm text-slate-500">Cuando el docente agregue preguntas apareceran aqui automaticamente.</p>
        </div>
    @else
        <div class="grid gap-5">
            @foreach ($questions as $question)
                @php
                    $existingAnswer = $participantKey ? $question->answers->firstWhere('participant_key', $participantKey) : null;
                    $resolvedOptions = $question->resolvedOptions();
                @endphp
                <article class="hero-card p-6 sm:p-8">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">{{ \App\Models\Question::TYPES[$question->question_type] ?? $question->question_type }}</p>
                            <h3 class="mt-3 text-2xl font-bold text-slate-950">{{ $question->prompt }}</h3>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $question->answers_count }} respuestas</span>
                    </div>

                    <form method="POST" action="{{ route('rooms.questions.answers.store', [$room, $question]) }}" class="mt-6 space-y-4" data-question-answer-form>
                        @csrf
                        <input type="hidden" name="participant_key" data-participant-key>
                        <input type="hidden" name="author_name" data-hidden-author-name>

                        @if ($question->question_type === 'open')
                            <div>
                                <label for="answer_text_{{ $question->id }}" class="mb-2 block text-sm font-medium text-slate-700">Tu respuesta</label>
                                <textarea id="answer_text_{{ $question->id }}" name="answer_text" rows="4" class="field-input" placeholder="Escribe tu respuesta">{{ old('answer_text', $existingAnswer?->answer_text) }}</textarea>
                            </div>
                        @else
                            <div class="grid gap-3">
                                @foreach ($resolvedOptions as $option)
                                    <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                                        <input type="radio" name="selected_option" value="{{ $option }}" class="h-4 w-4 border-slate-300 text-slate-950" @checked(old('selected_option', $existingAnswer?->selected_option) === $option)>
                                        <span>{{ $option }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            @if ($existingAnswer)
                                <p class="text-sm text-slate-500">Tu respuesta ya esta cargada y puedes editarla cuando quieras.</p>
                            @else
                                <p class="text-sm text-slate-500">Cada participante puede guardar una respuesta por pregunta.</p>
                            @endif
                            <button type="submit" class="btn-primary">{{ $existingAnswer ? 'Actualizar respuesta' : 'Guardar respuesta' }}</button>
                        </div>
                    </form>

                    <div class="mt-6 rounded-[1.75rem] bg-slate-50 p-5">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-semibold text-slate-900">Respuestas de los participantes</p>
                            <span class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Publicas</span>
                        </div>

                        <div class="mt-4 grid gap-3">
                            @forelse ($question->answers as $answer)
                                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-sm font-semibold text-slate-900">{{ $answer->author_name }}</p>
                                        @if ($participantKey !== '' && $answer->participant_key === $participantKey)
                                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Tu respuesta</span>
                                        @endif
                                    </div>
                                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $answer->displayAnswer() }}</p>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-200 px-4 py-5 text-sm text-slate-500">
                                    Aun no hay respuestas en esta pregunta.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</section>
