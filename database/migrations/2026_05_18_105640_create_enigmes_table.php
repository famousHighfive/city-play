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

            $table->text('texte');

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
