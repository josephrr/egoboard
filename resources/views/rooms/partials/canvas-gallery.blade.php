@php
    $drawings = $room->canvasDrawings ?? collect();
@endphp
<section
    class="hero-card p-8"
    data-canvas-gallery
    data-background-url="{{ $room->backgroundImageUrl() ?? '' }}"
    data-state-url="{{ route('rooms.state', $room) }}"
    data-board-signature="{{ $boardSignature ?? '' }}"
>
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <x-ui.section-heading eyebrow="Dibujos de la sala" title="Galeria de la clase" />
        <p class="text-sm text-slate-500">{{ $drawings->count() }} dibujos guardados</p>
    </div>

    @if ($drawings->isEmpty())
        <div class="mt-8 rounded-[2rem] border border-dashed border-slate-300 bg-slate-50 px-6 py-16 text-center">
            <p class="text-lg font-semibold text-slate-800">Aun no hay dibujos en esta sala.</p>
            <p class="mt-2 text-sm text-slate-500">Cuando tus estudiantes guarden su dibujo apareceran aqui.</p>
        </div>
    @else
        <div class="canvas-gallery-grid mt-8" data-canvas-gallery-list>
            @foreach ($drawings as $drawing)
                <article
                    class="canvas-gallery-card"
                    data-canvas-card
                    data-drawing-id="{{ $drawing->id }}"
                    data-drawing-fetch-url="{{ route('rooms.canvas.show', [$room->admin_token, $drawing]) }}"
                    data-drawing-delete-url="{{ route('rooms.canvas.destroy', [$room->admin_token, $drawing]) }}"
                    data-drawing-author="{{ $drawing->author_name }}"
                >
                    @if ($drawing->preview_png)
                        <img class="canvas-gallery-thumb" src="{{ $drawing->preview_png }}" alt="Dibujo de {{ $drawing->author_name }}">
                    @else
                        <div class="canvas-gallery-thumb flex items-center justify-center text-sm text-slate-400">Sin miniatura</div>
                    @endif
                    <div class="space-y-3 p-5">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $drawing->author_name }}</p>
                            <p class="text-xs text-slate-500">Actualizado {{ $drawing->updated_at?->diffForHumans() }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('rooms.canvas.edit', [$room->admin_token, $drawing]) }}" class="btn-primary text-xs">Abrir y editar</a>
                            <button type="button" class="btn-secondary text-xs" data-canvas-download>PNG</button>
                            <button type="button" class="btn-secondary border-rose-200 text-xs text-rose-700 hover:bg-rose-50" data-canvas-delete>Eliminar</button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

</section>
