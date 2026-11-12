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
        Schema::create('mensajes_apelacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apelacion_id')->constrained('apelacions')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->restrictOnDelete();
            $table->text('mensaje');
            $table->timestamp('leido_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['apelacion_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mensajes_apelacion');
    }
};
