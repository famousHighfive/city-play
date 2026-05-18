<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use App\Models\Enigme;
use Inertia\Inertia;
use App\Models\Environment;


class GameController extends Controller
{


    // 1. Afficher l'énigme active
    public function showEnigme(Game $game)
    {
        // On récupère l'énigme qui a le statut 'en_cours' pour cette partie
        $currentEnigme = $game->enigmes()
            ->with('place') // On charge aussi le lieu (coordonnées GPS, nom...)
            ->wherePivot('statut', 'en_cours')
            ->first();

        if (!$currentEnigme) {
            return to_route('dashboard')->with('message', 'Aucune énigme en cours ou partie terminée !');
        }

        return Inertia::render('Game/PlayEnigme', [
            'game' => $game,
            'enigme' => $currentEnigme,
        ]);
    }

    // 2. Demander un indice
    public function requestIndice(Game $game, Enigme $enigme)
    {
        $pivot = $game->enigmes()->findOrFail($enigme->id)->pivot;

        // On augmente le nombre d'indices demandés (Max 2)
        if ($pivot->nb_indices_demandes < 2) {
            $game->enigmes()->updateExistingPivot($enigme->id, [
                'nb_indices_demandes' => $pivot->nb_indices_demandes + 1
            ]);
        }

        return back();
    }

    // 3. Révéler la solution définitive (Abandon de l'énigme)
    public function revealSolution(Game $game, Enigme $enigme)
    {
        $game->enigmes()->updateExistingPivot($enigme->id, [
            'solution_affichee' => true,
            'statut' => 'non_resolue' // Marqué comme échoué
        ]);

        return back();
    }

    // 4. Passer à l'énigme suivante (quand résolue ou abandonnée)
    public function skipEnigme(Game $game, Enigme $enigme)
    {
        $currentPivot = $game->enigmes()->findOrFail($enigme->id)->pivot;

        // 1. On s'assure que l'énigme actuelle n'est plus 'en_cours'
        if ($currentPivot->statut === 'en_cours') {
            $game->enigmes()->updateExistingPivot($enigme->id, ['statut' => 'passee']);
        }

        // 2. Trouver la prochaine énigme selon la colonne 'ordre'
        $nextEnigme = $game->enigmes()
            ->wherePivot('ordre', $currentPivot->ordre + 1)
            ->first();

        if ($nextEnigme) {
            // Activer la suivante
            $game->enigmes()->updateExistingPivot($nextEnigme->id, ['statut' => 'en_cours']);
            return back()->with('success', 'Énigme suivante débloquée !');
        }

        // Plus d'énigme ? La ville est terminée !
        return to_route('dashboard')->with('success', 'Félicitations, vous avez terminé l\'environnement !');
    }


    public function startNewGame(Environment $environment)
    {
        // 1. Vérifier si le joueur a déjà une partie en cours sur cet environnement
        $existingGame = Game::where('user_id', auth()->id())
            ->where('environment_id', $environment->id)
            ->first();

        if ($existingGame) {
            // Si une partie existe déjà, on le redirige directement dessus
            return to_route('game.show', $existingGame->id);
        }

        // 2. Créer la nouvelle session de jeu
        $game = Game::create([
            'user_id'        => auth()->id(),
            'environment_id' => $environment->id,
        ]);

        // 3. Récupérer toutes les énigmes actives associées aux lieux (places) de cet environnement
        $enigmes = Enigme::whereHas('place', function ($query) use ($environment) {
            $query->where('environment_id', $environment->id);
        })
            ->where('actif', true)
            ->get();

        if ($enigmes->isEmpty()) {
            return back()->with('error', 'Cette ville ne contient aucune énigme active pour le moment.');
        }

        // 4. Attacher les énigmes à la partie dans la table pivot 'enigme_games'
        foreach ($enigmes as $index => $enigme) {
            $game->enigmes()->attach($enigme->id, [
                'ordre'               => $index + 1,
                'statut'              => $index === 0 ? 'en_cours' : 'a_faire', // La 1ère est jouable de suite
                'nb_indices_demandes' => 0,
                'solution_affichee'   => false,
            ]);
        }

        // 5. Rediriger le joueur vers l'interface de jeu que nous avons créée ensemble !
        return to_route('game.show', $game->id);
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Game $game)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Game $game)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Game $game)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Game $game)
    {
        //
    }
}
