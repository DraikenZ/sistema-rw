<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Panel' }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">
<div class="flex min-h-screen">

    <aside class="hidden w-60 shrink-0 flex-col border-r border-slate-200 bg-white p-4 md:flex">
        <div class="mb-6 px-2">
            <p class="text-lg font-bold text-slate-900">Rock Water</p>
            <p class="text-xs uppercase tracking-wider text-slate-500">Hotel &amp; Lodges</p>
        </div>
        <nav class="space-y-1 text-sm">
            @foreach ([['Dashboard', 'dashboard'], ['Semáforo de habitaciones', 'rooms.board']] as [$label, $route])
                <a href="{{ route($route) }}"
                    class="flex items-center rounded-lg px-3 py-2 {{ request()->routeIs($route) ? 'bg-slate-900 font-semibold text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                {{ $label }}
                </a>
            @endforeach
            @foreach (['Check-in y Check-out', 'Servicios y Reservas', 'Huéspedes'] as $item)
                <span class="flex items-center justify-between rounded-lg px-3 py-2 text-slate-400">
                    {{ $item }}
                    <span class="text-[10px] uppercase">Pronto</span>
                </span>
            @endforeach
        </nav>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="flex items-center justify-between border-b border-slate-200 bg-white px-6 py-3">
            <span class="text-sm capitalize text-slate-500">{{ now()->translatedFormat('l d \d\e F') }}</span>
            <div class="flex items-center gap-4 text-sm">
                <span class="font-semibold text-slate-900">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('hotel.logout') }}">
                    @csrf
                    <button type="submit" class="text-slate-500 hover:text-slate-900">Salir</button>
                </form>
            </div>
        </header>

        <main class="flex-1">
            {{ $slot }}
        </main>
    </div>
</div>
@livewireScripts
</body>
</html>