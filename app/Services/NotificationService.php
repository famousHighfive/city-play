<?php

namespace App\Services;

use App\Mail\GameInvitationMail;
use App\Models\Invitation;
use App\Models\OtpCode;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function envoyerInvitation(Invitation $invitation, string $lien): void
    {
        match ($invitation->canal) {
            'email'    => Mail::to($invitation->destinataire)
                ->send(new GameInvitationMail(
                    $lien,
                    $invitation->environment->nom
                )),
            'sms'      => $this->envoyerSms(
                $invitation->destinataire,
                "Rejoins la partie ! Lien valable 5h : {$lien}"
            ),
            'whatsapp' => $this->envoyerWhatsapp(
                $invitation->destinataire,
                "Rejoins la partie ! Lien valable 5h : {$lien}"
            ),
        };
    }

    public function envoyerOtp(OtpCode $otp): void
    {
        $message = "Ton code de vérification est : {$otp->code}. Valable 10 minutes.";

        match ($otp->canal) {
            'email'    => $this->envoyerEmail($otp->destinataire, 'Ton code de vérification', $message),
            'sms'      => $this->envoyerSms($otp->destinataire, $message),
            'whatsapp' => $this->envoyerWhatsapp($otp->destinataire, $message),
        };
    }

    private function envoyerEmail(string $destinataire, string $sujet, string $message): void
    {
        // Pour l'OTP on garde le log pour l'instant
        // on créera un OtpMail ensuite
        Log::info("EMAIL à {$destinataire} — {$sujet} : {$message}");
    }

    private function envoyerSms(string $destinataire, string $message): void
    {
        Log::info("SMS à {$destinataire} : {$message}");
    }

    private function envoyerWhatsapp(string $destinataire, string $message): void
    {
        Log::info("WHATSAPP à {$destinataire} : {$message}");
    }
}
