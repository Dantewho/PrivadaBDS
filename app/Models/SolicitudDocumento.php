<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudDocumento extends Model
{
    use HasFactory;

    protected $fillable = [
        'documento_id',
        'usuario_id',
        'estado',
        'observaciones',
        'archivo',
        'solicitado_at',
        'resuelto_at',
    ];

    protected $casts = [
        'solicitado_at' => 'datetime',
        'resuelto_at' => 'datetime',
    ];

    public function documento(): BelongsTo
    {
        return $this->belongsTo(Documento::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
