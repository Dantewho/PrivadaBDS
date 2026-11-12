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
        Schema::create('anuncio_envios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anuncio_id')->constrained('anuncios')->cascadeOnDelete();
            $table->string('canal', 30)->default('whatsapp');
            $table->string('estado', 30)->default('pendiente');
            $table->string('grupo_destino')->nullable();
            $table->string('external_id')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('enviado_at')->nullable();
            $table->timestamps();

            $table->index(['canal', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anuncio_envios');
    }
};
