<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceBooking extends Model
{
    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // ¿Ya está ocupado ese servicio en ese horario? (quincho, tinaja, pool, masaje)
    public static function hasConflict(int $serviceId, string $date, string $start, string $end, ?int $ignoreId = null): bool
    {
        $service = Service::findOrFail($serviceId);

        if (! $service->requires_schedule || $service->max_simultaneous === null) {
            return false;
        }

        $overlapping = static::where('service_id', $serviceId)
            ->where('date', $date)
            ->where('status', '!=', 'cancelado')
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->count();

        return $overlapping >= $service->max_simultaneous;
    }
}