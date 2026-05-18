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
        Schema::create('enigme_games', function (Blueprint $table) {
            $table->foreignId('game_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('enigme_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->integer('ordre');

            $table->enum('statut', [
                'a_faire',
                'en_cours',
                'resolue',
                'passee',
                'non_resolue'
            ]);

            $table->timestamp('date_resolution')->nullable();

            $table->integer('nb_indices_demandes')->default(0);

            $table->boolean('solution_affichee')->default(false);

            $table->primary(['game_id', 'enigme_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enigme_games');
    }
};
