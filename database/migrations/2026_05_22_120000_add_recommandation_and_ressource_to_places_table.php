<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('places', function (Blueprint $table) {
            if (! Schema::hasColumn('places', 'recommandation')) {
                $table->json('recommandation')->nullable()->after('description');
            }
            if (! Schema::hasColumn('places', 'ressource')) {
                $table->string('ressource', 30)->nullable()->after('recommandation');
            }
        });
    }

    public function down(): void
    {
        Schema::table('places', function (Blueprint $table) {
            $table->dropColumn(['recommandation', 'ressource']);
        });
    }
};
