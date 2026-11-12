<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MensajeQueja extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'mensajes_queja';

    protected $fillable = [
        'queja_id',
        'usuario_id',
        'mensaje',
        'ofensivo',
        'moderado_por',
        'moderado_at',
        'leido_at',
    ];

    protected $casts = [
        'leido_at' => 'datetime',
        'ofensivo' => 'boolean',
        'moderado_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function queja(): BelongsTo
    {
        return $this->belongsTo(Queja::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function moderador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderado_por');
    }

    public function autorEtiqueta(): string
    {
        $user=$this->usuario;
        if(!$user || $user->rol!=='administrador')return $user?->name ?? 'Usuario';
        $parts=preg_split('/\\s+/',trim($user->name)) ?: [];
        $firstNames=implode(' ',array_slice($parts,0,max(1,count($parts)-2)));
        return 'Administrador ('.$firstNames.($user->vivienda?->numero?' #'.$user->vivienda->numero:'').')';
    }
}
