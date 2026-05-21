<?php

namespace Database\Seeders;

use App\Models\Place;
use App\Models\Environment;
use Illuminate\Database\Seeder;

class PlaceSeeder extends Seeder
{
    public function run(): void
    {
        $places = [

            /*
            |--------------------------------------------------------------------------
            | COTONOU
            |--------------------------------------------------------------------------
            */

            [
                'environment' => 'Cotonou',

                'nom' => 'Place de l’Amazone',

                'description' => 'Monument emblématique dédié aux amazones du Dahomey',

                'latitude' => 6.3654,

                'longitude' => 2.4183,

                'popularite' => 5,
            ],

            [
                'environment' => 'Cotonou',

                'nom' => 'Marché Dantokpa',

                'description' => 'Le plus grand marché d’Afrique de l’Ouest',

                'latitude' => 6.3700,

                'longitude' => 2.4310,

                'popularite' => 5,
            ],

            [
                'environment' => 'Cotonou',

                'nom' => 'Route des Pêches',

                'description' => 'Zone côtière touristique',

                'latitude' => 6.3490,

                'longitude' => 2.3030,

                'popularite' => 4,
            ],

            /*
            |--------------------------------------------------------------------------
            | OUIDAH
            |--------------------------------------------------------------------------
            */

            [
                'environment' => 'Ouidah',

                'nom' => 'Porte du Non Retour',

                'description' => 'Monument historique de la traite négrière',

                'latitude' => 6.3628,

                'longitude' => 2.0851,

                'popularite' => 5,
            ],

            [
                'environment' => 'Ouidah',

                'nom' => 'Temple des Pythons',

                'description' => 'Lieu sacré du culte vodoun',

                'latitude' => 6.3637,

                'longitude' => 2.0855,

                'popularite' => 5,
            ],

            /*
            |--------------------------------------------------------------------------
            | ABOMEY
            |--------------------------------------------------------------------------
            */

            [
                'environment' => 'Abomey',

                'nom' => 'Palais Royaux d’Abomey',

                'description' => 'Patrimoine mondial de l’UNESCO',

                'latitude' => 7.1828,

                'longitude' => 1.9912,

                'popularite' => 5,
            ],
        ];

        foreach ($places as $data) {

            $environment = Environment::where(
                'nom',
                $data['environment']
            )->first();

            Place::create([

                'environment_id' => $environment->id,

                'nom' => $data['nom'],

                'description' => $data['description'],

                'latitude' => $data['latitude'],

                'longitude' => $data['longitude'],

                'rayon_validation' => 50,

                'ordre' => 0,

                'popularite' => $data['popularite'],

                'is_active' => true,
            ]);
        }
    }
}