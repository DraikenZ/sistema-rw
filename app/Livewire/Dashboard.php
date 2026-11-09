<?php

namespace App\Livewire;

use App\Models\Reservation;
use App\Models\Room;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.hotel')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        $today = today()->toDateString();

        // Habitaciones y su estado de hoy
        $rooms = Room::withStayOn($today)
            ->with('building')
            ->orderBy('building_id')
            ->orderBy('id')
            ->get();

        $statuses = $rooms->mapWithKeys(fn ($r) => [$r->id => $r->boardStatus()]);

        $counts = array_merge(
            ['libre' => 0, 'reservada' => 0, 'ocupada' => 0, 'limpieza' => 0, 'mantencion' => 0],
            $statuses->countBy()->all()
        );

        $totalRooms = $rooms->count();
        $occupancy = $totalRooms ? (int) round($counts['ocupada'] / $totalRooms * 100) : 0;

        $byBuilding = $rooms->groupBy('building_id')->map(fn ($group) => [
            'total' => $group->count(),
            'occupied' => $group->filter(fn ($r) => $statuses[$r->id] === 'ocupada')->count(),
        ]);

        // Movimientos del día
        $relations = [
            'holder',
            'rooms.building',
            'payments',
            'serviceBookings' => fn ($s) => $s->whereDate('date', $today)
                ->where('status', '!=', 'cancelado')
                ->orderBy('start_time')
                ->with('service'),
        ];

        $arrivals = Reservation::with($relations)
            ->whereDate('check_in_date', $today)
            ->whereIn('status', ['reservada', 'en_estadia'])
            ->get();

        $departures = Reservation::with($relations)
            ->whereDate('check_out_date', $today)
            ->whereIn('status', ['en_estadia', 'finalizada'])
            ->get();

        $movements = $arrivals
            ->map(fn ($r) => ['type' => 'checkin', 'time' => $r->arrival_time, 'done' => $r->status === 'en_estadia', 'reservation' => $r])
            ->concat($departures->map(fn ($r) => ['type' => 'checkout', 'time' => $r->departure_time, 'done' => $r->status === 'finalizada', 'reservation' => $r]))
            ->sortBy(fn ($m) => $m['time'] ?? '99:99')
            ->values();

        // Indicadores de las tarjetas
        $pendingArrivals = $arrivals->where('status', 'reservada');
        $pendingDepartures = $departures->where('status', 'en_estadia');

        $hour = now()->hour;
        $greeting = $hour < 12 ? '¡Buenos días' : ($hour < 20 ? '¡Buenas tardes' : '¡Buenas noches');

        return view('livewire.dashboard', [
            'greeting' => $greeting,
            'firstName' => explode(' ', trim(Auth::user()->name))[0],
            'dateLabel' => today()->translatedFormat('l, d \d\e F Y'),
            'rooms' => $rooms,
            'statuses' => $statuses,
            'counts' => $counts,
            'totalRooms' => $totalRooms,
            'occupancy' => $occupancy,
            'byBuilding' => $byBuilding,
            'roomsByBuilding' => $rooms->groupBy('building_id'),
            'movements' => $movements,
            'arrivalsCount' => $arrivals->count(),
            'pendingArrivalsCount' => $pendingArrivals->count(),
            'firstArrival' => $pendingArrivals->pluck('arrival_time')->filter()->sort()->first(),
            'departuresCount' => $departures->count(),
            'departuresWithBalance' => $departures->filter(fn ($r) => $r->total_amount !== null && $r->total_paid < $r->total_amount)->count(),
            'firstDeparture' => $pendingDepartures->pluck('departure_time')->filter()->sort()->first(),
            'guestsInHotel' => (int) Reservation::where('status', 'en_estadia')->sum('people_count'),
        ]);
    }
}