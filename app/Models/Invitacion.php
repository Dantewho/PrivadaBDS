<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invitacion extends Model
{
    use HasFactory;

    protected $table = 'invitaciones';

    protected $fillable = [
        'vivienda_id',
        'email',
        'es_propietario',
        'token',
        'estado',
        'expira_at',
    ];

    protected $casts = [
        'expira_at' => 'datetime',
        'es_propietario' => 'boolean',
    ];

    public function vivienda(): BelongsTo
    {
        return $this->belongsTo(Vivienda::class);
    }
}
