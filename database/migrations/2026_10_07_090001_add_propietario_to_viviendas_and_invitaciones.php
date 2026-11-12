<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('viviendas', function (Blueprint $table) {
            $table->foreignId('propietario_id')
                ->nullable()
                ->unique()
                ->constrained('users')
                ->nullOnDelete();
        });

        Schema::table('invitaciones', function (Blueprint $table) {
            $table->boolean('es_propietario')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('invitaciones', function (Blueprint $table) {
            $table->dropColumn('es_propietario');
        });

        Schema::table('viviendas', function (Blueprint $table) {
            $table->dropForeign(['propietario_id']);
            $table->dropUnique(['propietario_id']);
            $table->dropColumn('propietario_id');
        });
    }
};
