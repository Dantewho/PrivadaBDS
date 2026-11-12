<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Apelacion extends Model
{
    use HasFactory;

    protected $fillable = [
        'multa_id',
        'usuario_id',
        'motivo',
        'estado',
        'monto_resuelto',
        'resolucion',
        'resuelta_at',
    ];

    protected $casts = [
        'monto_resuelto' => 'decimal:2',
        'resuelta_at' => 'datetime',
    ];

    public function multa(): BelongsTo
    {
        return $this->belongsTo(Multa::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(MensajeApelacion::class);
    }
}
