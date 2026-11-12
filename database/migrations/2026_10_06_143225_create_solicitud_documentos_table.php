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
        Schema::create('solicitud_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_id')->constrained('documentos')->restrictOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->restrictOnDelete();
            $table->string('estado', 30)->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->string('archivo')->nullable();
            $table->timestamp('solicitado_at')->useCurrent();
            $table->timestamp('resuelto_at')->nullable();
            $table->timestamps();

            $table->index(['usuario_id', 'estado']);
            $table->index(['documento_id', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud_documentos');
    }
};
