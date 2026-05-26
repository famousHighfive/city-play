<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    protected $fillable = [
        'environment_id',
        'nom',
        'description',
        'recommandation',
        'ressource',
        'latitude',
        'longitude',
        'rayon_validation',
        'ordre',
        'popularite',
        'is_active',
        'image_principale',
    ];

    protected function casts(): array
    {
        return [
            'recommandation' => 'array',
        ];
    }

    public function environment()
    {
        return $this->belongsTo(Environment::class);
    }

    public function enigmes()
    {
        return $this->hasMany(Enigme::class);
    }
}