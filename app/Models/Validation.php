<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Validation extends Model
{
    protected $fillable = [
        'game_id',
        'place_id',
        'latitude',
        'longitude',
        'distance_metres',
        'bonne_reponse',
        'date_validation',
    ];

    protected function casts(): array
    {
        return [
            'date_validation' => 'datetime',
            'bonne_reponse' => 'boolean',
        ];
    }
}
