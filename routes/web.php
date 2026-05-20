<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Controllers
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\OtpController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\EnigmeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\EnvironmentController;

/*
|--------------------------------------------------------------------------
| PAGE PUBLIQUE
|--------------------------------------------------------------------------
*/

Route::get('/', function () {

    return Inertia::render('Welcome', [

        'canLogin' => Route::has('login'),

        'canRegister' => Route::has('register'),

        'laravelVersion' => Application::VERSION,

        'phpVersion' => PHP_VERSION,
    ]);

});

/*
|--------------------------------------------------------------------------
| DASHBOARD PLAYER
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', function () {

        $environments = \App\Models\Environment::where('actif', true)
            ->get();

        $games = \App\Models\Game::where('user_id', auth()->id())
            ->with('environment')
            ->latest()
            ->get();

        return Inertia::render('Dashboard', [

            'environments' => $environments,

            'games' => $games
        ]);

    })->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| ZONE ADMIN
|--------------------------------------------------------------------------
| Accessible uniquement aux admins
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'admin'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard Admin
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard-admin', function () {

    return Inertia::render('DashboardAdmin', [
    'usersCount' => \App\Models\User::count(),
    'gamesCount' => \App\Models\Game::count(),
    'environmentsCount' => \App\Models\Environment::count(),
    'enigmesCount' => \App\Models\Enigme::count(),

    'recentGames' => \App\Models\Game::latest()
        ->with('environment')
        ->limit(5)
        ->get(),
]);    
    })->name('dashboard.admin');

    /*
    |--------------------------------------------------------------------------
    | Environments
    |--------------------------------------------------------------------------
    */

    Route::resource('environments', EnvironmentController::class);

    /*
    |--------------------------------------------------------------------------
    | Places
    |--------------------------------------------------------------------------
    */

    Route::resource('places', PlaceController::class);

    /*
    |--------------------------------------------------------------------------
    | Enigmes
    |--------------------------------------------------------------------------
    */

    Route::resource('enigmes', EnigmeController::class);

    /*
    |--------------------------------------------------------------------------
    | Invitations
    |--------------------------------------------------------------------------
    */

    Route::get('/environments/{environment}/invitations', [

        InvitationController::class,
        'index'

    ])->name('invitations.index');

    Route::post('/environments/{environment}/inviter', [

        InvitationController::class,
        'store'

    ])->name('invitations.store');
});

/*
|--------------------------------------------------------------------------
| GAME PLAYER
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Configuration partie
    |--------------------------------------------------------------------------
    */

    Route::get('/environments/{environment}/configure', [

        GameController::class,
        'configure'

    ])->name('game.configure');

    /*
    |--------------------------------------------------------------------------
    | Démarrer une partie
    |--------------------------------------------------------------------------
    */

    Route::post('/environments/{environment}/start-game', [

        GameController::class,
        'startNewGame'

    ])->name('game.start');

    /*
    |--------------------------------------------------------------------------
    | Afficher énigme en cours
    |--------------------------------------------------------------------------
    */

    Route::get('/game/{game}', [

        GameController::class,
        'showEnigme'

    ])->name('game.show');

    /*
    |--------------------------------------------------------------------------
    | Demander indice
    |--------------------------------------------------------------------------
    */

    Route::post('/game/{game}/enigme/{enigme}/indice', [

        GameController::class,
        'requestIndice'

    ])->name('game.indice');

    /*
    |--------------------------------------------------------------------------
    | Voir solution
    |--------------------------------------------------------------------------
    */

    Route::post('/game/{game}/enigme/{enigme}/solution', [

        GameController::class,
        'revealSolution'

    ])->name('game.solution');

    /*
    |--------------------------------------------------------------------------
    | Passer énigme
    |--------------------------------------------------------------------------
    */

    Route::post('/game/{game}/enigme/{enigme}/skip', [

        GameController::class,
        'skipEnigme'

    ])->name('game.skip');
});

/*
|--------------------------------------------------------------------------
| PROFIL UTILISATEUR
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/profile', [

        ProfileController::class,
        'edit'

    ])->name('profile.edit');

    Route::patch('/profile', [

        ProfileController::class,
        'update'

    ])->name('profile.update');

    Route::delete('/profile', [

        ProfileController::class,
        'destroy'

    ])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| INVITATIONS PUBLIQUES
|--------------------------------------------------------------------------
*/

Route::prefix('invitation')
    ->name('invitation.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Ouvrir invitation
        |--------------------------------------------------------------------------
        */

        Route::get('/{token}', [

            InvitationController::class,
            'show'

        ])->name('show');

        /*
        |--------------------------------------------------------------------------
        | Inscription invité
        |--------------------------------------------------------------------------
        */

        Route::post('/{token}/register', [

            InvitationController::class,
            'register'

        ])->name('register');

        /*
        |--------------------------------------------------------------------------
        | Vérification OTP
        |--------------------------------------------------------------------------
        */

        Route::post('/{token}/otp/verifier', [

            OtpController::class,
            'verifier'

        ])->name('otp.verifier');

        /*
        |--------------------------------------------------------------------------
        | Renvoyer OTP
        |--------------------------------------------------------------------------
        */

        Route::post('/{token}/otp/renvoyer', [

            OtpController::class,
            'renvoyer'

        ])->name('otp.renvoyer');
    });

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';