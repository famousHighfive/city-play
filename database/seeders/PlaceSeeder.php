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
                'nom' => 'Canal Olympia Wologuèdè',
                'description' => 'Complexe culturel moderne et espace événementiel',
                'latitude' => 6.3659,
                'longitude' => 2.4105,
                'popularite' => 4,
            ],

            [
                'environment' => 'Cotonou',
                'nom' => 'Plage de Fidjrossè',
                'description' => 'Grande plage populaire de Cotonou',
                'latitude' => 6.3487,
                'longitude' => 2.3388,
                'popularite' => 5,
            ],

            [
                'environment' => 'Cotonou',
                'nom' => 'Erevan Supermarché',
                'description' => 'Centre commercial moderne très fréquenté',
                'latitude' => 6.3579,
                'longitude' => 2.3916,
                'popularite' => 4,
            ],

            [
                'environment' => 'Cotonou',
                'nom' => 'Novotel Cotonou Orisha',
                'description' => 'Hôtel moderne avec vue sur l’océan',
                'latitude' => 6.3544,
                'longitude' => 2.4257,
                'popularite' => 4,
            ],

            [
                'environment' => 'Cotonou',
                'nom' => 'BIIC Agence Ganhi',
                'description' => 'Agence bancaire située au cœur de Ganhi',
                'latitude' => 6.3572,
                'longitude' => 2.4286,
                'popularite' => 3,
            ],

            [
                'environment' => 'Cotonou',
                'nom' => 'Route des Pêches',
                'description' => 'Zone côtière touristique',
                'latitude' => 6.3490,
                'longitude' => 2.3030,
                'popularite' => 5,
            ],

            /*
            |--------------------------------------------------------------------------
            | PORTO-NOVO
            |--------------------------------------------------------------------------
            */

            [
                'environment' => 'Porto-Novo',
                'nom' => 'Grande Mosquée de Porto-Novo',
                'description' => 'Mosquée historique à architecture afro-brésilienne',
                'latitude' => 6.4965,
                'longitude' => 2.6285,
                'popularite' => 5,
            ],

            [
                'environment' => 'Porto-Novo',
                'nom' => 'Musée Honmè',
                'description' => 'Ancien palais royal transformé en musée',
                'latitude' => 6.4960,
                'longitude' => 2.6280,
                'popularite' => 5,
            ],

            [
                'environment' => 'Porto-Novo',
                'nom' => 'Jardin des Plantes et de la Nature',
                'description' => 'Espace vert touristique et pédagogique',
                'latitude' => 6.5001,
                'longitude' => 2.6165,
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

            [
                'environment' => 'Ouidah',
                'nom' => 'Forêt Sacrée de Kpassè',
                'description' => 'Forêt mystique chargée d’histoire',
                'latitude' => 6.3665,
                'longitude' => 2.0840,
                'popularite' => 4,
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

            [
                'environment' => 'Abomey',
                'nom' => 'Musée Historique d’Abomey',
                'description' => 'Musée retraçant l’histoire du Dahomey',
                'latitude' => 7.1821,
                'longitude' => 1.9918,
                'popularite' => 5,
            ],

            /*
            |--------------------------------------------------------------------------
            | PARAKOU
            |--------------------------------------------------------------------------
            */

            [
                'environment' => 'Parakou',
                'nom' => 'Université de Parakou',
                'description' => 'Grande université du nord du Bénin',
                'latitude' => 9.3482,
                'longitude' => 2.6095,
                'popularite' => 4,
            ],

            [
                'environment' => 'Parakou',
                'nom' => 'Marché Arzèkè',
                'description' => 'Grand marché populaire de Parakou',
                'latitude' => 9.3371,
                'longitude' => 2.6307,
                'popularite' => 5,
            ],

            /*
            |--------------------------------------------------------------------------
            | GRAND-POPO
            |--------------------------------------------------------------------------
            */

            [
                'environment' => 'Grand-Popo',
                'nom' => 'Bouche du Roy',
                'description' => 'Lieu touristique entre lagune et océan',
                'latitude' => 6.2811,
                'longitude' => 1.8229,
                'popularite' => 5,
            ],

            /*
            |--------------------------------------------------------------------------
            | NATITINGOU
            |--------------------------------------------------------------------------
            */

            [
                'environment' => 'Natitingou',
                'nom' => 'Musée Régional de Natitingou',
                'description' => 'Musée culturel du nord du Bénin',
                'latitude' => 10.3042,
                'longitude' => 1.3796,
                'popularite' => 4,
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