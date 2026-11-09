@php
    $dot = ['libre' => 'bg-emerald-500', 'reservada' => 'bg-blue-700', 'ocupada' => 'bg-red-600', 'limpieza' => 'bg-amber-500', 'mantencion' => 'bg-slate-500'];
    $label = ['libre' => 'Disponible', 'reservada' => 'Reservada', 'ocupada' => 'Ocupada', 'limpieza' => 'Limpieza', 'mantencion' => 'Mantención'];
    $money = fn ($n) => '$' . number_format($n, 0, ',', '.');
    $circ = 175.93;
    $dash = round($occupancy / 100 * $circ, 1);
@endphp

<div class="space-y-6 p-6">

    {{-- Encabezado --}}
    <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Mesa de operaciones</p>
        <h1 class="text-3xl font-bold text-slate-900">{{ $greeting }}, {{ $firstName }}!</h1>
        <p class="text-sm capitalize text-slate-500">{{ $dateLabel }} · resumen operativo de hoy</p>
    </div>

    {{-- Tarjetas --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Check-in hoy</p>
            <p class="mt-2 flex items-baseline gap-2">
                <span class="text-4xl font-bold text-slate-900">{{ $arrivalsCount }}</span>
                <span class="text-slate-500">llegadas</span>
                @if ($pendingArrivalsCount)
                    <span class="rounded bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800">{{ $pendingArrivalsCount }} pendientes</span>
                @endif
            </p>
            <p class="mt-3 text-xs text-slate-500">
                {{ $firstArrival ? 'Primer arribo: ' . substr($firstArrival, 0, 5) . ' hrs' : 'Sin llegadas pendientes' }}
            </p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Check-out hoy</p>
            <p class="mt-2 flex items-baseline gap-2">
                <span class="text-4xl font-bold text-slate-900">{{ $departuresCount }}</span>
                <span class="text-slate-500">salidas</span>
                @if ($departuresWithBalance)
                    <span class="rounded bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">{{ $departuresWithBalance }} con saldo</span>
                @endif
            </p>
            <p class="mt-3 text-xs text-slate-500">
                {{ $firstDeparture ? 'Próxima salida: ' . substr($firstDeparture, 0, 5) . ' hrs' : 'Sin salidas pendientes' }}
            </p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Huéspedes en hotel</p>
            <p class="mt-2 flex items-baseline gap-2">
                <span class="text-4xl font-bold text-slate-900">{{ $guestsInHotel }}</span>
                <span class="text-slate-500">alojados</span>
            </p>
            <p class="mt-3 text-xs text-slate-500">
                Habitaciones ocupadas:
                @foreach ($byBuilding as $buildingId => $b)
                    {{ substr($roomsByBuilding[$buildingId]->first()->building->name, -1) }} {{ $b['occupied'] }}{{ ! $loop->last ? ' ·' : '' }}
                @endforeach
            </p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Tasa de ocupación</p>
            <div class="mt-2 flex items-center justify-between gap-3">
                <div>
                    <p class="text-4xl font-bold text-slate-900">{{ $occupancy }}%</p>
                    <p class="text-xs text-slate-500">{{ $counts['ocupada'] }} de {{ $totalRooms }} habitaciones</p>
                </div>
                <div class="relative h-16 w-16 shrink-0">
                    <svg viewBox="0 0 72 72" class="h-16 w-16 -rotate-90">
                        <circle cx="36" cy="36" r="28" fill="none" stroke="#e2e8f0" stroke-width="8" />
                        <circle cx="36" cy="36" r="28" fill="none" stroke="#059669" stroke-width="8"
                                stroke-linecap="round" stroke-dasharray="{{ $dash }} {{ $circ }}" />
                    </svg>
                    <span class="absolute inset-0 grid place-items-center text-xs font-bold text-slate-700">{{ $counts['ocupada'] }}/{{ $totalRooms }}</span>
                </div>
            </div>
            <p class="mt-3 text-xs text-slate-500">
                @foreach ($byBuilding as $buildingId => $b)
                    Edif. {{ substr($roomsByBuilding[$buildingId]->first()->building->name, -1) }}: {{ $b['occupied'] }}/{{ $b['total'] }}{{ ! $loop->last ? ' ·' : '' }}
                @endforeach
            </p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">

        {{-- Movimientos del día --}}
        <section class="rounded-xl border border-slate-200 bg-white xl:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-100 p-5">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Check-ins y Check-outs de hoy</h2>
                    <p class="text-xs text-slate-500">Control de llegadas, salidas y pagos pendientes</p>
                </div>
                <span class="rounded bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $movements->count() }} movimientos</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Huésped titular</th>
                            <th class="px-3 py-3 font-semibold">Habitación y pax</th>
                            <th class="px-3 py-3 font-semibold">Horario</th>
                            <th class="px-3 py-3 font-semibold">Servicios</th>
                            <th class="px-3 py-3 font-semibold">Pago</th>
                            <th class="px-5 py-3 font-semibold">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($movements as $m)
                            @php $r = $m['reservation']; @endphp
                            <tr>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-slate-900 text-xs font-bold text-white">
                                            {{ collect(explode(' ', $r->holder->full_name))->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->join('') }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $r->holder->full_name }}</p>
                                            @if ($r->holder->document_number)
                                                <p class="text-xs text-slate-500">{{ strtoupper($r->holder->document_type) }} {{ $r->holder->document_number }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    @foreach ($r->rooms as $room)
                                        <p class="text-slate-800">
                                            {{ substr($room->building->name, -1) }} ·
                                            {{ $room->building->naming_type === 'numero' ? 'Hab. ' : '' }}{{ $room->name }}
                                        </p>
                                    @endforeach
                                    <p class="text-xs text-slate-500">{{ $r->people_count }} personas</p>
                                </td>
                                <td class="px-3 py-3">
                                    <p class="font-semibold text-slate-900">{{ $m['time'] ? substr($m['time'], 0, 5) . ' hrs' : 'Sin hora' }}</p>
                                    <p class="text-xs {{ $m['type'] === 'checkin' ? 'text-emerald-700' : 'text-amber-700' }}">
                                        {{ $m['type'] === 'checkin' ? 'Check-in' : 'Check-out' }}
                                    </p>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse ($r->serviceBookings as $sb)
                                            <span class="rounded bg-slate-100 px-2 py-1 text-xs text-slate-700">
                                                {{ $sb->service->name }}@if ($sb->start_time) {{ substr($sb->start_time, 0, 5) }}@endif
                                            </span>
                                        @empty
                                            <span class="text-xs text-slate-400">—</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    @if ($r->total_amount !== null)
                                        <p class="font-semibold text-slate-900">{{ $money($r->total_paid) }}</p>
                                        @if ($r->total_paid >= $r->total_amount)
                                            <p class="text-xs text-emerald-700">Pagado</p>
                                        @else
                                            <p class="text-xs text-red-600">Pendiente {{ $money($r->total_amount - $r->total_paid) }}</p>
                                        @endif
                                    @else
                                        <span class="text-xs text-slate-400">Sin monto</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $m['done'] ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $m['done'] ? 'Realizado' : 'Pendiente' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-slate-500">No hay movimientos programados para hoy.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Resumen del semáforo --}}
        <section class="rounded-xl border border-slate-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-900">Semáforo de habitaciones</h2>
                <a href="{{ route('rooms.board') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-900">Ver matriz completa →</a>
            </div>

            <div class="mt-4 flex flex-wrap gap-x-4 gap-y-1 rounded-lg bg-slate-50 p-3 text-xs text-slate-700">
                @foreach ($label as $key => $text)
                    <span class="flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full {{ $dot[$key] }}"></span>{{ $text }} ({{ $counts[$key] }})
                    </span>
                @endforeach
            </div>

            @foreach ($roomsByBuilding as $buildingId => $group)
                <div class="mt-5">
                    <div class="mb-2 flex items-center justify-between text-xs">
                        <span class="font-semibold text-slate-700">{{ $group->first()->building->name }}</span>
                        <span class="text-slate-500">{{ $group->count() }} unidades</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach ($group as $room)
                            @php
                                $st = $statuses[$room->id];
                                $res = $room->reservations->first();
                                $note = match ($st) {
                                    'ocupada' => \Illuminate\Support\Str::limit($res?->holder->full_name ?? 'En estadía', 16),
                                    'reservada' => 'Llega ' . ($res?->arrival_time ? substr($res->arrival_time, 0, 5) : ''),
                                    'limpieza' => 'Housekeeping',
                                    'mantencion' => 'Fuera de servicio',
                                    default => 'Lista para asignar',
                                };
                            @endphp
                            <div class="rounded-lg bg-slate-50 p-2.5 text-xs">
                                <div class="flex items-center justify-between gap-1">
                                    <span class="font-semibold text-slate-900">
                                        {{ $room->building->naming_type === 'numero' ? 'Hab. ' : '' }}{{ $room->name }}
                                    </span>
                                    <span class="h-2 w-2 shrink-0 rounded-full {{ $dot[$st] }}"></span>
                                </div>
                                <p class="mt-0.5 text-slate-500">{{ $note }}</p>
                                @if ($res && in_array($st, ['ocupada', 'reservada']))
                                    <p class="text-slate-500">Pax: {{ $res->people_count }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </section>
    </div>
</div>