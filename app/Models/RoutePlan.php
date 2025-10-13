<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RoutePlan extends Model {
  protected $fillable=['order_id','waypoints','polyline','distance_m','duration_s'];
  protected $casts=['waypoints'=>'array','polyline'=>'array'];
  public function order(){ return $this->belongsTo(Order::class); }
}
