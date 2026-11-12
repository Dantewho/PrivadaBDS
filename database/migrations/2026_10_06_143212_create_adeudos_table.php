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
        Schema::create('adeudos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vivienda_id')->constrained('viviendas')->restrictOnDelete();
            $table->string('concepto');
            $table->string('periodo', 20);
            $table->decimal('monto', 10, 2);
            $table->date('fecha_vencimiento');
            $table->string('estado', 30)->default('pendiente');
            $table->timestamps();
            $table->index(['vivienda_id', 'estado']);
            $table->unique(['vivienda_id', 'concepto', 'periodo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adeudos');
    }
};
