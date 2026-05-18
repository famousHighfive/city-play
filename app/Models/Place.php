<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    protected $fillable = [
        'environment_id',
        'nom',
        'latitude',
        'longitude',
        'description',
        'rayon_validation',
    ];

    // un lieu appartient à un environnement
     public function environment()
    {
        return $this->belongsTo(Environment::class);
    }

    // un lieu peut avoir plusieurs énigmes
    public function enigmes()
    {
        return $this->hasMany(Enigme::class);   
    }
}
