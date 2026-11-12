<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Adeudo extends Model
{
    use HasFactory;

    protected $fillable = [
        'vivienda_id',
        'tarjeta_id',
        'multa_id',
        'concepto',
        'periodo',
        'monto',
        'fecha_vencimiento',
        'estado',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_vencimiento' => 'date',
    ];

    public function vivienda(): BelongsTo
    {
        return $this->belongsTo(Vivienda::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    public function tarjeta(): BelongsTo
    {
        return $this->belongsTo(Tarjeta::class);
    }

    public function multa(): BelongsTo
    {
        return $this->belongsTo(Multa::class);
    }
}
