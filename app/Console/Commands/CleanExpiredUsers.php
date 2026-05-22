<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CleanExpiredUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-expired-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
public function handle(): void
{
    // On prend les joueurs expirés qui ne sont pas DEJA dans la corbeille
    $expiredUsers = User::where('role', 'player')
        ->where('access_expires_at', '<', now())
        ->get();

    $count = $expiredUsers->count();

    if ($count > 0) {
        foreach ($expiredUsers as $user) {
            // Passe le statut en 'expired' pour l'historique visuel avant de le masquer
            $user->update(['account_status' => 'expired']);
            
            // Met l'utilisateur dans la corbeille (remplit la colonne deleted_at)
            $user->delete(); 
        }

        $this->info("$count joueur(s) expiré(s) ont été archivé(s) automatiquement.");
    }
}
}
