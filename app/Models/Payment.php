<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'paid_on' => 'date',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}