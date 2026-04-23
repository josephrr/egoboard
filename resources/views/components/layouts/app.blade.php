<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? 'Muro de notas' }}</title>
        <meta name="description" content="{{ $description ?? 'Salas publicas para que tus estudiantes dejen notas sin iniciar sesion.' }}">
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=sora:400,500,600,700,800|space-grotesk:500,700" rel="stylesheet" />
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
            @stack('page_vite')
        @endif
    </head>
    <body>
        <div class="relative min-h-screen overflow-hidden">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-72 bg-[radial-gradient(circle_at_top,rgba(251,146,60,0.24),transparent_60%)]"></div>
            {{ $slot }}
            <footer class="px-4 pb-8 text-center text-sm text-slate-500 sm:px-6 lg:px-8">
                Hecho por <a href="https://egobytes.com" target="_blank" rel="noopener noreferrer" class="font-semibold text-slate-700 transition hover:text-orange-600">egobytes</a>
            </footer>
        </div>
    </body>
</html>
