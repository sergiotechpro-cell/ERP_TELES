<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    DashboardController,
    InventoryController,
    OrderController,
    DeliveryController,
    FinanceController,
    CustomerController,
    EmployeeController,
    TransferController,
    ScheduleController,
    RouteController,
    PosController,
    WarrantyController,
    RoleController
};

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Rutas del ERP SRDigitalPro.
| ⚠️ ATENCIÓN: Middleware de autenticación COMENTADO para desarrollo/testing
| ⚠️ IMPORTANTE: Descomentar el middleware antes de subir a producción
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => redirect()->route('dashboard'));

// Rutas de autenticación de Breeze (login, logout, register, etc.)
require __DIR__ . '/auth.php';

// ⚠️ MIDDLEWARE DE AUTENTICACIÓN COMENTADO
// Route::middleware(['auth'])->group(function () {

    // ================== DASHBOARD ==================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ================== INVENTARIO ==================
    Route::resource('inventario', InventoryController::class);

    // Alta de bodegas desde la UI
    Route::get('/inventario/warehouses/create', [InventoryController::class, 'createWarehouse'])
        ->name('inventario.warehouses.create');
    Route::post('/inventario/warehouses', [InventoryController::class, 'storeWarehouse'])
        ->name('inventario.warehouses.store');

    // Alta de stock desde la UI
    Route::get('/inventario/stock/create', [InventoryController::class, 'createStock'])
        ->name('inventario.stock.create');
    Route::post('/inventario/stock', [InventoryController::class, 'storeStock'])
        ->name('inventario.stock.store');

    // ================== PEDIDOS ==================
    Route::resource('pedidos', OrderController::class);

    // Logística (asignación y escaneo)
    Route::post('/logistica/asignar', [DeliveryController::class, 'assign'])->name('logistica.assign');
    Route::post('/logistica/escanear', [DeliveryController::class, 'scan'])->name('logistica.scan');

    // Calendario de entregas
    Route::get('/calendario', [ScheduleController::class, 'index'])->name('calendario.index');
    Route::post('/calendario', [ScheduleController::class, 'store'])->name('calendario.store');

    // Rutas (Google Maps) para un pedido
    Route::get('/pedidos/{pedido}/ruta', [RouteController::class, 'compute'])->name('pedidos.ruta');

    // ================== TRASPASOS ==================
    Route::get('/traspasos', [TransferController::class, 'index'])->name('traspasos.index');
    Route::get('/traspasos/nuevo', [TransferController::class, 'create'])->name('traspasos.create');
    Route::post('/traspasos', [TransferController::class, 'store'])->name('traspasos.store');
    Route::post('/traspasos/{traspaso}/recibir', [TransferController::class, 'receive'])->name('traspasos.receive');

    // ================== FINANZAS ==================
    Route::get('/finanzas', [FinanceController::class, 'index'])->name('finanzas.index');
    Route::get('/finanzas/cierre-diario', [FinanceController::class, 'showDaily'])->name('finanzas.cierre-diario');
    Route::post('/finanzas/cierre-diario', [FinanceController::class, 'dailyClose'])->name('finanzas.dailyClose');
    // (opcional) cierre semanal
    // Route::get('/finanzas/cierre-semanal', [FinanceController::class,'showWeekly'])->name('finanzas.cierre-semanal');
    // Route::post('/finanzas/cierre-semanal', [FinanceController::class,'weeklyClose'])->name('finanzas.weeklyClose');

    // ================== CLIENTES ==================
    Route::resource('clientes', CustomerController::class);

    // ================== EMPLEADOS ==================
    Route::resource('empleados', EmployeeController::class);

    // ================== POS ==================
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos', [PosController::class, 'store'])->name('pos.store');

    // ================== GARANTÍAS ==================
    Route::get('/garantias', [WarrantyController::class, 'index'])->name('garantias.index');
    Route::get('/garantias/nueva', [WarrantyController::class, 'create'])->name('garantias.create');
    Route::post('/garantias', [WarrantyController::class, 'store'])->name('garantias.store');

    // ================== ROLES / PERMISOS ==================
    Route::get('/roles/seed', [RoleController::class, 'seed'])->name('roles.seed');
    Route::post('/roles/assign', [RoleController::class, 'assign'])->name('roles.assign');

// }); // ⚠️ FIN DEL GRUPO DE AUTENTICACIÓN COMENTADO

// Fallback 404 simple
Route::fallback(fn () => response()->view('errors.404', [], 404));