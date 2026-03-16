@props([
    'label',
    'value',
    'tone' => 'slate',
])

@php
    $tones = [
        'slate' => 'bg-slate-100 text-slate-500',
        'orange' => 'bg-orange-50 text-orange-500',
        'teal' => 'bg-teal-50 text-teal-600',
    ];
@endphp

<div {{ $attributes->class('rounded-2xl px-4 py-3 '.($tones[$tone] ?? $tones['slate'])) }}>
    <p>{{ $label }}</p>
    <p class="mt-1 text-2xl font-bold text-slate-950">{{ $value }}</p>
</div>
