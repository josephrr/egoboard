@props([
    'eyebrow',
    'title',
    'description' => null,
])

<div {{ $attributes->class('') }}>
    <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">{{ $eyebrow }}</p>
    <h2 class="mt-2 font-[var(--font-display)] text-3xl font-bold text-slate-950">{{ $title }}</h2>
    @if ($description)
        <p class="mt-2 text-sm text-slate-500">{{ $description }}</p>
    @endif
</div>
