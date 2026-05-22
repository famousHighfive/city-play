<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Validation;
use Illuminate\Http\Request;
use App\Models\Enigme;
use Inertia\Inertia;
use App\Services\GamePathService;
use App\Models\Environment;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class GameController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();

        $environments = $user->isAdmin()
            ? Environment::where('actif', true)->get()
            : $user->environmentsAccessibles();

        $gamesQuery = Game::where('user_id', $user->id)
            ->when(! $user->isAdmin(), function ($query) use ($user) {
                $query->whereIn(
                    'environment_id',
                    $user->invitations()->where('statut', 'used')->pluck('environment_id')
                );
            })
            ->with(['environment', 'enigmes'])
            ->latest();

        $games = $gamesQuery->get()->map(function (Game $game) {
            $this->synchroniserStatutPartie($game);

            return $this->formaterPartiePourDashboard($game->fresh(['environment', 'enigmes']));
        });

        $partiesReprendables = $games
            ->filter(fn (array $partie) => $partie['peut_reprendre'])
            ->keyBy('environment_id');

        $environmentsAvecPartie = $environments->map(function ($env) use ($partiesReprendables, $games) {
            $partie = $partiesReprendables->get($env->id);
            $aUnePartieEnCours = $games->contains(
                fn (array $g) => $g['environment_id'] === $env->id
                    && in_array($g['statut'], ['en_cours', 'pause'], true)
            );

            return [
                'id' => $env->id,
                'nom' => $env->nom,
                'description' => $env->description,
                'peut_nouvelle_partie' => ! $aUnePartieEnCours,
                'partie_active' => $partie ? [
                    'id' => $partie['id'],
                    'progression' => $partie['progression'],
                    'etape_reprise' => $partie['etape_reprise'],
                ] : null,
            ];
        });

        $stats = [
            'total_parties' => $games->count(),
            'parties_en_cours' => $games->where('peut_reprendre', true)->count(),
            'enigmes_resolues' => $games->sum('progression.resolues'),
            'enigmes_total' => $games->sum('progression.total'),
        ];

        if ($stats['enigmes_total'] > 0) {
            $stats['pourcentage_global'] = (int) round(
                ($stats['enigmes_resolues'] / $stats['enigmes_total']) * 100
            );
        } else {
            $stats['pourcentage_global'] = 0;
        }

        return Inertia::render('Dashboard', [
            'environments' => $environmentsAvecPartie,
            'games' => $games->values(),
            'stats' => $stats,
            'labels' => $this->dashboardLabels(),
        ]);
    }

    public function configure(Request $request, Environment $environment)
    {
        $this->authorizePlayerEnvironment($environment);

        $existingGame = Game::where('user_id', auth()->id())
            ->where('environment_id', $environment->id)
            ->where('statut', 'en_cours')
            ->with('enigmes')
            ->first();

        $existingGameData = null;

        if ($existingGame) {
            $this->synchroniserStatutPartie($existingGame);
            $existingGame->refresh()->load('enigmes');

            if ($existingGame->statut === 'en_cours' && $this->peutReprendrePartie($existingGame)) {
                $enigme = $this->trouverEnigmeCourante($existingGame, activer: false);
                $existingGameData = [
                    'id' => $existingGame->id,
                    'etape_reprise' => $enigme?->pivot->ordre,
                    'progression' => $this->calculerProgression($existingGame),
                ];
            }
        }

        return Inertia::render('Game/ConfigureGame', [
            'environment' => $environment,
            'gameOptions' => $this->gameOptions($environment),
            'existingGame' => $existingGameData,
            'afficher_modal_mode' => $request->boolean('nouvelle'),
        ]);
    }
    public function startNewGame(Request $request, Environment $environment)
    {
        \Log::info('startNewGame called!', ['user_id' => auth()->id(), 'environment_id' => $environment->id, 'request' => $request->all()]);
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
        
        \Log::info('Validation passed!', ['validated' => $validated]);

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

        // Delete any existing finished game for this environment to allow new game
        Game::where('user_id', auth()->id())
            ->where('environment_id', $environment->id)
            ->where('statut', 'terminee')
            ->delete();

        // Only check for existing game if we're not explicitly starting a new one (like from ?nouvelle=1)
        // For now, let's just delete any in-progress game to let the user start fresh
        Game::where('user_id', auth()->id())
            ->where('environment_id', $environment->id)
            ->where('statut', 'en_cours')
            ->delete();

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
        
        \Log::info('Game created!', ['game_id' => $game->id]);

        $parcours = app(GamePathService::class)

            ->construireParcours(

                $environment,

                $validated['latitude'],
                $validated['longitude'],

                $validated['niveau_difficulte'],

                $validated['moyen_locomotion'],

                (int) $validated['duree_prevue'],
            );
            
        \Log::info('Parcours built!', ['count' => $parcours->count()]);

        if ($parcours->isEmpty()) {
            $game->delete();
            \Log::info('Parcours is empty! Deleting game and returning error.');
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
        
        \Log::info('Enigmes attached to game!');

        // Store parcours in session temporarily
        $formattedParcours = $parcours->map(function ($step) {
            return [
                'latitude' => $step['place']->latitude,
                'longitude' => $step['place']->longitude,
            ];
        })->values();
        
        session(['game_parcours' => $formattedParcours]);
        \Log::info('Parcours stored in session!', ['parcours' => $formattedParcours]);

        // Redirect to the immersive start sequence
        \Log::info('Redirecting to start sequence!', ['route' => route('game.start-sequence', $game->id)]);
        return to_route('game.start-sequence', $game->id);
    }

    public function startSequence(Game $game)
    {
        $this->ensureGameOwner($game);
        
        $parcours = session('game_parcours', []);
        session()->forget('game_parcours');
        
        return Inertia::render('Game/GameStartSequence', [
            'game' => $game,
            'parcours' => $parcours,
        ]);
    }

    public function resume(Game $game)
    {
        return $this->showEnigme($game);
    }

    public function showEnigme(Game $game)
    {
        $this->ensureGameOwner($game);

        if ($game->statut === 'terminee') {
            return redirect()
                ->to(route('game.configure', $game->environment_id).'?nouvelle=1')
                ->with('info', 'Cette partie est terminée. Vous pouvez en lancer une nouvelle.');
        }

        return $this->renderPlayEnigme(
            $game,
            modalLieu: session('modal_lieu'),
            gpsError: session('gps_error'),
        );
    }

    public function pauseGame(Game $game)
    {
        $this->ensureGameOwner($game);
        abort_unless($game->statut === 'en_cours', 403);

        $game->update(['statut' => 'pause']);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Partie mise en pause. Vous pouvez la reprendre depuis le tableau de bord.');
    }

    public function requestIndice(Game $game, Enigme $enigme)
    {
        $this->ensureGameOwner($game);

        $pivot = $game->enigmes()->findOrFail($enigme->id)->pivot;

        if ($pivot->nb_indices_demandes < 2) {
            $game->enigmes()->updateExistingPivot($enigme->id, [
                'nb_indices_demandes' => $pivot->nb_indices_demandes + 1,
            ]);
        }

        return $this->redirectToPlayEnigme($game);
    }

    public function revealSolution(Game $game, Enigme $enigme)
    {
        $this->ensureGameOwner($game);

        $enigmeLiee = $game->enigmes()->with('place')->where('enigmes.id', $enigme->id)->first();

        abort_unless($enigmeLiee !== null, 404);

        $pivot = $enigmeLiee->pivot;

        if ($pivot->statut === 'non_resolue' && $pivot->solution_affichee) {
            return $this->redirectToPlayEnigme($game, $this->modalLieuDonnees($enigmeLiee));
        }

        if ($pivot->statut === 'en_cours') {
            $game->enigmes()->updateExistingPivot($enigmeLiee->id, [
                'solution_affichee' => true,
                'statut' => 'non_resolue',
            ]);

            return $this->redirectToPlayEnigme($game, $this->modalLieuDonnees($enigmeLiee));
        }

        $enigmeActive = $this->enigmeEnCours($game);

        if ($enigmeActive) {
            $game->enigmes()->updateExistingPivot($enigmeActive->id, [
                'solution_affichee' => true,
                'statut' => 'non_resolue',
            ]);

            return $this->redirectToPlayEnigme($game, $this->modalLieuDonnees($enigmeActive));
        }

        return redirect()
            ->route('game.show', $game)
            ->with('error', 'Aucune énigme en cours pour afficher la solution.');
    }

    /**
     * Valide la position GPS du joueur par rapport au lieu de l'énigme.
     */
    public function validerPosition(Request $request, Game $game, Enigme $enigme)
    {
        $this->ensureGameOwner($game);

        $enigmeLiee = $game->enigmes()->with('place')->where('enigmes.id', $enigme->id)->first()
            ?? $this->enigmeEnCours($game);

        abort_unless($enigmeLiee !== null, 404);

        $enigme = $enigmeLiee;
        $pivot = $enigme->pivot;

        if ($pivot->statut === 'resolue') {
            return $this->redirectToPlayEnigme($game, $this->modalLieuDonnees($enigme, 'success'));
        }

        abort_unless($pivot->statut === 'en_cours', 403, 'Cette énigme n\'est plus en cours de résolution.');

        $data = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $place = $enigme->place;
        $distance = $this->distanceMetres(
            (float) $data['latitude'],
            (float) $data['longitude'],
            (float) $place->latitude,
            (float) $place->longitude,
        );

        $rayon = (float) ($place->rayon_validation ?? 30);

        if ($distance > $rayon) {
            return $this->redirectToPlayEnigme(
                $game,
                gpsError: sprintf(
                    'Vous êtes à %d m du lieu (rayon autorisé : %d m). Rapprochez-vous.',
                    (int) round($distance),
                    (int) $rayon,
                ),
            );
        }

        $game->enigmes()->updateExistingPivot($enigme->id, [
            'statut' => 'resolue',
            'date_resolution' => now(),
        ]);

        Validation::create([
            'game_id' => $game->id,
            'place_id' => $place->id,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'distance_metres' => round($distance, 2),
            'bonne_reponse' => true,
            'date_validation' => now(),
        ]);

        return $this->redirectToPlayEnigme($game, $this->modalLieuDonnees($enigme, 'success'));
    }

    public function skipEnigme(Game $game, Enigme $enigme)
    {
        $this->ensureGameOwner($game);

        $enigmeLiee = $game->enigmes()->where('enigmes.id', $enigme->id)->first()
            ?? $this->trouverEnigmeCourante($game, activer: false);

        abort_unless($enigmeLiee !== null, 404);

        $enigme = $enigmeLiee;
        $currentPivot = $enigme->pivot;

        if ($currentPivot->statut === 'en_cours') {
            $game->enigmes()->updateExistingPivot($enigme->id, ['statut' => 'passee']);
        }

        $nextEnigme = $game->enigmes()
            ->wherePivot('ordre', $currentPivot->ordre + 1)
            ->first();

        if ($nextEnigme) {
            $game->enigmes()->updateExistingPivot($nextEnigme->id, ['statut' => 'en_cours']);

            return $this->redirectToPlayEnigme($game);
        }

        $game->update([
            'statut' => 'terminee',
            'date_fin' => now(),
        ]);

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

    private function formaterPartiePourDashboard(Game $game): array
    {
        $progression = $this->calculerProgression($game);
        $enigmeReprise = $this->trouverEnigmeCourante($game, activer: false);

        return [
            'id' => $game->id,
            'environment_id' => $game->environment_id,
            'statut' => $game->statut,
            'mode_jeu' => $game->mode_jeu,
            'nb_membres' => $game->nb_membres,
            'duree_prevue' => $game->duree_prevue,
            'duree_restante' => $game->duree_restante,
            'moyen_locomotion' => $game->moyen_locomotion,
            'niveau_difficulte' => $game->niveau_difficulte,
            'date_debut' => $game->date_debut,
            'date_fin' => $game->date_fin,
            'environment' => $game->environment,
            'progression' => $progression,
            'peut_reprendre' => $this->peutReprendrePartie($game),
            'etape_reprise' => $enigmeReprise?->pivot->ordre,
        ];
    }

    private function calculerProgression(Game $game): array
    {
        $enigmes = $game->enigmes;
        $total = $enigmes->count();

        $resolues = $enigmes->where('pivot.statut', 'resolue')->count();
        $nonResolues = $enigmes->where('pivot.statut', 'non_resolue')->count();
        $passees = $enigmes->where('pivot.statut', 'passee')->count();
        $aFaire = $enigmes->where('pivot.statut', 'a_faire')->count();
        $enigmeEnCours = $enigmes->firstWhere('pivot.statut', 'en_cours');
        $enigmeReprise = $this->trouverEnigmeCourante($game, activer: false);

        return [
            'total' => $total,
            'resolues' => $resolues,
            'non_resolues' => $nonResolues,
            'passees' => $passees,
            'a_faire' => $aFaire,
            'etape_actuelle' => $enigmeReprise?->pivot->ordre ?? $enigmeEnCours?->pivot->ordre,
            'pourcentage_resolues' => $total > 0 ? (int) round(($resolues / $total) * 100) : 0,
            'pourcentage_avancement' => $total > 0
                ? (int) round((($resolues + $nonResolues + $passees) / $total) * 100)
                : 0,
            'terminee' => $game->statut === 'terminee',
        ];
    }

    private function peutReprendrePartie(Game $game): bool
    {
        return in_array($game->statut, ['en_cours', 'pause'], true)
            && $this->trouverEnigmeCourante($game, activer: false) !== null;
    }

    /**
     * Affiche la page de jeu avec l'énigme courante.
     */
    private function renderPlayEnigme(Game $game, ?array $modalLieu = null, ?string $gpsError = null)
    {
        $game->load('environment');

        if ($game->statut === 'pause') {
            $game->update(['statut' => 'en_cours']);
            $game->refresh();
        }

        if ($game->statut === 'terminee') {
            return redirect()
                ->to(route('game.configure', $game->environment_id).'?nouvelle=1')
                ->with('info', 'Cette partie est terminée. Vous pouvez en lancer une nouvelle.');
        }

        $currentEnigme = $this->trouverEnigmeCourante($game, activer: true);

        if (! $currentEnigme && $modalLieu !== null) {
            $currentEnigme = $game->enigmes()
                ->with('place')
                ->wherePivotIn('statut', ['resolue', 'non_resolue'])
                ->orderByPivot('ordre', 'desc')
                ->first();
        }

        if (! $currentEnigme) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Impossible de reprendre cette partie. Commencez-en une nouvelle.');
        }

        $totalEnigmes = $game->enigmes()->count();

        return Inertia::render('Game/PlayEnigme', [
            'game' => $game,
            'enigme' => $currentEnigme->load('place'),
            'progression' => [
                'etape' => $currentEnigme->pivot->ordre,
                'total' => $totalEnigmes,
            ],
            'labels' => $this->gameLabels(),
            'modal_lieu' => $modalLieu,
            'gps_error' => $gpsError,
        ]);
    }

    /**
     * Trouve l'énigme où le joueur doit reprendre (sans révéler le lieu).
     */
    private function trouverEnigmeCourante(Game $game, bool $activer = false): ?Enigme
    {
        if (! in_array($game->statut, ['en_cours', 'pause'], true)) {
            return null;
        }

        $enigme = $game->enigmes()
            ->with('place')
            ->wherePivot('statut', 'en_cours')
            ->orderByPivot('ordre')
            ->first();

        if ($enigme) {
            return $enigme;
        }

        // Dernière énigme validée ou solution vue (y compris la dernière de la partie)
        $enigme = $game->enigmes()
            ->with('place')
            ->wherePivotIn('statut', ['resolue', 'non_resolue'])
            ->orderByPivot('ordre', 'desc')
            ->first();

        if ($enigme) {
            return $enigme;
        }

        if (! $activer) {
            return null;
        }

        $enigme = $game->enigmes()
            ->with('place')
            ->wherePivot('statut', 'a_faire')
            ->orderByPivot('ordre')
            ->first();

        if ($enigme) {
            $game->enigmes()->updateExistingPivot($enigme->id, ['statut' => 'en_cours']);

            return $game->enigmes()
                ->with('place')
                ->where('enigmes.id', $enigme->id)
                ->first();
        }

        return null;
    }

    private function ensureGameOwner(Game $game): void
    {
        abort_unless((int) $game->user_id === (int) auth()->id(), 403);
    }

    private function enigmeEnCours(Game $game): ?Enigme
    {
        return $game->enigmes()
            ->with('place')
            ->wherePivot('statut', 'en_cours')
            ->orderByPivot('ordre')
            ->first();
    }

    private function modalLieuDonnees(Enigme $enigme, string $type = 'solution'): array
    {
        return [
            'type' => $type,
            'nom' => $enigme->place->nom,
            'description' => $enigme->place->description,
        ];
    }

    /**
     * Redirige vers la page de jeu (URL stable) en conservant les données flash pour les modales.
     */
    private function redirectToPlayEnigme(Game $game, ?array $modalLieu = null, ?string $gpsError = null)
    {
        $redirect = redirect()->route('game.show', $game);

        if ($modalLieu !== null) {
            $redirect->with('modal_lieu', $modalLieu);
        }

        if ($gpsError !== null) {
            $redirect->with('gps_error', $gpsError);
        }

        return $redirect;
    }

    /**
     * Marque la partie terminée uniquement quand il ne reste plus d'énigmes jouables.
     * La fin effective est gérée par skipEnigme ; ici on corrige l'affichage dashboard.
     */
    private function synchroniserStatutPartie(Game $game): void
    {
        if ($game->statut !== 'en_cours') {
            return;
        }

        if ($game->enigmes()->count() === 0) {
            return;
        }

        $resteAJouer = $game->enigmes()
            ->wherePivotIn('statut', ['a_faire', 'en_cours'])
            ->count();

        if ($resteAJouer > 0) {
            return;
        }

        $game->update([
            'statut' => 'terminee',
            'date_fin' => $game->date_fin ?? now(),
        ]);
    }

    private function dashboardLabels(): array
    {
        return [
            ...$this->gameLabels(),
            'statuts_partie' => [
                'en_cours' => 'En cours',
                'terminee' => 'Terminée',
                'pause' => 'En pause',
                'abandonnee' => 'Abandonnée',
            ],
            'statuts_enigme' => [
                'resolues' => 'Résolues',
                'non_resolues' => 'Non résolues',
                'passees' => 'Passées',
                'a_faire' => 'À faire',
                'en_cours' => 'En cours',
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

    // public function validatePosition(Request $request, Game $game
    /**
     * Nombre d'énigmes selon durée et locomotion (instructions projet).
     */
    private function nombreEnigmesPourPartie(string $moyenLocomotion, int $dureePrevue): int
    {
        $minutesParEnigme = match ($moyenLocomotion) {
            'pied' => 20,
            'velo' => 15,
            'voiture' => 10,
            default => 20,
        };

        return max(1, (int) floor($dureePrevue / $minutesParEnigme));
    }

    /**
     * Données énigme envoyées au joueur (sans révéler le lieu).
     */
    private function enigmePourJoueur(Enigme $enigme): array
    {
        return [
            'id' => $enigme->id,
            'texte' => $enigme->texte,
            'image' => $enigme->image,
            'indice_1' => $enigme->indice_1,
            'indice_2' => $enigme->indice_2,
            'pivot' => $enigme->pivot,
            'lieu_validation' => [
                'latitude' => $enigme->place->latitude,
                'longitude' => $enigme->place->longitude,
                'rayon_validation' => $enigme->place->rayon_validation ?? 30,
            ],
        ];
    }

    private function distanceMetres(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        $a = sin($latDelta / 2) ** 2
            + cos($latFrom) * cos($latTo) * sin($lonDelta / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function selectionnerEnigmes(
        Environment $environment,
        string $niveauDifficulte,
        string $moyenLocomotion,
        int $dureePrevue,
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
