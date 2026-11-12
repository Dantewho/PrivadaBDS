<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anuncios', function (Blueprint $table) {
            $table->date('visible_desde')->nullable()->after('fecha_publicacion');
            $table->date('visible_hasta')->nullable()->after('visible_desde');
        });

        DB::table('anuncios')->whereNotNull('fecha_publicacion')->update([
            'visible_desde' => DB::raw('DATE(fecha_publicacion)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('anuncios', function (Blueprint $table) {
            $table->dropColumn(['visible_desde', 'visible_hasta']);
        });
    }
};

