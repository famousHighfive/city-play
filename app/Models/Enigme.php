<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enigme extends Model
{
    protected $fillable = [
        'place_id',
        'niveau',
        'texte',
        'image',
        'actif',
    ];

    public function place()
    {
        return $this->belongsTo(Place::class);
    }
}
