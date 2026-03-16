@props([
    'message',
    'tone' => 'success',
])

@php
    $tones = [
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-800',
        'error' => 'border-rose-200 bg-rose-50 text-rose-800',
    ];
@endphp

<div {{ $attributes->class('rounded-3xl border px-5 py-4 text-sm font-medium '.($tones[$tone] ?? $tones['success'])) }}>
    {{ $message }}
</div>
