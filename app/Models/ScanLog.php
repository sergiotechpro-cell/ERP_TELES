<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScanLog extends Model
{
    protected $fillable = [
        'order_id','serial_number_id','user_id','tipo','scanned_at','meta'
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'meta' => 'array',
    ];

    public function order() {
        return $this->belongsTo(Order::class);
    }
    public function serial() {
        return $this->belongsTo(SerialNumber::class, 'serial_number_id');
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
}
