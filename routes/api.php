<?php

use App\Models\SerialNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/seriales', function(Request $r){
    $pid = $r->query('product_id');
    if(!$pid) return response()->json([]);
    return SerialNumber::whereHas('warehouseProduct', fn($q)=>$q->where('product_id',$pid))
        ->limit(200)
        ->get(['id','numero_serie']);
});
