<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Factura extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'pago_id',
        'vivienda_id',
        'folio',
        'archivo',
        'fecha_emision',
    ];

    protected $casts = [
        'fecha_emision' => 'datetime',
    ];

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class);
    }

    public function vivienda(): BelongsTo
    {
        return $this->belongsTo(Vivienda::class);
    }
}