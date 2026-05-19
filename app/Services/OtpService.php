<?php

namespace App\Services;

use App\Models\OtpCode;

class OtpService
{
    /**
     * Génère un nouveau code OTP et le sauvegarde en base.
     * Appelé juste après l'inscription de l'invité.
     */
    public function generer(string $destinataire, string $canal): OtpCode
    {
        // Supprime tous les anciens codes de ce destinataire
        // pour qu'il n'y ait jamais qu'un seul code actif à la fois
        OtpCode::where('destinataire', $destinataire)->delete();

        return OtpCode::create([
            'destinataire' => $destinataire,
            'canal'        => $canal,
            'code'         => str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'expires_at'   => now()->addMinutes(10),
        ]);
    }

    /**
     * Vérifie que le code soumis par l'invité est correct.
     * Retourne true si valide, false sinon.
     */
    public function verifier(string $destinataire, string $code): bool
    {
        $otp = OtpCode::where('destinataire', $destinataire)
                      ->latest()
                      ->first();

        // Pas de code trouvé
        if (!$otp) {
            return false;
        }

        // Code expiré
        if ($otp->isExpired()) {
            $otp->delete();
            return false;
        }

        // Trop de tentatives
        if ($otp->isBloque()) {
            $otp->delete();
            abort(429, 'Trop de tentatives. Demande un nouveau code.');
        }

        // On incrémente le nombre de tentatives
        $otp->increment('tentatives');

        // Code incorrect
        if ($otp->code !== $code) {
            return false;
        }

        // Code correct — on supprime le code, il ne sert plus à rien
        $otp->delete();
        return true;
    }
}