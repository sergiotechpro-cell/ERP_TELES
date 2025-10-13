<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id','forma_pago','monto','estado','held_by','reportado_at','entregado_caja_at'
    ];

    protected $casts = [
        'reportado_at' => 'datetime',
        'entregado_caja_at' => 'datetime',
    ];

    public function order() {
        return $this->belongsTo(Order::class);
    }
    public function holder() {
        return $this->belongsTo(User::class, 'held_by');
    }
}
