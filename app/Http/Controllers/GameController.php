<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use App\Models\Enigme;
use Inertia\Inertia;
use App\Models\Environment;


class GameController extends Controller
{
    public function show(Game $game)
    {
        return $this->showEnigme($game);
    }

    public function configure(Environment $environment)
    {
        return Inertia::render('Game/ConfigureGame', [
            'environment' => $environment
        ]);
    }

    public function startNewGame(Request $request, Environment $environment)
    {
        $validated = $request->validate([
            'nb_membres' => 'required|integer|min:1|max:10',
            'duree_prevue' => 'required|integer|min:1',
            'moyen_locomotion' => 'required|in:pied,velo,voiture',
            'niveau_difficulte' => 'required|integer|min:1|max:3',
        ]);

        $existingGame = Game::where('user_id', auth()->id())
            ->where('environment_id', $environment->id)
            ->first();

        if ($existingGame) {
            return to_route('game.show', $existingGame->id);
        }

        $game = Game::create([
            'user_id' => auth()->id(),
            'environment_id' => $environment->id,
            'date_debut' => now(),
            'statut' => 'en_cours',
            'nb_membres' => $validated['nb_membres'],
            'duree_prevue' => $validated['duree_prevue'],
            'duree_restante' => $validated['duree_prevue'],
            'moyen_locomotion' => $validated['moyen_locomotion'],
            'niveau_difficulte' => $validated['niveau_difficulte'],
        ]);

        $enigmes = Enigme::whereHas('place', function ($query) use ($environment) {
            $query->where('environment_id', $environment->id);
        })
            ->where('actif', true)
            ->where('niveau', $validated['niveau_difficulte'])
            ->get();

        if ($enigmes->isEmpty()) {
            $enigmes = Enigme::whereHas('place', function ($query) use ($environment) {
                $query->where('environment_id', $environment->id);
            })
                ->where('actif', true)
                ->get();
        }

        if ($enigmes->isEmpty()) {
            return back()->with('error', 'Cette ville ne contient aucune énigme active pour le moment.');
        }

        foreach ($enigmes as $index => $enigme) {
            $game->enigmes()->attach($enigme->id, [
                'ordre' => $index + 1,
                'statut' => $index === 0 ? 'en_cours' : 'a_faire',
                'nb_indices_demandes' => 0,
                'solution_affichee' => false,
            ]);
        }

        return to_route('game.show', $game->id);
    }

    public function showEnigme(Game $game)
    {
        $currentEnigme = $game->enigmes()
            ->with('place')
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

    public function requestIndice(Game $game, Enigme $enigme)
    {
        $pivot = $game->enigmes()->findOrFail($enigme->id)->pivot;

        if ($pivot->nb_indices_demandes < 2) {
            $game->enigmes()->updateExistingPivot($enigme->id, [
                'nb_indices_demandes' => $pivot->nb_indices_demandes + 1
            ]);
        }

        return back();
    }

    public function revealSolution(Game $game, Enigme $enigme)
    {
        $game->enigmes()->updateExistingPivot($enigme->id, [
            'solution_affichee' => true,
            'statut' => 'non_resolue'
        ]);

        return back();
    }

    public function skipEnigme(Game $game, Enigme $enigme)
    {
        $currentPivot = $game->enigmes()->findOrFail($enigme->id)->pivot;

        if ($currentPivot->statut === 'en_cours') {
            $game->enigmes()->updateExistingPivot($enigme->id, ['statut' => 'passee']);
        }

        $nextEnigme = $game->enigmes()
            ->wherePivot('ordre', $currentPivot->ordre + 1)
            ->first();

        if ($nextEnigme) {
            $game->enigmes()->updateExistingPivot($nextEnigme->id, ['statut' => 'en_cours']);
            return back()->with('success', 'Énigme suivante débloquée !');
        }

        return to_route('dashboard')->with('success', 'Félicitations, vous avez terminé l\'environnement !');
    }
}
