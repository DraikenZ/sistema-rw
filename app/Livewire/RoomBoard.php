<?php

namespace App\Livewire;

use App\Models\Building;
use App\Models\Room;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.hotel')]
#[Title('Semáforo de habitaciones')]
class RoomBoard extends Component
{
    #[Url] public string $date = '';
    #[Url] public string $building = '';  // '' = todos
    #[Url] public string $type = '';
    #[Url] public string $bathroom = '';

    public function mount(): void
    {
        $this->date = $this->date ?: today()->toDateString();
    }

    public function previousDay(): void
    {
        $this->date = Carbon::parse($this->date)->subDay()->toDateString();
    }

    public function nextDay(): void
    {
        $this->date = Carbon::parse($this->date)->addDay()->toDateString();
    }

    public function goToday(): void
    {
        $this->date = today()->toDateString();
    }

    public function clearFilters(): void
    {
        $this->reset(['building', 'type', 'bathroom']);
    }

    public function markClean(int $roomId): void
    {
        Room::findOrFail($roomId)->update(['needs_cleaning' => false]);
    }

    public function render()
    {
        $date = Carbon::parse($this->date)->toDateString();
        $isToday = $date === today()->toDateString();

            $all = Room::withStayOn($date)
        ->with('building')
        ->orderBy('building_id')
        ->orderBy('id')
        ->get();
        
        $statuses = $all->mapWithKeys(fn ($r) => [$r->id => $r->boardStatus($isToday)]);

        $counts = array_merge(
            ['libre' => 0, 'reservada' => 0, 'ocupada' => 0, 'limpieza' => 0, 'mantencion' => 0],
            $statuses->countBy()->all()
        );

        // Ocupación por edificio: habitaciones ocupadas o reservadas / total
        $occupancy = $all->groupBy('building_id')->map(function ($rooms) use ($statuses) {
            $busy = $rooms->filter(fn ($r) => in_array($statuses[$r->id], ['ocupada', 'reservada']))->count();
            return (int) round($busy / max($rooms->count(), 1) * 100);
        });

        $visible = $all->filter(fn ($r) =>
            (! $this->building || (string) $r->building_id === $this->building)
            && (! $this->type || $r->type === $this->type)
            && (! $this->bathroom || $r->bathroom === $this->bathroom)
        )->groupBy('building_id');

        return view('livewire.room-board', [
            'all' => $all,
            'statuses' => $statuses,
            'counts' => $counts,
            'occupancy' => $occupancy,
            'visible' => $visible,
            'buildings' => Building::orderBy('id')->get(),
            'dateLabel' => Carbon::parse($date)->translatedFormat('l, d F Y'),
        ]);
    }
}