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
        $user = auth()->user();

        // Joueur : uniquement les villes liées à ses invitations acceptées
        $environments = $user->isAdmin()
            ? \App\Models\Environment::where('actif', true)->get()
            : $user->environmentsAccessibles();

        $games = \App\Models\Game::where('user_id', $user->id)
            ->when(! $user->isAdmin(), function ($query) use ($user) {
                $query->whereIn(
                    'environment_id',
                    $user->invitations()->where('statut', 'used')->pluck('environment_id')
                );
            })
            ->with('environment')
            ->latest()
            ->get();

        return Inertia::render('Dashboard', [
            'environments' => $environments,
            'games' => $games,
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

    Route::resource('environments', EnvironmentController::class);
    Route::resource('places', PlaceController::class);
    Route::resource('enigmes', EnigmeController::class);

    Route::get('/environments/{environment}/invitations', [InvitationController::class, 'index'])
        ->name('invitations.index');

    Route::post('/environments/{environment}/inviter', [InvitationController::class, 'store'])
        ->name('invitations.store');
});

/*
|--------------------------------------------------------------------------
| GAME PLAYER
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/environments/{environment}/configure', [GameController::class, 'configure'])
        ->name('game.configure');

    Route::post('/environments/{environment}/start-game', [GameController::class, 'startNewGame'])
        ->name('game.start');

    Route::get('/game/{game}', [GameController::class, 'showEnigme'])
        ->name('game.show');

    Route::post('/game/{game}/enigme/{enigme}/indice', [GameController::class, 'requestIndice'])
        ->name('game.indice');

    Route::post('/game/{game}/enigme/{enigme}/solution', [GameController::class, 'revealSolution'])
        ->name('game.solution');

    Route::post('/game/{game}/enigme/{enigme}/skip', [GameController::class, 'skipEnigme'])
        ->name('game.skip');

        Route::post('/games/{game}/validate-position', [
    GameController::class,
    'validatePosition'
])->name('game.validate.position');
});

/*
|--------------------------------------------------------------------------
| PROFIL UTILISATEUR
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| INVITATIONS PUBLIQUES
|--------------------------------------------------------------------------
*/

Route::prefix('invitation')
    ->name('invitation.')
    ->group(function () {
        // Routes spécifiques en premier (avant GET /{token})
        Route::get('/{token}/register', [InvitationController::class, 'registerUrlFallback'])
            ->name('register.fallback');

        Route::get('/{token}/otp', [InvitationController::class, 'showOtp'])
            ->name('otp.show');

        Route::post('/{token}/register', [InvitationController::class, 'register'])
            ->name('register');

        Route::post('/{token}/otp/verifier', [OtpController::class, 'verifier'])
            ->name('otp.verifier');

        Route::post('/{token}/otp/renvoyer', [OtpController::class, 'renvoyer'])
            ->name('otp.renvoyer');

        Route::get('/{token}', [InvitationController::class, 'show'])
            ->name('show');
    });

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';
