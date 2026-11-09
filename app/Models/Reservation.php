<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $guarded = [];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
    ];

    // Quien reserva
    public function holder()
    {
        return $this->belongsTo(Guest::class, 'guest_id');
    }

    // Grupo familiar o amigos
    public function guests()
    {
        return $this->belongsToMany(Guest::class, 'reservation_guest');
    }

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'reservation_room')
            ->withPivot('extra_bed')
            ->withTimestamps();
    }

    public function serviceBookings()
    {
        return $this->hasMany(ServiceBooking::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getNightsAttribute(): int
    {
        return (int) $this->check_in_date->diffInDays($this->check_out_date);
    }

    public function getTotalPaidAttribute(): int
    {
        return (int) $this->payments->sum('amount');
    }
}