<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DeliverySchedule extends Model {
  protected $fillable=['order_id','courier_id','fecha','hora','titulo','meta'];
  protected $casts=['fecha'=>'date','meta'=>'array'];
  public function order(){ return $this->belongsTo(Order::class); }
  public function courier(){ return $this->belongsTo(User::class,'courier_id'); }
}
