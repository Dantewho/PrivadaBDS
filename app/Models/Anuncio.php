<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Anuncio extends Model
{
    use HasFactory;

    protected $fillable = [
        'administrador_id',
        'titulo',
        'contenido',
        'fecha_publicacion',
        'visible_desde',
        'visible_hasta',
        'estado',
    ];

    protected $casts = [
        'fecha_publicacion' => 'datetime',
        'visible_desde' => 'date',
        'visible_hasta' => 'date',
    ];

    public function administrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'administrador_id');
    }

    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class);
    }

    public function envios(): HasMany
    {
        return $this->hasMany(AnuncioEnvio::class);
    }
}
