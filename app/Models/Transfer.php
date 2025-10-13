<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model {
  protected $fillable = ['warehouse_from_id','warehouse_to_id','created_by','estado','enviado_at','recibido_at','nota'];
  protected $casts = ['enviado_at'=>'datetime','recibido_at'=>'datetime'];

  public function from(){ return $this->belongsTo(Warehouse::class,'warehouse_from_id'); }
  public function to(){ return $this->belongsTo(Warehouse::class,'warehouse_to_id'); }
  public function items(){ return $this->hasMany(TransferItem::class); }
  public function creador(){ return $this->belongsTo(User::class,'created_by'); }
}
