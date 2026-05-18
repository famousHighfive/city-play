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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('game_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->enum('type_notification', [
                'bonne_reponse',
                'mauvaise_reponse',
                'indice',
                'solution',
                'pause',
                'fin_partie'
            ]);

            $table->text('contenu');

            $table->boolean('lu')->default(false);

            $table->timestamp('date_envoi');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
