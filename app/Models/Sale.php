<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model {
  protected $fillable=['customer_id','user_id','subtotal','envio','total','status'];
  public function items(){ return $this->hasMany(SaleItem::class); }
  public function customer(){ return $this->belongsTo(Customer::class); }
  public function user(){ return $this->belongsTo(User::class); }
}
