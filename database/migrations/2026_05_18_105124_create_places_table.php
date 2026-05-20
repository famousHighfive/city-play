<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('places', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relation environnement / ville
            |--------------------------------------------------------------------------
            */
            $table->foreignId('environment_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Informations principales
            |--------------------------------------------------------------------------
            */
            $table->string('nom', 150);

            $table->text('description')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Coordonnées GPS
            |--------------------------------------------------------------------------
            */
            $table->decimal('latitude', 10, 7);

            $table->decimal('longitude', 10, 7);

            /*
            |--------------------------------------------------------------------------
            | Rayon de validation GPS
            | Distance max en mètres
            |--------------------------------------------------------------------------
            */
            $table->integer('rayon_validation')
                ->default(30);

            /*
            |--------------------------------------------------------------------------
            | Classement et logique du moteur

            1. ordre ->Permet à la mairie de dire :

            1 → lieu important
            2 → ensuite celui-ci
            3 → etc

            popularite -> Permet au moteur de privilégier :

            les meilleurs lieux
            les lieux iconiques

            |--------------------------------------------------------------------------
            */

            // Ordre manuel défini par la mairie
            $table->integer('ordre')
                ->default(0);

            // Popularité du lieu
            // 1 = faible
            // 5 = incontournable
            $table->tinyInteger('popularite')
                ->default(1);


            /*
            |--------------------------------------------------------------------------
            | Etat du lieu
            |--------------------------------------------------------------------------
            */

            // Permet de masquer un lieu sans le supprimer
            $table->boolean('is_active')
                ->default(true);

            /*
            |--------------------------------------------------------------------------
            | Médias
            |--------------------------------------------------------------------------
            */

            // Image principale (optionnel)
            $table->string('image_principale')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('places');
    }
};