<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adeudos', function (Blueprint $table) {
            $table->foreignId('tarjeta_id')->nullable()->after('vivienda_id')->constrained('tarjetas')->nullOnDelete();
            $table->foreignId('multa_id')->nullable()->after('tarjeta_id')->constrained('multas')->nullOnDelete();
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->string('estado', 24)->default('pendiente')->after('metodo')->index();
        });

        Schema::table('mensajes_queja', function (Blueprint $table) {
            $table->boolean('ofensivo')->default(false)->after('mensaje');
            $table->foreignId('moderado_por')->nullable()->after('ofensivo')->constrained('users')->nullOnDelete();
            $table->timestamp('moderado_at')->nullable()->after('moderado_por');
        });
    }

    public function down(): void
    {
        Schema::table('mensajes_queja', function (Blueprint $table) {
            $table->dropConstrainedForeignId('moderado_por');
            $table->dropColumn(['ofensivo', 'moderado_at']);
        });
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropIndex(['estado']);
            $table->dropColumn('estado');
        });
        Schema::table('adeudos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('multa_id');
            $table->dropConstrainedForeignId('tarjeta_id');
        });
    }
};
