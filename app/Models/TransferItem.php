<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TransferItem extends Model {
  protected $fillable=['transfer_id','product_id','cantidad','seriales'];
  protected $casts=['seriales'=>'array'];
  public function transfer(){ return $this->belongsTo(Transfer::class); }
  public function product(){ return $this->belongsTo(Product::class); }
}
