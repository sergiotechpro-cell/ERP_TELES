<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    DashboardController,
    InventoryController,
    OrderController,
    DeliveryController,
    FinanceController,
    EmployeeController,
    ScheduleController,
    RouteController,
    PosController,
    RoleController
};

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Rutas del ERP SRDigitalPro.
| Requieren autenticación para acceder a los módulos.
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => redirect()->route('dashboard'));

// Rutas de autenticación de Breeze (login, logout, register, etc.)
require __DIR__ . '/auth.php';

Route::middleware(['auth'])->group(function () {

    // ================== DASHBOARD ==================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ================== INVENTARIO ==================
    // NOTA: Las rutas específicas deben ir ANTES del resource route para evitar conflictos
    
    // Gestión de bodegas (rutas específicas primero)
    Route::get('/inventario/warehouses', [InventoryController::class, 'indexWarehouses'])
        ->name('inventario.warehouses.index');
    Route::get('/inventario/warehouses/create', [InventoryController::class, 'createWarehouse'])
        ->name('inventario.warehouses.create');
    Route::post('/inventario/warehouses', [InventoryController::class, 'storeWarehouse'])
        ->name('inventario.warehouses.store');
    Route::get('/inventario/warehouses/{warehouse}/edit', [InventoryController::class, 'editWarehouse'])
        ->name('inventario.warehouses.edit');
    Route::put('/inventario/warehouses/{warehouse}', [InventoryController::class, 'updateWarehouse'])
        ->name('inventario.warehouses.update');
    Route::delete('/inventario/warehouses/{warehouse}', [InventoryController::class, 'destroyWarehouse'])
        ->name('inventario.warehouses.destroy');

    // Alta de stock desde la UI (rutas específicas primero)
    Route::get('/inventario/stock/create', [InventoryController::class, 'createStock'])
        ->name('inventario.stock.create');
    Route::post('/inventario/stock', [InventoryController::class, 'storeStock'])
        ->name('inventario.stock.store');
    
    // Agregar unidades a producto existente (rutas específicas primero)
    Route::get('/inventario/{inventario}/agregar-stock', [InventoryController::class, 'addStock'])
        ->name('inventario.add-stock');
    Route::post('/inventario/{inventario}/agregar-stock', [InventoryController::class, 'storeAddStock'])
        ->name('inventario.store-add-stock');

    // Resource route (debe ir al final para no capturar rutas específicas)
    Route::resource('inventario', InventoryController::class);

    // ================== PEDIDOS ==================
    Route::resource('pedidos', OrderController::class);

    // Logística (asignación y escaneo)
    Route::post('/logistica/asignar', [DeliveryController::class, 'assign'])->name('logistica.assign');
    Route::post('/logistica/escanear', [DeliveryController::class, 'scan'])->name('logistica.scan');

    // Calendario de entregas
    Route::get('/calendario', [ScheduleController::class, 'index'])->name('calendario.index');
    Route::post('/calendario', [ScheduleController::class, 'store'])->name('calendario.store');

    // ================== FINANZAS ==================
    Route::get('/finanzas', [FinanceController::class, 'index'])->name('finanzas.index');
    Route::get('/finanzas/cierre-diario', [FinanceController::class, 'showDaily'])->name('finanzas.cierre-diario');
    Route::post('/finanzas/cierre-diario', [FinanceController::class, 'dailyClose'])->name('finanzas.dailyClose');
    // (opcional) cierre semanal
    // Route::get('/finanzas/cierre-semanal', [FinanceController::class,'showWeekly'])->name('finanzas.cierre-semanal');
    // Route::post('/finanzas/cierre-semanal', [FinanceController::class,'weeklyClose'])->name('finanzas.weeklyClose');

    // ================== EMPLEADOS ==================
    Route::resource('empleados', EmployeeController::class);

    // ================== POS ==================
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos', [PosController::class, 'store'])->name('pos.store');

    // ================== RUTAS Y ENTREGAS ==================
    Route::get('/rutas', [RouteController::class, 'index'])->name('rutas.index');
    Route::get('/pedidos/{pedido}/ruta', [RouteController::class, 'compute'])->name('pedidos.ruta');

    // ================== ROLES / PERMISOS ==================
    Route::get('/roles/seed', [RoleController::class, 'seed'])->name('roles.seed');
    Route::post('/roles/assign', [RoleController::class, 'assign'])->name('roles.assign');
});

// Fallback 404 simple
Route::fallback(fn () => response()->view('errors.404', [], 404));
