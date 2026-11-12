<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\RestablecerContrasena;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'name',
    'email',
    'google_id',
    'password',
    'vivienda_id',
    'rol',
    'estado',
    'email_verified_at',
])]
#[Hidden([
    'password',
    'remember_token',
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new RestablecerContrasena($token));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function viviendaComoPropietario(): HasOne
    {
        return $this->hasOne(Vivienda::class, 'propietario_id');
    }

    public function esAdministrador(): bool
    {
        return $this->rol === 'administrador';
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'activo';
    }

    /**
     * Relación con la vivienda.
     */
    public function vivienda(): BelongsTo
    {
        return $this->belongsTo(Vivienda::class);
    }

    /**
     * Relación con los pagos realizados por el usuario.
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'usuario_id');
    }

    /**
     * Relación con las tarjetas del usuario.
     */
    public function tarjetas(): HasMany
    {
        return $this->hasMany(Tarjeta::class, 'usuario_id');
    }

    /**
     * Relación con las multas del usuario.
     */
    public function multas(): HasMany
    {
        return $this->hasMany(Multa::class, 'usuario_id');
    }

    /**
     * Relación con las apelaciones del usuario.
     */
    public function apelaciones(): HasMany
    {
        return $this->hasMany(Apelacion::class, 'usuario_id');
    }

    /**
     * Relación con los mensajes de apelaciones.
     */
    public function mensajesApelacion(): HasMany
    {
        return $this->hasMany(MensajeApelacion::class, 'usuario_id');
    }

    /**
     * Relación con las quejas del usuario.
     */
    public function quejas(): HasMany
    {
        return $this->hasMany(Queja::class, 'usuario_id');
    }

    /**
     * Relación con los mensajes de quejas.
     */
    public function mensajesQueja(): HasMany
    {
        return $this->hasMany(MensajeQueja::class, 'usuario_id');
    }

    /**
     * Relación con los anuncios publicados por el administrador.
     */
    public function anuncios(): HasMany
    {
        return $this->hasMany(Anuncio::class, 'administrador_id');
    }

    /**
     * Relación con las notificaciones del usuario.
     */
    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class, 'usuario_id');
    }

    /**
     * Relación con las solicitudes de documentos.
     */
    public function solicitudesDocumento(): HasMany
    {
        return $this->hasMany(SolicitudDocumento::class, 'usuario_id');
    }
}
