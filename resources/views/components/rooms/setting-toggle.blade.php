@props([
    'name',
    'label',
    'checked' => false,
])

<label {{ $attributes->class('flex items-center gap-3 rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-700') }}>
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" name="{{ $name }}" value="1" class="h-4 w-4" @checked($checked)>
    {{ $label }}
</label>
