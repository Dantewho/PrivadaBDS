<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MensajeApelacion extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'mensajes_apelacion';

    protected $fillable = [
        'apelacion_id',
        'usuario_id',
        'mensaje',
        'adjunto_path',
        'adjunto_nombre',
        'adjunto_mime',
        'leido_at',
    ];

    protected $casts = [
        'leido_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function apelacion(): BelongsTo
    {
        return $this->belongsTo(Apelacion::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function autorEtiqueta(): string
    {
        $user = $this->usuario;
        if (!$user || $user->rol !== 'administrador') return $user?->name ?? 'Usuario';
        $parts = preg_split('/\\s+/', trim($user->name)) ?: [];
        $firstNames = implode(' ', array_slice($parts, 0, max(1, count($parts) - 2)));
        return 'Administrador ('.$firstNames.($user->vivienda?->numero ? ' #'.$user->vivienda->numero : '').')';
    }
}
