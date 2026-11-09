<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    protected $guarded = [];

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'guest_tag');
    }

    // Reservas donde esta persona es quien reserva
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}