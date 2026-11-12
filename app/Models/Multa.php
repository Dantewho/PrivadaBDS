<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Multa extends Model
{
    use HasFactory;

    protected $fillable = [
        'vivienda_id',
        'usuario_id',
        'motivo_id',
        'monto_base',
        'incremento',
        'monto_final',
        'numero_reincidencia',
        'descripcion',
        'evidencia_path',
        'evidencia_nombre',
        'estado',
        'fecha',
    ];

    protected $casts = [
        'monto_base' => 'decimal:2',
        'incremento' => 'decimal:2',
        'monto_final' => 'decimal:2',
        'fecha' => 'datetime',
    ];

    public function vivienda(): BelongsTo
    {
        return $this->belongsTo(Vivienda::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function motivo(): BelongsTo
    {
        return $this->belongsTo(MotivoMulta::class);
    }

    public function apelaciones(): HasMany
    {
        return $this->hasMany(Apelacion::class);
    }

    public function adeudo(): HasOne
    {
        return $this->hasOne(Adeudo::class);
    }
}
