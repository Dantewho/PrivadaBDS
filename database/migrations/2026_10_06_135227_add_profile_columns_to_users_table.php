<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->unique();
            $table->foreignId('vivienda_id')
                ->nullable()
                ->constrained('viviendas')
                ->nullOnDelete();

            $table->enum('rol', ['administrador', 'residente'])
                ->default('residente');

            $table->enum('estado', ['activo', 'inactivo'])
                ->default('activo');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['vivienda_id']);
            $table->dropUnique(['google_id']);
            $table->dropColumn(['google_id', 'vivienda_id', 'rol', 'estado']);
        });
    }
};
