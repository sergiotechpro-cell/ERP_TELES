<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['descripcion','costo_unitario','precio_venta'];

    public function warehouses() {
        return $this->belongsToMany(Warehouse::class, 'warehouse_product')
            ->withPivot('stock')->withTimestamps();
    }

    public function warehouseProducts() {
        return $this->hasMany(WarehouseProduct::class);
    }

    public function serials() {
        return $this->hasManyThrough(SerialNumber::class, WarehouseProduct::class,
            'product_id', 'warehouse_product_id', 'id', 'id');
    }
}
