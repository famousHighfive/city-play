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
        Schema::create('games', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('environment_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->timestamp('date_debut');

            $table->timestamp('date_fin')->nullable();

            $table->enum('statut', ['en_cours', 'pause', 'terminee', 'abandonnee']);

            $table->enum('mode_jeu', ['equipe', 'challenge'])->default('equipe');

            $table->integer('nb_membres');

            $table->json('participants')->nullable();

            $table->string('challenger_email')->nullable();

            $table->integer('duree_prevue');

            $table->integer('duree_restante')->nullable();

            $table->enum('moyen_locomotion', ['pied', 'velo', 'voiture']);

            $table->enum('niveau_difficulte', ['1', '2', '3', 'enfant']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
