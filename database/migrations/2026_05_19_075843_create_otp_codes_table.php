<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();

            // À qui appartient ce code (email ou numéro de téléphone)
            $table->string('destinataire');

            // Par quel canal on a envoyé le code
            $table->enum('canal', ['email', 'sms', 'whatsapp']);

            // Le code à 6 chiffres
            $table->string('code', 6);

            // Nombre de tentatives (max 3)
            $table->unsignedTinyInteger('tentatives')->default(0);

            // Le code expire après 10 minutes
            $table->timestamp('expires_at');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
