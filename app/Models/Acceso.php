<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Acceso extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'tarjeta_id',
        'vivienda_id',
        'puerta',
        'movimiento',
        'fecha_hora',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
    ];

    public function tarjeta(): BelongsTo
    {
        return $this->belongsTo(Tarjeta::class);
    }

    public function vivienda(): BelongsTo
    {
        return $this->belongsTo(Vivienda::class);
    }
}