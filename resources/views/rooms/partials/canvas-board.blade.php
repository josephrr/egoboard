@php
    $backgroundUrl = $room->backgroundImageUrl();
    $existingData = $myDrawing?->canvas_data;
@endphp
<section
    class="space-y-5"
    data-canvas-board
    data-save-url="{{ route('rooms.canvas.store', $room) }}"
    data-mine-url="{{ route('rooms.canvas.mine', $room) }}"
    data-background-url="{{ $backgroundUrl ?? '' }}"
    data-room-closed="{{ $room->isClosed() ? 'true' : 'false' }}"
>
    <script type="application/json" data-canvas-initial-data>{!! $existingData ?? 'null' !!}</script>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-500">Participacion</p>
            <h2 class="mt-2 font-[var(--font-display)] text-3xl font-bold text-slate-950">Dibuja tu respuesta</h2>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <span class="canvas-dirty-pill hidden" data-canvas-dirty-indicator>Cambios sin guardar</span>
            <span class="text-sm text-slate-500" data-canvas-saved-indicator>
                @if ($myDrawing)
                    Ultimo guardado: {{ $myDrawing->updated_at?->diffForHumans() }}
                @else
                    Aun no has guardado.
                @endif
            </span>
            <button type="button" class="btn-secondary" data-open-name-modal>Cambiar nombre</button>
        </div>
    </div>

    @if ($room->isClosed())
        <div class="rounded-3xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-medium text-rose-800">
            Esta sala esta cerrada. Puedes seguir dibujando pero no podras guardar cambios.
        </div>
    @endif

    @include('rooms.partials._canvas-toolbar')

    <div class="hero-card overflow-hidden p-3 sm:p-4">
        <div class="canvas-stage" data-canvas-wrapper>
            <canvas data-canvas-stage width="1600" height="1000"></canvas>
        </div>
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-xs text-slate-500">Tu dibujo se guarda solo cuando pulsas el boton Guardar. Puedes volver a abrir esta sala para continuar.</p>
        <div class="flex flex-wrap gap-2">
            <button type="button" class="btn-secondary" data-canvas-download-png>Descargar mi dibujo</button>
            <button type="button" class="btn-primary" data-canvas-save>Guardar dibujo</button>
        </div>
    </div>
</section>
