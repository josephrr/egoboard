@php
    $toolbarId = $toolbarId ?? 'canvas-toolbar';
    $colors = ['#0f172a', '#ef4444', '#f97316', '#f59e0b', '#10b981', '#0ea5e9', '#6366f1', '#ec4899', '#ffffff'];
@endphp
<div class="canvas-toolbar" data-canvas-toolbar>
    <div class="flex flex-wrap items-center gap-1" role="group" aria-label="Herramientas">
        <button type="button" class="canvas-tool-btn canvas-tool-btn-active" data-canvas-tool="pen" title="Lapiz" aria-label="Lapiz">&#9998;</button>
        <button type="button" class="canvas-tool-btn" data-canvas-tool="eraser" title="Borrar" aria-label="Borrar">&#10007;</button>
        <button type="button" class="canvas-tool-btn" data-canvas-tool="select" title="Seleccionar" aria-label="Seleccionar">&#10016;</button>
        <span class="mx-1 h-6 w-px bg-slate-200" aria-hidden="true"></span>
        <button type="button" class="canvas-tool-btn" data-canvas-tool="rect" title="Rectangulo" aria-label="Rectangulo">&#9645;</button>
        <button type="button" class="canvas-tool-btn" data-canvas-tool="circle" title="Circulo" aria-label="Circulo">&#9675;</button>
        <button type="button" class="canvas-tool-btn" data-canvas-tool="triangle" title="Triangulo" aria-label="Triangulo">&#9651;</button>
        <button type="button" class="canvas-tool-btn" data-canvas-tool="line" title="Linea" aria-label="Linea">&#9585;</button>
        <button type="button" class="canvas-tool-btn" data-canvas-tool="arrow" title="Flecha" aria-label="Flecha">&#10140;</button>
        <button type="button" class="canvas-tool-btn" data-canvas-tool="text" title="Texto" aria-label="Texto">T</button>
    </div>

    <div class="flex flex-wrap items-center gap-1" role="group" aria-label="Colores">
        @foreach ($colors as $color)
            <button
                type="button"
                class="canvas-color-swatch"
                style="background-color: {{ $color }}; border: 1px solid rgba(15,23,42,0.12);"
                data-canvas-color="{{ $color }}"
                aria-label="Color {{ $color }}"
            ></button>
        @endforeach
    </div>

    <div class="flex flex-wrap items-center gap-2" role="group" aria-label="Grosor">
        <label class="flex items-center gap-2 text-xs font-semibold text-slate-500">
            Grosor
            <input type="range" min="1" max="40" value="4" class="accent-orange-500" data-canvas-width>
        </label>
    </div>

    <div class="flex flex-wrap items-center gap-2" role="group" aria-label="Tamano de texto">
        <label class="flex items-center gap-2 text-xs font-semibold text-slate-500">
            Texto
            <select class="field-input h-9 w-auto min-w-[5rem] px-2 py-1 text-xs" data-canvas-font-size>
                <option value="14">14</option>
                <option value="18">18</option>
                <option value="24">24</option>
                <option value="32" selected>32</option>
                <option value="48">48</option>
                <option value="64">64</option>
                <option value="96">96</option>
                <option value="128">128</option>
            </select>
        </label>
    </div>

    <div class="ml-auto flex flex-wrap items-center gap-1" role="group" aria-label="Acciones">
        <button type="button" class="canvas-tool-btn" data-canvas-action="undo" title="Deshacer (Ctrl+Z / Cmd+Z)" aria-label="Deshacer">&#8630;</button>
        <button type="button" class="canvas-tool-btn" data-canvas-action="redo" title="Rehacer (Ctrl+Y / Ctrl+Shift+Z / Cmd+Shift+Z)" aria-label="Rehacer">&#8631;</button>
        <button type="button" class="canvas-tool-btn" data-canvas-action="clear" title="Limpiar todo" aria-label="Limpiar todo">&#128465;</button>
    </div>
</div>
