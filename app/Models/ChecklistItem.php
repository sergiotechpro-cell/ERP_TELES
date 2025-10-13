<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model {
  protected $fillable=['order_id','texto','completado','completed_at','completed_by'];
  protected $casts=['completado'=>'boolean','completed_at'=>'datetime'];
  public function order(){ return $this->belongsTo(Order::class); }
  public function user(){ return $this->belongsTo(User::class,'completed_by'); }
}
