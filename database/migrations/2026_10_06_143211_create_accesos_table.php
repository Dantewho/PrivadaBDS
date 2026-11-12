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
        Schema::create('accesos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarjeta_id')->nullable()->constrained('tarjetas')->nullOnDelete();
            $table->foreignId('vivienda_id')->constrained('viviendas')->restrictOnDelete();
            $table->string('puerta', 50);
            $table->enum('movimiento', ['entrada', 'salida']);
            $table->timestamp('fecha_hora')->useCurrent();
            $table->index(['tarjeta_id', 'fecha_hora']);
            $table->index(['vivienda_id', 'fecha_hora']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accesos');
    }
};
