<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vivienda extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero',
        'propietario_id',
        'direccion',
        'estado',
    ];

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function propietario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'propietario_id');
    }

    public function invitaciones(): HasMany
    {
        return $this->hasMany(Invitacion::class);
    }

    public function tarjetas(): HasMany
    {
        return $this->hasMany(Tarjeta::class);
    }

    public function accesos(): HasMany
    {
        return $this->hasMany(Acceso::class);
    }

    public function adeudos(): HasMany
    {
        return $this->hasMany(Adeudo::class);
    }

    public function multas(): HasMany
    {
        return $this->hasMany(Multa::class);
    }

    public function quejas(): HasMany
    {
        return $this->hasMany(Queja::class);
    }

    public function quejasRecibidas(): HasMany
    {
        return $this->hasMany(Queja::class, 'vivienda_reportada_id');
    }

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class);
    }
}
