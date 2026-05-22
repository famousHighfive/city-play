<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAccessExpiration
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Si l'utilisateur est connecté, que c'est un joueur et que son accès est expiré
        if ($user && $user->role === 'player' && $user->access_expires_at && $user->access_expires_at->isPast()) {
            
            // Optionnel : Tu peux changer son statut en base avant de le déconnecter
            $user->update(['account_status' => 'expired']);
            
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Votre accès a expiré. Veuillez contacter un administrateur.');
        }

        return $next($request);
    }
}