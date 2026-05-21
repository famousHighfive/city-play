<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use App\Models\Enigme;
use Inertia\Inertia;
use App\Services\GamePathService;
use App\Models\Environment;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class GameController extends Controller
{
    public function configure(Environment $environment)
    {
        $this->authorizePlayerEnvironment($environment);

        $existingGame = Game::where('user_id', auth()->id())
            ->where('environment_id', $environment->id)
            ->where('statut', 'en_cours')
            ->first();

        return Inertia::render('Game/ConfigureGame', [
            'environment' => $environment,
            'gameOptions' => $this->gameOptions($environment),
            'existingGame' => $existingGame,
        ]);
    }


    public function startNewGame(Request $request, Environment $environment)
    {
        $this->authorizePlayerEnvironment($environment);

        $validated = $request->validate([
            'mode_jeu' => ['required', Rule::in(['equipe', 'challenge'])],
            'duree_prevue' => 'required|integer|min:1',
            'moyen_locomotion' => ['required', Rule::in(['pied', 'velo', 'voiture'])],
            'niveau_difficulte' => ['required', Rule::in(['easy', 'medium', 'hard', 'kid'])],
            'nb_membres' => 'required_if:mode_jeu,equipe|nullable|integer|min:1|max:10',
            'participants' => 'nullable|array',
            'participants.*' => 'required|email|distinct',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'challenger_email' => 'required_if:mode_jeu,challenge|nullable|email',
        ]);

        if ($validated['mode_jeu'] === 'equipe') {
            $nbMembres = (int) $validated['nb_membres'];
            $emailsCoEquipiers = array_values($validated['participants'] ?? []);
            $nbCoEquipiersAttendu = max(0, $nbMembres - 1);

            if (count($emailsCoEquipiers) !== $nbCoEquipiersAttendu) {
                throw ValidationException::withMessages([
                    'participants' => $nbCoEquipiersAttendu === 0
                        ? 'Aucun email supplémentaire n\'est requis lorsque vous jouez seul.'
                        : "Indiquez {$nbCoEquipiersAttendu} email(s) pour vos coéquipiers.",
                ]);
            }

            $captainEmail = auth()->user()->email;
            $participants = $captainEmail
                ? array_merge([$captainEmail], $emailsCoEquipiers)
                : $emailsCoEquipiers;
        } else {
            $nbMembres = 1;
            $participants = null;
        }

        $existingGame = Game::where('user_id', auth()->id())
            ->where('environment_id', $environment->id)
            ->where('statut', 'en_cours')
            ->first();

        if ($existingGame) {
            return to_route('game.show', $existingGame->id);
        }

        $game = Game::create([
            'user_id' => auth()->id(),
            'environment_id' => $environment->id,
            'date_debut' => now(),
            'statut' => 'en_cours',
            'mode_jeu' => $validated['mode_jeu'],
            'nb_membres' => $nbMembres,
            'participants' => $participants,
            'challenger_email' => $validated['mode_jeu'] === 'challenge'
                ? $validated['challenger_email']
                : null,
            'duree_prevue' => $validated['duree_prevue'],
            'duree_restante' => $validated['duree_prevue'],
            'moyen_locomotion' => $validated['moyen_locomotion'],
            'niveau_difficulte' => $validated['niveau_difficulte'],
            'latitude_depart' => $validated['latitude'],
            'longitude_depart' => $validated['longitude'],
        ]);

        $parcours = app(GamePathService::class)

            ->construireParcours(

                $environment,

                $validated['latitude'],
                $validated['longitude'],

                $validated['niveau_difficulte'],

                $validated['moyen_locomotion'],

                (int) $validated['duree_prevue'],
            );

        if ($parcours->isEmpty()) {
            $game->delete();

            return back()->with('error', 'Cette ville ne contient aucune énigme active pour le moment.');
        }

        foreach ($parcours as $step) {
            $game->enigmes()->attach(

                $step['enigme']->id,

                [

                    'ordre' => $step['ordre'],

                    'statut' => $step['ordre'] === 1
                        ? 'en_cours'
                        : 'a_faire',

                    'nb_indices_demandes' => 0,

                    'solution_affichee' => false,
                ]
            );
        }
        // dd($parcours);
        return to_route('game.show', $game->id);
    }

    public function showEnigme(Game $game)
    {
        abort_unless($game->user_id === auth()->id(), 403);

        $game->load('environment');

        $currentEnigme = $game->enigmes()
            ->with('place')
            ->wherePivot('statut', 'en_cours')
            ->first();

        if (!$currentEnigme) {
            return to_route('dashboard')->with('message', 'Aucune énigme en cours ou partie terminée !');
        }

        $totalEnigmes = $game->enigmes()->count();

        return Inertia::render('Game/PlayEnigme', [
            'game' => $game,
            'enigme' => $currentEnigme,
            'progression' => [
                'etape' => $currentEnigme->pivot->ordre,
                'total' => $totalEnigmes,
            ],
            'labels' => $this->gameLabels(),
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

    private function authorizePlayerEnvironment(Environment $environment): void
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return;
        }

        abort_unless(
            $user->hasAccessToEnvironment($environment),
            403,
            'Vous n\'avez pas accès à cet environnement.'
        );
    }

    private function gameOptions(Environment $environment): array
    {
        $enigmesDisponibles = Enigme::whereHas('place', function ($query) use ($environment) {
            $query->where('environment_id', $environment->id);
        })
            ->where('actif', true)
            ->count();

        return [
            'max_membres' => 10,
            'duree_defaut' => 60,
            'duree_min' => 1,
            'enigmes_disponibles' => $enigmesDisponibles,
            'modes' => [
                [
                    'value' => 'equipe',
                    'label' => 'Mode Équipe',
                    'description' => 'Jusqu\'à 10 joueurs (vous inclus). Seul par défaut, emails requis pour les coéquipiers.',
                    'emoji' => '👥',
                ],
                [
                    'value' => 'challenge',
                    'label' => 'Mode Challenge',
                    'description' => 'Affrontez un adversaire dans une course stratégique.',
                    'emoji' => '⚔️',
                ],
            ],
            'moyens_locomotion' => [
                ['value' => 'pied', 'label' => 'À pied', 'emoji' => '🚶'],
                ['value' => 'velo', 'label' => 'À vélo', 'emoji' => '🚴'],
                ['value' => 'voiture', 'label' => 'Voiture', 'emoji' => '🚗'],
            ],
            'niveaux_difficulte' => [
                ['value' => 'easy', 'label' => 'Facile', 'emoji' => '🟢'],
                ['value' => 'medium', 'label' => 'Moyen', 'emoji' => '🟠'],
                ['value' => 'hard', 'label' => 'Difficile', 'emoji' => '🔴'],
                ['value' => 'kid', 'label' => 'Enfant', 'emoji' => '🧒'],
            ],
        ];
    }

    private function gameLabels(): array
    {
        return [
            'moyens_locomotion' => [
                'pied' => '🚶 À pied',
                'velo' => '🚴 À vélo',
                'voiture' => '🚗 Voiture',
            ],
            'niveaux_difficulte' => [
                'easy' => '🟢 Facile',
                'medium' => '🟠 Moyen',
                'hard' => '🔴 Difficile',
                'kid' => '🧒 Enfant',
            ],
            'modes' => [
                'equipe' => '👥 Équipe',
                'challenge' => '⚔️ Challenge',
            ],
        ];
    }

    public function validatePosition(Request $request, Game $game
    ) {

        $request->validate([

            'latitude' => [
                'required',
                'numeric'
            ],

            'longitude' => [
                'required',
                'numeric'
            ],

            'enigme_id' => [
                'required',
                'exists:enigmes,id'
            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | Récupération énigme + lieu
        |--------------------------------------------------------------------------
        */

        $enigme = Enigme::with('place')
            ->findOrFail(
                $request->enigme_id
            );


        /*
        |--------------------------------------------------------------------------
        | Calcul distance GPS
        |--------------------------------------------------------------------------
        */

        $distance = app(GamePathService::class)
            ->calculerDistance(

                $request->latitude,
                $request->longitude,

                $enigme->place->latitude,
                $enigme->place->longitude

            );


        /*
        |--------------------------------------------------------------------------
        | Vérification rayon GPS
        |--------------------------------------------------------------------------
        */

        if (
            $distance >
            $enigme->place->rayon_validation
        ) {

            return back()->with(
                'error',
                'Vous êtes trop loin du lieu.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Marquer énigme résolue
        |--------------------------------------------------------------------------
        */

        $game->enigmes()
            ->updateExistingPivot(
                $enigme->id,
                [

                    'statut' => 'resolue',

                    'resolue_at' => now(),

                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Chercher prochaine énigme
        |--------------------------------------------------------------------------
        */

        $nextEnigme = $game->enigmes()

            ->wherePivot(
                'statut',
                'en_attente'
            )

            ->orderByPivot('ordre')

            ->first();


        /*
        |--------------------------------------------------------------------------
        | Si plus d'énigme
        |--------------------------------------------------------------------------
        */

        if (!$nextEnigme) {

            $game->update([
                'statut' => 'terminee'
            ]);

            return redirect()
                ->route(
                    'game.finish',
                    $game->id
                );

        }


        /*
        |--------------------------------------------------------------------------
        | Sinon retour jeu
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'game.show',
                $game->id
            );

    }

}
