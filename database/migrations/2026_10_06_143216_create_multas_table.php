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
        Schema::create('multas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vivienda_id')->constrained('viviendas')->restrictOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('motivo_id')->constrained('motivo_multas')->restrictOnDelete();
            $table->decimal('monto_base', 10, 2);
            $table->decimal('incremento', 5, 2)->default(50.00);
            $table->decimal('monto_final', 10, 2);
            $table->unsignedInteger('numero_reincidencia')->default(1);
            $table->text('descripcion')->nullable();
            $table->string('estado', 30)->default('pendiente');
            $table->timestamp('fecha')->useCurrent();
            $table->timestamps();
            $table->index(['vivienda_id', 'motivo_id']);
            $table->index(['vivienda_id', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('multas');
    }
};
