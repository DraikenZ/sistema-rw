<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $guarded = [];

    public function guests()
    {
        return $this->belongsToMany(Guest::class, 'guest_tag');
    }
}