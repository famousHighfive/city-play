<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlannedEvent extends Model
{
    protected $fillable = [
        'nom',
        'description',
        'environment_id',
        'date_evenement',
        'image',
        'points_xp_bonus',
        'actif',
    ];

    protected $casts = [
        'date_evenement' => 'datetime',
        'actif' => 'boolean',
    ];

    public function environment()
    {
        return $this->belongsTo(Environment::class);
    }
}
