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
        Schema::create('anuncios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('administrador_id')->constrained('users')->restrictOnDelete();
            $table->string('titulo', 150);
            $table->text('contenido');
            $table->timestamp('fecha_publicacion')->nullable();
            $table->string('estado', 30)->default('borrador');
            $table->timestamps();
            $table->index(['estado', 'fecha_publicacion']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anuncios');
    }
};
