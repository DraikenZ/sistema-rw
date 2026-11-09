@php
    $ui = [
        'libre'      => ['label' => 'Disponible',    'hint' => 'Habitación libre para asignar', 'top' => 'border-t-emerald-600', 'bar' => 'border-l-emerald-600', 'text' => 'text-emerald-700', 'badge' => 'bg-emerald-600 text-white'],
        'reservada'  => ['label' => 'Reservada',     'hint' => 'Llegada próxima confirmada',    'top' => 'border-t-blue-700',    'bar' => 'border-l-blue-700',    'text' => 'text-blue-700',    'badge' => 'bg-blue-700 text-white'],
        'ocupada'    => ['label' => 'Ocupada',       'hint' => 'Huéspedes en estadía activa',   'top' => 'border-t-red-600',     'bar' => 'border-l-red-600',     'text' => 'text-red-700',     'badge' => 'bg-red-600 text-white'],
        'limpieza'   => ['label' => 'En limpieza',   'hint' => 'Housekeeping en preparación',   'top' => 'border-t-amber-500',   'bar' => 'border-l-amber-500',   'text' => 'text-amber-700',   'badge' => 'bg-amber-500 text-white'],
        'mantencion' => ['label' => 'Mantenimiento', 'hint' => 'Fuera de servicio temporal',    'top' => 'border-t-slate-500',   'bar' => 'border-l-slate-500',   'text' => 'text-slate-600',   'badge' => 'bg-slate-600 text-white'],
    ];
    $types = ['matrimonial', 'premium', 'individual', 'doble', 'triple'];
@endphp

<div class="space-y-6 p-6">

    {{-- Encabezado y fecha --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Semáforo operativo</p>
            <h1 class="text-3xl font-bold text-slate-900">Semáforo de habitaciones</h1>
            <p class="text-sm capitalize text-slate-500">{{ $dateLabel }} · {{ $all->count() }} unidades monitoreadas</p>
        </div>
        <div class="flex items-center gap-1 rounded-lg border border-slate-200 bg-white p-1.5">
            <button wire:click="previousDay" class="rounded px-3 py-1.5 text-slate-600 hover:bg-slate-100">‹</button>
            <input type="date" wire:model.live="date" class="rounded border-0 text-sm focus:ring-0">
            <button wire:click="nextDay" class="rounded px-3 py-1.5 text-slate-600 hover:bg-slate-100">›</button>
            <button wire:click="goToday" class="rounded bg-slate-100 px-3 py-1.5 text-sm font-semibold hover:bg-slate-200">Hoy</button>
        </div>
    </div>

    {{-- Tarjetas de resumen --}}
    <div class="grid grid-cols-2 gap-4 md:grid-cols-5">
        @foreach ($ui as $key => $s)
            <div class="rounded-lg border border-slate-200 border-l-4 bg-white p-4 {{ $s['bar'] }}">
                <p class="text-xs font-bold uppercase {{ $s['text'] }}">{{ $s['label'] }}</p>
                <p class="text-3xl font-bold text-slate-900">{{ $counts[$key] }}</p>
                <p class="text-sm text-slate-500">{{ $s['hint'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Filtros --}}
    <div class="flex flex-wrap items-center gap-3 rounded-lg border border-slate-200 bg-white p-3">
        <div class="flex rounded-md bg-slate-100 p-1 text-sm">
            <button wire:click="$set('building', '')"
                    class="rounded px-3 py-1 {{ $building === '' ? 'bg-white font-semibold shadow-sm' : 'text-slate-600' }}">Todos</button>
            @foreach ($buildings as $b)
                <button wire:click="$set('building', '{{ $b->id }}')"
                        class="rounded px-3 py-1 {{ $building === (string) $b->id ? 'bg-white font-semibold shadow-sm' : 'text-slate-600' }}">{{ $b->name }}</button>
            @endforeach
        </div>

        <select wire:model.live="type" class="rounded-md border-slate-300 text-sm">
            <option value="">Todos los tipos</option>
            @foreach ($types as $t)
                <option value="{{ $t }}">{{ ucfirst($t) }}</option>
            @endforeach
        </select>

        <select wire:model.live="bathroom" class="rounded-md border-slate-300 text-sm">
            <option value="">Todos los baños</option>
            <option value="individual">Baño individual</option>
            <option value="compartido">Baño compartido</option>
        </select>

        <button wire:click="clearFilters" class="ml-auto text-sm font-semibold text-slate-500 hover:text-slate-900">Limpiar filtros</button>
    </div>

    {{-- Edificios y habitaciones --}}
    @forelse ($buildings as $b)
        @php $group = $visible->get($b->id); @endphp
        @continue(! $group)

        <section class="space-y-3">
            <div class="flex items-center justify-between rounded-lg bg-slate-100 px-4 py-3">
                <div class="flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-lg bg-slate-900 font-bold text-white">{{ strtoupper(substr($b->name, -1)) }}</span>
                    <div>
                        <h2 class="font-semibold text-slate-900">{{ $b->name }}</h2>
                        <p class="text-xs text-slate-500">{{ $b->naming_type === 'nombre' ? 'Habitaciones con nombre' : 'Habitaciones numeradas' }}</p>
                    </div>
                </div>
                <p class="text-sm text-slate-600">Ocupación: <strong>{{ $occupancy[$b->id] }}%</strong></p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($group as $room)
                    @php
                        $st = $statuses[$room->id];
                        $s = $ui[$st];
                        $res = $room->reservations->first();
                    @endphp

                    <article class="flex flex-col rounded-xl border border-slate-200 border-t-4 bg-white shadow-sm {{ $s['top'] }}">
                        <div class="flex-1 space-y-3 p-4">

                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900">
                                        {{ $b->naming_type === 'numero' ? 'Habitación ' : '' }}{{ $room->name }}
                                    </h3>
                                    <p class="text-xs uppercase text-slate-500">{{ $room->type }} · {{ $b->name }}</p>
                                </div>
                                <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $s['badge'] }}">{{ $s['label'] }}</span>
                            </div>

                            <div class="flex flex-wrap gap-1.5 text-xs">
                                <span class="rounded bg-slate-100 px-2 py-1">Baño {{ $room->bathroom }}</span>
                                <span class="rounded bg-slate-100 px-2 py-1">Capacidad {{ $room->capacity }}</span>
                                @if ($room->allows_extra_bed)
                                    <span class="rounded bg-slate-100 px-2 py-1">Cama adicional</span>
                                @endif
                            </div>

                            {{-- Bloque central según estado --}}
                            @if ($res && in_array($st, ['ocupada', 'reservada']))
                                <div class="space-y-2 rounded-lg bg-slate-50 p-3">
                                    <div class="flex items-center gap-3">
                                        <div class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-slate-900 text-xs font-bold text-white">
                                            {{ collect(explode(' ', $res->holder->full_name))->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->join('') }}
                                        </div>
                                        <div class="text-sm">
                                            <p class="font-semibold text-slate-900">{{ $res->holder->full_name }}</p>
                                            <p class="text-xs text-slate-500">
                                                @if ($st === 'ocupada')
                                                    {{ $res->nights }} noches · Salida {{ $res->check_out_date->translatedFormat('d M') }}
                                                @else
                                                    Check-in {{ $res->check_in_date->translatedFormat('d M') }}@if ($res->arrival_time) · {{ substr($res->arrival_time, 0, 5) }} hrs @endif
                                                @endif
                                                · {{ $res->people_count }} pers.
                                            </p>
                                        </div>
                                    </div>
                                    @if ($res->holder->tags->isNotEmpty())
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($res->holder->tags as $tag)
                                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] text-amber-800">{{ $tag->name }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @elseif ($st === 'limpieza')
                                <div class="flex items-center justify-between rounded-lg bg-amber-50 p-3 text-sm text-amber-900">
                                    <span>Pendiente de limpieza</span>
                                    <button wire:click="markClean({{ $room->id }})"
                                            class="rounded bg-amber-500 px-2.5 py-1 text-xs font-bold text-white hover:bg-amber-600">Marcar limpia</button>
                                </div>
                            @elseif ($st === 'mantencion')
                                <div class="rounded-lg bg-slate-100 p-3 text-sm text-slate-700">
                                    {{ $room->notes ?: 'Fuera de servicio' }}
                                </div>
                            @else
                                <div class="rounded-lg bg-emerald-50 p-3 text-sm text-emerald-800">Sin huésped asignado</div>
                            @endif
                        </div>

                        {{-- Servicios del día --}}
                        <div class="border-t border-slate-100 px-4 py-3 text-xs text-slate-600">
                            @if ($res && $res->serviceBookings->isNotEmpty())
                                @foreach ($res->serviceBookings as $sb)
                                    <span class="mr-3 inline-block">{{ $sb->service->name }}@if ($sb->start_time) {{ substr($sb->start_time, 0, 5) }}@endif</span>
                                @endforeach
                            @else
                                Sin servicios para este día
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @empty
        <p class="text-slate-500">No hay edificios registrados.</p>
    @endforelse

    @if ($visible->isEmpty())
        <p class="rounded-lg border border-dashed border-slate-300 p-8 text-center text-slate-500">
            No hay habitaciones con estos filtros.
        </p>
    @endif
</div>