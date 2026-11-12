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
        Schema::create('invitaciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vivienda_id')
                ->constrained('viviendas')
                ->cascadeOnDelete();

            $table->string('email');

            $table->string('token')->unique();

            $table->enum('estado', [
                'pendiente',
                'aceptada',
                'expirada',
                'cancelada',
            ])->default('pendiente');

            $table->dateTime('expira_at');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitaciones');
    }
};
