<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tarjeta extends Model
{
    use HasFactory;

    protected $fillable = [
        'vivienda_id',
        'usuario_id',
        'numero_tarjeta',
        'tipo',
        'precio',
        'estado',
        'fecha_activacion',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'fecha_activacion' => 'datetime',
    ];

    public function vivienda(): BelongsTo
    {
        return $this->belongsTo(Vivienda::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function accesos(): HasMany
    {
        return $this->hasMany(Acceso::class);
    }

    public function adeudos(): HasMany
    {
        return $this->hasMany(Adeudo::class);
    }
}
