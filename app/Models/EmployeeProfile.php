<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeProfile extends Model
{
    protected $fillable = ['user_id','telefono','direccion'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
