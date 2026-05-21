<?php

namespace App\Services;

use App\Models\Enigme;
use App\Models\Environment;

class GamePathService
{
    /**
     * Construire un parcours intelligent.
     * Dans le GamePathService :
     * Il cherche les lieux (places) de la ville.
     * Il calcule la distance entre le joueur et chaque lieu (Formule de Haversine).
     * Il élimine les lieux trop loin (ex: plus de 3km à pied).
     * Il filtre par difficulté (ex: seulement les énigmes easy).
     * Il trie du plus proche au plus loin.
     * Il définit le nombre d'étapes (ex: 60 min à pied = 3 énigmes).
     */
    public function construireParcours(
        Environment $environment,
        float $latitudeJoueur,
        float $longitudeJoueur,
        string $niveau,
        string $locomotion,
        int $duree
    ) {

        /*
        |--------------------------------------------------------------------------
        | 1. Récupérer lieux actifs
        |--------------------------------------------------------------------------
        */

        $places = $environment->places()

            ->where('is_active', true)

            ->with([

                'enigmes' => function ($query) use ($niveau) {

                    $query
                        ->where('actif', true)
                        ->where('niveau', $niveau);
                }

            ])

            ->get();

        /*
        |--------------------------------------------------------------------------
        | 2. Calculer distance joueur -> lieu
        |--------------------------------------------------------------------------
        */

        $placesAvecDistance = $places->map(function ($place) use (

            $latitudeJoueur,
            $longitudeJoueur

        ) {

            $distance = $this->calculerDistance(

                $latitudeJoueur,
                $longitudeJoueur,

                $place->latitude,
                $place->longitude
            );

            $place->distance = round($distance);

            return $place;
        });

        /*
        |--------------------------------------------------------------------------
        | 3. Distance max selon locomotion
        |--------------------------------------------------------------------------
        */

        $distanceMax = $this->distanceMaxParLocomotion(
            $locomotion
        );

        /*
        |--------------------------------------------------------------------------
        | 4. Garder seulement lieux proches
        |--------------------------------------------------------------------------
        */

        $placesDisponibles = $placesAvecDistance

            ->filter(function ($place) use ($distanceMax) {

                return $place->distance <= $distanceMax;
            });

        /*
        |--------------------------------------------------------------------------
        | 5. Supprimer lieux sans énigme
        |--------------------------------------------------------------------------
        */

        $placesDisponibles = $placesDisponibles

            ->filter(function ($place) {

                return $place->enigmes->count() > 0;
            });

        /*
        |--------------------------------------------------------------------------
        | 6. Trier par proximité
        |--------------------------------------------------------------------------
        */

        $placesDisponibles = $placesDisponibles

            ->sortBy('distance')

            ->values();

        /*
        |--------------------------------------------------------------------------
        | 7. Nombre d'étapes selon durée
        |--------------------------------------------------------------------------
        */

        $nombreEtapes = $this->nombreEtapes(
            $locomotion,
            $duree
        );

        /*
        |--------------------------------------------------------------------------
        | 8. Limiter le parcours
        |--------------------------------------------------------------------------
        */

        $placesFinales = $placesDisponibles
            ->take($nombreEtapes);

        /*
        |--------------------------------------------------------------------------
        | 9. Construire parcours final
        |--------------------------------------------------------------------------
        */

        $parcours = $placesFinales->map(function (

            $place,
            $index

        ) {

            /*
            |--------------------------------------------------------------------------
            | Choisir UNE énigme aléatoire du lieu
            |--------------------------------------------------------------------------
            */

            $enigme = $place->enigmes->random();

            return [

                'ordre' => $index + 1,

                'place' => $place,

                'enigme' => $enigme,

                'distance' => $place->distance,
            ];
        });

        return $parcours;
    }

    /**
     * Distance max selon locomotion.
     */
    private function distanceMaxParLocomotion(
        string $locomotion
    ): int {

        return match ($locomotion) {

            'pied' => 3000,

            'velo' => 8000,

            'voiture' => 25000,

            default => 3000,
        };
    }

    /**
     * Nombre d'étapes selon durée.
     */
    private function nombreEtapes(
        string $locomotion,
        int $duree
    ): int {

        $minutesParEtape = match ($locomotion) {

            'pied' => 20,

            'velo' => 15,

            'voiture' => 10,

            default => 20,
        };

        return max(
            1,
            floor($duree / $minutesParEtape)
        );
    }

    /**
     * Calcul distance GPS.
     */
    public function calculerDistance(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): float {

        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);

        $dLng = deg2rad($lng2 - $lng1);

        $a =

            sin($dLat / 2) *
            sin($dLat / 2)

            +

            cos(deg2rad($lat1)) *
            cos(deg2rad($lat2))

            *

            sin($dLng / 2) *
            sin($dLng / 2);

        $c = 2 * atan2(
            sqrt($a),
            sqrt(1 - $a)
        );

        return $earthRadius * $c;
    }
}