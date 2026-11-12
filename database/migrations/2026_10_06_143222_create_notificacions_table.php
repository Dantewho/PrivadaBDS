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
        Schema::create('notificacions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('anuncio_id')->nullable()->constrained('anuncios')->nullOnDelete();
            $table->string('titulo', 150);
            $table->text('mensaje');
            $table->timestamp('leida_at')->nullable();
            $table->timestamps();
            $table->index(['usuario_id', 'leida_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificacions');
    }
};
