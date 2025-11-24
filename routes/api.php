<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourierController;
use App\Http\Controllers\Api\TrackingController;
use App\Models\SerialNumber;
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
        
        // Tracking en tiempo real
        Route::post('/tracking/update', [TrackingController::class, 'updateLocation']);
        Route::post('/tracking/stop', [TrackingController::class, 'stopTracking']);
    });
});

// API para tracking (admin)
Route::middleware('auth:sanctum')->prefix('tracking')->group(function () {
    Route::get('/drivers', [TrackingController::class, 'getAllActiveDrivers']);
    Route::get('/drivers/{driverId}', [TrackingController::class, 'getDriverLocation']);
});
