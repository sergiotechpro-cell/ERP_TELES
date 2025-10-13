<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MessageLog extends Model {
  protected $fillable=['order_id','to','provider','body','status','meta'];
  protected $casts=['meta'=>'array'];
  public function order(){ return $this->belongsTo(\App\Models\Order::class); }
}
