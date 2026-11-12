<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reglamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'archivo',
        'version',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];
}