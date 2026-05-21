<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = [
        'user_id',
        'environment_id',
        'date_debut',
        'date_fin',
        'statut',
        'mode_jeu',
        'nb_membres',
        'participants',
        'latitude',
        'longitude',
        'challenger_email',
        'duree_prevue',
        'duree_restante',
        'moyen_locomotion',
        'niveau_difficulte',
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
        'participants' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function environment()
    {
        return $this->belongsTo(Environment::class);
    }

    public function enigmes()
    {
        return $this->belongsToMany(Enigme::class, 'enigme_games')
            ->withPivot('ordre', 'statut', 'nb_indices_demandes', 'solution_affichee')
            ->withTimestamps();
    }
}
