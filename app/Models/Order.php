<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'customer_id','created_by','estado','direccion_entrega','lat','lng','costo_envio','ruta_sugerida'
    ];

    protected $casts = [
        'ruta_sugerida' => 'array',
    ];

    public function customer() {
        return $this->belongsTo(Customer::class);
    }

    public function items() {
        return $this->hasMany(OrderItem::class);
    }

    public function payments() {
        return $this->hasMany(Payment::class);
    }

    public function assignment() {
        return $this->hasOne(DeliveryAssignment::class);
    }

    public function routePlan() {
        return $this->hasOne(RoutePlan::class);
    }

    public function checklistItems() {
        return $this->hasMany(ChecklistItem::class);
    }
}
