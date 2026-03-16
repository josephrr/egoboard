@props([
    'id',
    'label',
    'value',
])

<div>
    <label class="mb-2 block text-sm font-medium text-slate-700">{{ $label }}</label>
    <div class="flex flex-col gap-3 sm:flex-row">
        <input id="{{ $id }}" type="text" readonly value="{{ $value }}" class="field-input">
        <button type="button" class="btn-secondary" data-copy-target="#{{ $id }}">Copiar</button>
    </div>
</div>
