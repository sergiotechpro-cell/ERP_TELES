<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'cantidad',
        'precio_unitario',
        'costo_unitario',
        'seriales',
    ];

    protected $casts = [
        'seriales' => 'array',
    ];

    public function sale() {
        return $this->belongsTo(Sale::class);
    }
    
    public function product() {
        return $this->belongsTo(Product::class);
    }
}
