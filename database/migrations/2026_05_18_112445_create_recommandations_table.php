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
        Schema::create('recommandations', function (Blueprint $table) {
            $table->id();

             $table->foreignId('environment_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->enum('type', [
                'restaurant',
                'boutique',
                'avis',
                'amelioration'
            ]);

            $table->string('titre')->nullable();

            $table->text('contenu');

            $table->string('lien_externe', 2048)->nullable();

            $table->boolean('actif')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recommandations');
    }
};
