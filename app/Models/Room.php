<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Room extends Model
{
    protected $guarded = [];

    protected $casts = [
        'allows_extra_bed' => 'boolean',
        'out_of_service' => 'boolean',
        'needs_cleaning' => 'boolean',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function reservations()
    {
        return $this->belongsToMany(Reservation::class, 'reservation_room')
            ->withPivot('extra_bed')
            ->withTimestamps();
    }

    public function getFullNameAttribute(): string
    {
        return $this->building->name . ' - ' . $this->name;
    }

    // Habitaciones libres entre dos fechas (el día de salida se puede reutilizar)
    public function scopeAvailable($query, $checkIn, $checkOut)
    {
        return $query->where('out_of_service', false)
            ->whereDoesntHave('reservations', function ($q) use ($checkIn, $checkOut) {
                $q->whereIn('reservations.status', ['reservada', 'en_estadia'])
                  ->where('reservations.check_in_date', '<', $checkOut)
                  ->where('reservations.check_out_date', '>', $checkIn);
            });
    }

    // Estado para el semáforo, usando la reserva ya cargada (evita consultas repetidas)
    // libre | reservada | ocupada | limpieza | mantencion
    public function boardStatus(bool $showCleaning = true): string
    {
        if ($this->out_of_service) {
            return 'mantencion';
        }

        $reservation = $this->reservations->first();

        if ($reservation?->status === 'en_estadia') {
            return 'ocupada';
        }

        if ($showCleaning && $this->needs_cleaning) {
            return 'limpieza';
        }

        return $reservation ? 'reservada' : 'libre';
    }

        // Reserva que afecta a la habitación en una fecha (con huésped y servicios del día)
    public function scopeWithStayOn($query, string $date)
    {
        return $query->with(['reservations' => function ($q) use ($date) {
            $q->where(function ($w) use ($date) {
                $w->where(fn ($r) => $r->where('reservations.status', 'reservada')
                        ->where('reservations.check_in_date', '<=', $date)
                        ->where('reservations.check_out_date', '>', $date))
                ->orWhere(fn ($r) => $r->where('reservations.status', 'en_estadia')
                        ->where('reservations.check_in_date', '<=', $date)
                        ->where('reservations.check_out_date', '>=', $date));
            })->with([
                'holder.tags',
                'serviceBookings' => fn ($s) => $s->whereDate('date', $date)
                    ->where('status', '!=', 'cancelado')
                    ->orderBy('start_time')
                    ->with('service'),
            ]);
        }]);
    }
}