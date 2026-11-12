<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Queja extends Model
{
    use HasFactory;

    protected $fillable = [
        'vivienda_id',
        'vivienda_reportada_id',
        'usuario_id',
        'asunto',
        'descripcion',
        'estado',
    ];

    public function vivienda(): BelongsTo
    {
        return $this->belongsTo(Vivienda::class);
    }

    public function viviendaReportada(): BelongsTo
    {
        return $this->belongsTo(Vivienda::class, 'vivienda_reportada_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(MensajeQueja::class);
    }
}
