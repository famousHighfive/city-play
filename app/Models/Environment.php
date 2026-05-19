<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }
}
