<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Environment extends Model
{
    protected $fillable = [
        'nom',
        'description',
        'actif',
    ];

    // un environnement a plusieurs lieux
    public function places()
    {
        return $this->hasMany(Place::class);           
    } 
}
