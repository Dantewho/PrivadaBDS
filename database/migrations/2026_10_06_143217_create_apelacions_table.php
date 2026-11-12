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
        Schema::create('apelacions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('multa_id')->constrained('multas')->restrictOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->restrictOnDelete();
            $table->text('motivo');
            $table->string('estado', 30)->default('pendiente');
            $table->timestamps();
            $table->index(['usuario_id', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apelacions');
    }
};
