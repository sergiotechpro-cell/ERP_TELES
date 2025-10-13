<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id','product_id','warehouse_id','cantidad','precio_unitario','costo_unitario'
    ];

    public function order() {
        return $this->belongsTo(Order::class);
    }
    public function product() {
        return $this->belongsTo(Product::class);
    }
    public function warehouse() {
        return $this->belongsTo(Warehouse::class);
    }
}
