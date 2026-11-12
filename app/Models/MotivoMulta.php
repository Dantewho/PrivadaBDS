<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MotivoMulta extends Model
{
    use HasFactory;

    protected $table = 'motivo_multas';

    protected $fillable = [
        'nombre',
        'descripcion',
        'monto_base',
        'incremento',
        'activo',
    ];

    protected $casts = [
        'monto_base' => 'decimal:2',
        'incremento' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function multas(): HasMany
    {
        return $this->hasMany(Multa::class, 'motivo_id');
    }
}
