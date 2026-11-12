<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('motivo_multas', function (Blueprint $table) {
            $table->decimal('incremento', 5, 2)->default(50)->after('monto_base');
        });

        Schema::table('multas', function (Blueprint $table) {
            $table->string('evidencia_path')->nullable()->after('descripcion');
            $table->string('evidencia_nombre')->nullable()->after('evidencia_path');
        });

        Schema::table('apelacions', function (Blueprint $table) {
            $table->decimal('monto_resuelto', 10, 2)->nullable()->after('estado');
            $table->text('resolucion')->nullable()->after('monto_resuelto');
            $table->timestamp('resuelta_at')->nullable()->after('resolucion');
        });

        Schema::table('mensajes_apelacion', function (Blueprint $table) {
            $table->string('adjunto_path')->nullable()->after('mensaje');
            $table->string('adjunto_nombre')->nullable()->after('adjunto_path');
            $table->string('adjunto_mime', 100)->nullable()->after('adjunto_nombre');
        });
    }

    public function down(): void
    {
        Schema::table('mensajes_apelacion', function (Blueprint $table) {
            $table->dropColumn(['adjunto_path', 'adjunto_nombre', 'adjunto_mime']);
        });
        Schema::table('apelacions', function (Blueprint $table) {
            $table->dropColumn(['monto_resuelto', 'resolucion', 'resuelta_at']);
        });
        Schema::table('multas', function (Blueprint $table) {
            $table->dropColumn(['evidencia_path', 'evidencia_nombre']);
        });
        Schema::table('motivo_multas', function (Blueprint $table) {
            $table->dropColumn('incremento');
        });
    }
};
