<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $guarded = [];

    protected $casts = [
        'requires_schedule' => 'boolean',
        'active' => 'boolean',
    ];

    public function bookings()
    {
        return $this->hasMany(ServiceBooking::class);
    }
}