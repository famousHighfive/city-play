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
        Schema::create('places', function (Blueprint $table) {
            $table->id();

            $table->foreignId('environment_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('nom');

            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);

            $table->string('description', 500)->nullable();

            $table->integer('rayon_validation')->default(30);

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
