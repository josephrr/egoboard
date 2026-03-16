@props([
    'room',
    'filters',
])

<section {{ $attributes->class('hero-card p-5 sm:p-6') }}>
    <form method="GET" action="{{ route('rooms.show', $room) }}" class="grid gap-4 md:grid-cols-[1fr_220px_180px]">
        <div>
            <label for="q" class="mb-2 block text-sm font-medium text-slate-700">Buscar nota</label>
            <input id="q" name="q" type="text" class="field-input" value="{{ $filters['q'] }}" placeholder="Buscar por texto o nombre">
        </div>
        <div>
            <label for="category" class="mb-2 block text-sm font-medium text-slate-700">Categoria</label>
            <select id="category" name="category" class="field-input">
                <option value="">Todas</option>
                @foreach (\App\Models\Note::CATEGORIES as $key => $label)
                    <option value="{{ $key }}" @selected($filters['category'] === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="sort" class="mb-2 block text-sm font-medium text-slate-700">Orden</label>
            <select id="sort" name="sort" class="field-input">
                <option value="recent" @selected($filters['sort'] === 'recent')>Mas recientes</option>
                <option value="top" @selected($filters['sort'] === 'top')>Mas votadas</option>
            </select>
        </div>
        <div class="flex flex-wrap gap-3 md:col-span-3">
            <button type="submit" class="btn-secondary">Aplicar filtros</button>
            <a href="{{ route('rooms.show', $room) }}" class="btn-secondary">Limpiar</a>
        </div>
    </form>
</section>
