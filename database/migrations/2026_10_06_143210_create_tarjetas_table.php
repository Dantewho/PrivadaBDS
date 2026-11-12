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
        Schema::create('tarjetas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vivienda_id')->constrained('viviendas')->restrictOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('numero_tarjeta', 100)->unique();
            $table->enum('tipo', ['vehicular', 'peatonal']);
            $table->decimal('precio', 10, 2);
            $table->string('estado', 30)->default('pendiente');
            $table->timestamp('fecha_activacion')->nullable();
            $table->timestamps();
            $table->index(['vivienda_id', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarjetas');
    }
};
