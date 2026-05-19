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
        Schema::create('enigmes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('place_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('niveau', ['1', '2', '3', 'enfant']);

            $table->text('texte'); // La question/énigme à afficher

            $table->text('solution'); // La réponse textuelle ou explication de la solution
            $table->text('indice_1')->nullable(); // Le premier indice à afficher
            $table->text('indice_2')->nullable(); // Un deuxième indice si besoin

            $table->string('image', 2048)->nullable();

            $table->boolean('actif')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enigmes');
    }
};
