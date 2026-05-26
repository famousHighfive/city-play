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
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('environment_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->enum('canal', ['email', 'sms', 'whatsapp']);

            $table->string('destinataire');

            $table->foreignId('player_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('token')->unique();

            $table->enum('statut', ['pending', 'used', 'expired'])
                  ->default('pending');

            $table->timestamp('expires_at');

            $table->timestamp('used_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
