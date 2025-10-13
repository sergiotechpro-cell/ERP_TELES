<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = ['nombre','direccion','lat','lng'];

    public function products() {
        return $this->belongsToMany(Product::class, 'warehouse_product')
            ->withPivot('stock')->withTimestamps();
    }

    public function warehouseProducts() {
        return $this->hasMany(WarehouseProduct::class);
    }
}
