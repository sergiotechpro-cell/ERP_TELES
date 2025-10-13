<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SerialNumber extends Model
{
    protected $fillable = ['warehouse_product_id','numero_serie','estado'];

    public function warehouseProduct() {
        return $this->belongsTo(WarehouseProduct::class);
    }
}
