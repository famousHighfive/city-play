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
            | NIVEAU ENFANT
            |--------------------------------------------------------------------------
            */

            Enigme::create([
                'place_id' => $place->id,
                'niveau' => 'kid',
                'texte' => "Je suis un endroit très connu où beaucoup de personnes aiment visiter et prendre des photos.",
                'solution' => $place->nom,
                'indice_1' => "Je suis célèbre dans ma ville.",
                'indice_2' => "Regarde autour des lieux touristiques.",
                'actif' => true,
            ]);

            /*
            |--------------------------------------------------------------------------
            | NIVEAU EASY
            |--------------------------------------------------------------------------
            */

            Enigme::create([
                'place_id' => $place->id,
                'niveau' => 'easy',
                'texte' => "Entre histoire, culture ou loisirs, je fais partie des lieux que les visiteurs aiment découvrir.",
                'solution' => $place->nom,
                'indice_1' => "Je suis populaire dans cette ville.",
                'indice_2' => "Mon nom apparaît souvent dans les guides touristiques.",
                'actif' => true,
            ]);

            /*
            |--------------------------------------------------------------------------
            | NIVEAU MEDIUM
            |--------------------------------------------------------------------------
            */

            Enigme::create([
                'place_id' => $place->id,
                'niveau' => 'medium',
                'texte' => "Je raconte une partie de l’identité de ma ville. Certains viennent pour apprendre, d’autres simplement admirer.",
                'solution' => $place->nom,
                'indice_1' => "Mon importance dépasse souvent le simple tourisme.",
                'indice_2' => "Je suis connu des habitants comme des visiteurs.",
                'actif' => true,
            ]);

            /*
            |--------------------------------------------------------------------------
            | NIVEAU HARD
            |--------------------------------------------------------------------------
            */

            Enigme::create([
                'place_id' => $place->id,
                'niveau' => 'hard',
                'texte' => "Je suis le témoin silencieux d’histoires humaines, de traditions ou d’échanges qui ont marqué ma région.",
                'solution' => $place->nom,
                'indice_1' => "Mon passé ou mon activité attire beaucoup de monde.",
                'indice_2' => "On peut me retrouver dans les lieux emblématiques de la ville.",
                'actif' => true,
            ]);
        }
    }
}