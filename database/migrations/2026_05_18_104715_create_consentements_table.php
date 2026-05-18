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
        Schema::create('consentements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->boolean('accepte_cgu');
            $table->boolean('accepte_politique');

            $table->string('version_cgu')->nullable();
            $table->string('version_politique')->nullable();

            $table->ipAddress('ip_address')->nullable();

            $table->timestamp('date_acceptation');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consentements');
    }
};
