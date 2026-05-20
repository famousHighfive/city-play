<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('games')) {
            return;
        }

        DB::statement("ALTER TABLE games MODIFY niveau_difficulte ENUM('1', '2', '3', 'enfant') NOT NULL");
    }

    public function down(): void
    {
        if (! Schema::hasTable('games')) {
            return;
        }

        DB::statement('ALTER TABLE games MODIFY niveau_difficulte INT NOT NULL');
    }
};
