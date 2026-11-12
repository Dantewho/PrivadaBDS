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
        Schema::create('mensajes_queja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queja_id')->constrained('quejas')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->restrictOnDelete();
            $table->text('mensaje');
            $table->timestamp('leido_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['queja_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mensajes_queja');
    }
};
