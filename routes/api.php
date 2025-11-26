<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourierController;
use App\Models\SerialNumber;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rutas públicas
Route::get('/seriales', function(Request $r){
    $pid = $r->query('product_id');
    if(!$pid) return response()->json([]);
    return SerialNumber::whereHas('warehouseProduct', fn($q)=>$q->where('product_id',$pid))
        ->limit(200)
        ->get(['id','numero_serie']);
});

// Obtener items de un pedido con sus números de serie
Route::get('/pedidos/{order}/items', function(Order $order){
    $items = $order->items()
        ->with('product:id,descripcion')
        ->get(['id', 'order_id', 'product_id', 'cantidad', 'seriales'])
        ->map(function($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->descripcion ?? '',
                'cantidad' => $item->cantidad,
                'seriales' => $item->seriales ?? [],
            ];
        });
    
    return response()->json($items);
});

// API para la app del chofer
Route::prefix('courier')->group(function () {
    // Rutas públicas
    Route::post('/login', [AuthController::class, 'login']);
    
    // Rutas protegidas (requieren autenticación)
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);
        
        // Assignments
        Route::get('/assignments', [CourierController::class, 'assignments']);
        Route::get('/assignments/{assignment}', [CourierController::class, 'showAssignment']);
        Route::post('/assignments/{assignment}/start', [CourierController::class, 'startAssignment']);
        Route::post('/assignments/{assignment}/complete', [CourierController::class, 'completeAssignment']);
        
        // Sales (ventas brutas)
        Route::get('/sales', [CourierController::class, 'sales']);
    });
});
