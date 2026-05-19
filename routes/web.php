<?php

use App\Http\Controllers\EnigmeController;
use App\Http\Controllers\EnvironmentController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\OtpController;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// ajout de middleware admin after
Route::get('/dashboard-admin', function () {
    return Inertia::render('DashboardAdmin');
})->middleware(['auth', 'verified'])->name('dashboard.admin');

Route::get('/dashboard', function () {
    $environments = \App\Models\Environment::where('actif', true)->get();
    $games = \App\Models\Game::where('user_id', auth()->id())
        ->with('environment')
        ->orderBy('created_at', 'desc')
        ->get();

    return Inertia::render('Dashboard', [
        'environments' => $environments,
        'games' => $games
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::resource('environments', EnvironmentController::class);

    Route::get('/environments/{environment}/configure', [GameController::class, 'configure'])
        ->name('game.configure');
    Route::post('/environments/{environment}/start-game', [GameController::class, 'startNewGame'])
        ->name('game.start');
});
Route::middleware('auth')->group(function () {
    Route::resource('places', PlaceController::class);
});
Route::middleware('auth')->group(function () {
    Route::resource('enigmes', EnigmeController::class);
});

// Routes admin — envoyer une invitation
Route::middleware('auth')->group(function () {
    Route::get('/environments/{environment}/invitations', [InvitationController::class, 'index'])
        ->name('invitations.index');
    Route::post('/environments/{environment}/inviter', [InvitationController::class, 'store'])
        ->name('invitations.store');
});

// Routes publiques — parcours de l'invité
Route::prefix('invitation')->name('invitation.')->group(function () {
    // 1. L'invité clique sur le lien
    Route::get('/{token}', [InvitationController::class, 'show'])
        ->name('show');

    // 2. L'invité soumet le formulaire d'inscription
    Route::post('/{token}/register', [InvitationController::class, 'register'])
        ->name('register');

    // 3. L'invité soumet son code OTP
    Route::post('/{token}/otp/verifier', [OtpController::class, 'verifier'])
        ->name('otp.verifier');

    // 4. L'invité demande un nouveau code OTP
    Route::post('/{token}/otp/renvoyer', [OtpController::class, 'renvoyer'])
        ->name('otp.renvoyer');
});

require __DIR__ . '/auth.php';

Route::middleware(['auth'])->group(function () {
    // Affichage de l'énigme en cours du joueur
    Route::get('/game/{game}', [GameController::class, 'showEnigme'])->name('game.show');
    
    // Actions sur l'énigme
    Route::post('/game/{game}/enigme/{enigme}/indice', [GameController::class, 'requestIndice'])->name('game.indice');
    Route::post('/game/{game}/enigme/{enigme}/solution', [GameController::class, 'revealSolution'])->name('game.solution');
    Route::post('/game/{game}/enigme/{enigme}/skip', [GameController::class, 'skipEnigme'])->name('game.skip');
});


require __DIR__.'/auth.php';
