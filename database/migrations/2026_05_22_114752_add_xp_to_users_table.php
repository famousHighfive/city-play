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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('xp')->default(0)->after('role');  // stocke le niveau actuel du joueur (calculé à partir de l'XP).
            $table->integer('level')->default(1)->after('xp'); // stocke le niveau actuel du joueur (calculé à partir de l'XP).
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['xp', 'level']);
        });
    }
};
