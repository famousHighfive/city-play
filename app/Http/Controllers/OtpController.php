<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\InvitationService;
use App\Services\NotificationService;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class OtpController extends Controller
{
    public function __construct(
        private OtpService          $otpService,
        private InvitationService   $invitationService,
        private NotificationService $notificationService,
    ) {}

    /**
     * L'invité soumet son code OTP.
     */
    public function verifier(Request $request, string $token)
    {
        $data = $request->validate([
            'code'         => 'required|digits:6',
            'destinataire' => 'required|string',
        ]);

        // Vérifie le code OTP
        $valide = $this->otpService->verifier($data['destinataire'], $data['code']);

        if (!$valide) {
            return back()->withErrors([
                'code' => 'Code incorrect ou expiré.',
            ]);
        }

        // Récupère les infos de session
        $invitation = $this->invitationService->validerToken($token);

        // Crée le compte du joueur
        $player = User::create([
            'name'     => session('invite_name'),
            'pseudo'   => session('invite_pseudo'),
            'email'    => filter_var(session('invite_destinataire'), FILTER_VALIDATE_EMAIL)
                            ? session('invite_destinataire')
                            : null,
            'telephone' => !filter_var(session('invite_destinataire'), FILTER_VALIDATE_EMAIL)
                            ? session('invite_destinataire')
                            : null,
            'password' => Hash::make(session('invite_password')),
            'role'     => 'player',
            'email_verified_at' => now(),
        ]);

        // Marque l'invitation comme utilisée et lie le joueur
        $this->invitationService->marquerUtilisee($invitation, $player);

        // Connecte le joueur automatiquement
        Auth::login($player);

        // Nettoie la session
        session()->forget([
            'invite_name',
            'invite_pseudo',
            'invite_destinataire',
            'invite_password',
            'invite_canal',
            'invite_token',
        ]);

        // Redirige directement vers l'environnement de l'invitation acceptée
        return redirect()->route('game.configure', $invitation->environment_id);
    }

    /**
     * L'invité demande un nouveau code OTP.
     */
    public function renvoyer(Request $request, string $token)
    {
        $this->invitationService->validerToken($token);

        $data = $request->validate([
            'destinataire' => 'required|string',
            'canal'        => 'required|in:email,sms,whatsapp',
        ]);

        $otp = $this->otpService->generer($data['destinataire'], $data['canal']);
        $this->notificationService->envoyerOtp($otp);

        return back()->with('success', 'Nouveau code envoyé !');
    }
}