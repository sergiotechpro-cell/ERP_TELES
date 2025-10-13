<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryAssignment extends Model
{
    protected $fillable = [
        'order_id','courier_id','asignado_at','salida_at','entregado_at','estado'
    ];

    protected $casts = [
        'asignado_at' => 'datetime',
        'salida_at' => 'datetime',
        'entregado_at' => 'datetime',
    ];

    public function order() {
        return $this->belongsTo(Order::class);
    }

    public function courier() {
        return $this->belongsTo(User::class, 'courier_id');
    }
}
