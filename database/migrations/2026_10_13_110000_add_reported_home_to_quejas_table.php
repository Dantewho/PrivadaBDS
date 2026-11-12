<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quejas', function (Blueprint $table) {
            $table->foreignId('vivienda_reportada_id')->nullable()->after('vivienda_id')
                ->constrained('viviendas')->nullOnDelete();
            $table->index(['vivienda_reportada_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::table('quejas', function (Blueprint $table) {
            $table->dropIndex(['vivienda_reportada_id', 'estado']);
            $table->dropConstrainedForeignId('vivienda_reportada_id');
        });
    }
};
