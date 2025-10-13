<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarrantyClaim extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'serial_number_id',
        'motivo',
        'condicion',
        'status',
        'fecha_compra', // <-- importante
    ];

    protected $casts = [
        'fecha_compra' => 'date', // <-- importante
    ];

    protected $attributes = [
        'status' => 'abierta',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function serialNumber()
    {
        return $this->belongsTo(SerialNumber::class, 'serial_number_id');
    }
}
