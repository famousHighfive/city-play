<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\Environment;
use App\Models\User;
use Illuminate\Support\Str;

class InvitationService
{
    /**
     * Crée une invitation et retourne l'objet créé.
     * Appelé par l'admin quand il veut inviter quelqu'un.
     */
    public function creer(Environment $environment, string $destinataire, string $canal): Invitation
    {
        return Invitation::create([
            'environment_id' => $environment->id,
            'canal'          => $canal,
            'destinataire'   => $destinataire,
            'token'          => Str::random(64),
            'statut'         => 'pending',
            'expires_at'     => now()->addHours(5),
        ]);
    }

    /**
     * Vérifie qu'un token est valide et retourne l'invitation.
     * Appelé quand l'invité clique sur le lien.
     */
    public function validerToken(string $token): Invitation
    {
        // On cherche l'invitation avec ce token
        $invitation = Invitation::where('token', $token)->firstOrFail();

        // Si elle est expirée on met à jour le statut et on arrête
        if ($invitation->isExpired()) {
            $invitation->update(['statut' => 'expired']);
            abort(410, 'Ce lien d\'invitation a expiré.');
        }

        // Si elle a déjà été utilisée on arrête aussi
        if ($invitation->isUsed()) {
            abort(410, 'Ce lien d\'invitation a déjà été utilisé.');
        }

        return $invitation;
    }

    /**
     * Marque l'invitation comme utilisée.
     * Appelé quand l'invité a fini son inscription.
     */
    public function marquerUtilisee(Invitation $invitation, User $player): void
    {
        $invitation->update([
            'statut'    => 'used',
            'used_at'   => now(),
            'player_id' => $player->id,
        ]);
    }
}