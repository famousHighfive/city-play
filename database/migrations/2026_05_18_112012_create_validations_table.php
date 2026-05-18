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
        Schema::create('validations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('game_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('place_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);

            $table->decimal('distance_metres', 10, 2)->nullable();

            $table->boolean('bonne_reponse');

            $table->timestamp('date_validation');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('validations');
    }
};
