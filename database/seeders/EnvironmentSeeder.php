<?php

namespace Database\Seeders;

use App\Models\Environment;
use Illuminate\Database\Seeder;

class EnvironmentSeeder extends Seeder
{
    public function run(): void
    {
        $cities = [

            [
                'nom' => 'Cotonou',
                'description' => 'Capitale économique du Bénin',
            ],

            [
                'nom' => 'Porto-Novo',
                'description' => 'Capitale administrative du Bénin',
            ],

            [
                'nom' => 'Ouidah',
                'description' => 'Ville historique et culturelle',
            ],

            [
                'nom' => 'Abomey',
                'description' => 'Ancienne capitale du royaume du Dahomey',
            ],

            [
                'nom' => 'Parakou',
                'description' => 'Grande ville du nord du Bénin',
            ],

            [
                'nom' => 'Grand-Popo',
                'description' => 'Ville touristique côtière',
            ],

            [
                'nom' => 'Natitingou',
                'description' => 'Porte des montagnes de l’Atacora',
            ],

            [
                'nom' => 'Bohicon',
                'description' => 'Ville carrefour du centre du Bénin',
            ],
        ];

        foreach ($cities as $city) {

            Environment::create([
                'nom' => $city['nom'],
                'description' => $city['description'],
                'actif' => true,
            ]);
        }
    }
}