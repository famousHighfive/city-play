<?php

namespace Database\Seeders;

use App\Models\Place;
use App\Models\Enigme;
use Illuminate\Database\Seeder;

class EnigmeSeeder extends Seeder
{
    public function run(): void
    {
        $places = Place::all();

        foreach ($places as $place) {

            /*
            |--------------------------------------------------------------------------
            | ENFANT
            |--------------------------------------------------------------------------
            */

            Enigme::create([

                'place_id' => $place->id,

                'niveau' => 'kid',

                'texte' => "Je suis un lieu célèbre de {$place->nom}. Trouve-moi !",

                'solution' => $place->nom,

                'indice_1' => "Je suis très connu dans cette ville.",

                'indice_2' => "Observe les monuments autour de toi.",

                'actif' => true,
            ]);

            /*
            |--------------------------------------------------------------------------
            | NIVEAU 1
            |--------------------------------------------------------------------------
            */

            Enigme::create([

                'place_id' => $place->id,

                'niveau' => 'easy',

                'texte' => "On vient souvent ici pour découvrir l’histoire et la culture. Quel est ce lieu ?",

                'solution' => $place->nom,

                'indice_1' => "C’est un lieu emblématique.",

                'indice_2' => "Les habitants le connaissent très bien.",

                'actif' => true,
            ]);

            /*
            |--------------------------------------------------------------------------
            | NIVEAU 2
            |--------------------------------------------------------------------------
            */

            Enigme::create([

                'place_id' => $place->id,

                'niveau' => 'medium',

                'texte' => "Je garde les souvenirs du passé et attire de nombreux visiteurs. Qui suis-je ?",

                'solution' => $place->nom,

                'indice_1' => "Mon importance est historique.",

                'indice_2' => "Cherche un lieu populaire.",

                'actif' => true,
            ]);

            /*
            |--------------------------------------------------------------------------
            | NIVEAU 3
            |--------------------------------------------------------------------------
            */

            Enigme::create([

                'place_id' => $place->id,

                'niveau' => 'hard',

                'texte' => "Entre mémoire, symbole et héritage, mon existence raconte une partie de l’âme béninoise.",

                'solution' => $place->nom,

                'indice_1' => "Je suis lié à l’histoire du pays.",

                'indice_2' => "Je suis un lieu emblématique.",

                'actif' => true,
            ]);
        }
    }
}