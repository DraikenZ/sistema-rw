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

    // Estado para el semáforo: libre | reservada | ocupada | alerta
    public function statusOn($date = null): string
    {
        $date = Carbon::parse($date ?? today())->toDateString();

        if ($this->out_of_service) {
            return 'alerta';
        }

        $reservation = $this->reservations()
            ->whereIn('reservations.status', ['reservada', 'en_estadia'])
            ->where('reservations.check_in_date', '<=', $date)
            ->where('reservations.check_out_date', '>', $date)
            ->first();

        if (! $reservation) {
            return 'libre';
        }

        return $reservation->status === 'en_estadia' ? 'ocupada' : 'reservada';
    }
}