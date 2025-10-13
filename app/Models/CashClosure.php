<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashClosure extends Model
{
    protected $fillable = [
        'tipo','fecha_corte','total_efectivo','total_tarjeta','total_transferencia','resumen'
    ];

    protected $casts = [
        'fecha_corte' => 'date',
        'resumen' => 'array',
    ];
}
