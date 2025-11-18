<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = ['nombre','direccion','lat','lng','parent_warehouse_id'];

    public function products() {
        return $this->belongsToMany(Product::class, 'warehouse_product')
            ->withPivot('stock')->withTimestamps();
    }

    public function warehouseProducts() {
        return $this->hasMany(WarehouseProduct::class);
    }

    // Relación con bodega padre
    public function parentWarehouse() {
        return $this->belongsTo(Warehouse::class, 'parent_warehouse_id');
    }

    // Relación con sub-bodegas
    public function subWarehouses() {
        return $this->hasMany(Warehouse::class, 'parent_warehouse_id');
    }

    // Verificar si es bodega principal
    public function isMainWarehouse() {
        return is_null($this->parent_warehouse_id);
    }
}
