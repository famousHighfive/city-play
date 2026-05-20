<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Environment;
use App\Services\InvitationService;
use App\Services\NotificationService;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvitationController extends Controller
{
    public function __construct(
        private InvitationService  $invitationService,
        private NotificationService $notificationService,
        private OtpService         $otpService,
    ) {}

    public function index(Environment $environment): Response
{
    return Inertia::render('Admin/Invitation/Index', [
        'environment' => $environment,
        'invitations' => $environment->invitations()
                                     ->with('player')
                                     ->latest()
                                     ->get(),
    ]);
}

    /**
     * L'admin envoie une invitation.
     */
    public function store(Request $request, Environment $environment)
    {
        $data = $request->validate([
            'destinataire' => 'required|string',
            'canal'        => 'required|in:email,sms,whatsapp',
        ]);

        // 1. Créer l'invitation en base
        $invitation = $this->invitationService->creer(
            $environment,
            $data['destinataire'],
            $data['canal']
        );

        // 2. Construire le lien
        $lien = route('invitation.show', ['token' => $invitation->token]);

        // 3. Envoyer le lien au destinataire
        $this->notificationService->envoyerInvitation($invitation, $lien);

        return back()->with('success', 'Invitation envoyée avec succès !');
    }

    /**
     * L'invité clique sur le lien — on affiche le formulaire d'inscription.
     */
    public function show(string $token): Response
    {
        // Valide le token (expire, déjà utilisé, inexistant)
        $invitation = $this->invitationService->validerToken($token);

        return Inertia::render('Admin/Invitation/RegisterPage', [
            'token'        => $token,
            'canal'        => $invitation->canal,
            'environment'  => $invitation->environment,
        ]);
    }

    /**
     * L'invité soumet le formulaire d'inscription.
     */
    public function register(Request $request, string $token)
    {
        // Revalide le token à chaque étape
        $invitation = $this->invitationService->validerToken($token);

        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'pseudo'       => 'required|string|max:255|unique:users,pseudo',
            'destinataire' => 'required|string',
            'password'     => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        // Stocke les infos en session pour les récupérer après l'OTP
        session([
            'invite_name'         => $data['name'],
            'invite_pseudo'       => $data['pseudo'],
            'invite_destinataire' => $data['destinataire'],
            'invite_password'     => $data['password'],
            'invite_canal'        => $invitation->canal,
            'invite_token'        => $token,
        ]);

        // Génère et envoie le code OTP
        $otp = $this->otpService->generer($data['destinataire'], $invitation->canal);
        $this->notificationService->envoyerOtp($otp);

        // Redirection GET vers une URL dédiée : sinon Inertia laisse l'adresse sur …/register
        // (POST seulement) et un rafraîchissement ou une navigation suivante provoque une 405.
        return redirect()->route('invitation.otp.show', ['token' => $token]);
    }

    /**
     * Affiche la saisie OTP après inscription (URL en GET, rafraîchissable).
     */
    public function showOtp(string $token): Response|RedirectResponse
    {
        $invitation = $this->invitationService->validerToken($token);

        if (!session()->has('invite_destinataire')) {
            return redirect()->route('invitation.show', ['token' => $token]);
        }

        return Inertia::render('Admin/Invitation/OtpPage', [
            'token'        => $token,
            'destinataire' => session('invite_destinataire'),
            'canal'        => $invitation->canal,
        ]);
    }

    /**
     * Secours si l'URL reste sur /register (comportement Inertia après POST) : GET autorisé.
     */
    public function registerUrlFallback(string $token): RedirectResponse
    {
        $this->invitationService->validerToken($token);

        if (session()->has('invite_destinataire')) {
            return redirect()->route('invitation.otp.show', ['token' => $token]);
        }

        return redirect()->route('invitation.show', ['token' => $token]);
    }
}