<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    DashboardController,
    InventoryController,
    WarehouseController,
    OrderController,
    DeliveryController,
    FinanceController,
    EmployeeController,
    ScheduleController,
    RouteController,
    PosController,
    RoleController,
    CustomerController
};

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Rutas del ERP SRDigitalPro.
| Requieren autenticación para acceder a los módulos.
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (auth()->check()) {
        // Si el usuario tiene permiso para ver dashboard, redirigir ahí
        if (auth()->user()->can('ver-dashboard')) {
            return redirect()->route('dashboard');
        }
        // Si no, redirigir al primer módulo al que tenga acceso
        if (auth()->user()->can('ver-pedidos')) {
            return redirect()->route('pedidos.index');
        }
        if (auth()->user()->can('ver-inventario')) {
            return redirect()->route('inventario.index');
        }
        if (auth()->user()->can('ver-pos')) {
            return redirect()->route('pos.index');
        }
        // Si no tiene ningún permiso, redirigir a login
        return redirect()->route('login');
    }
    return redirect()->route('login');
});

// Rutas de autenticación de Breeze (login, logout, register, etc.)
require __DIR__ . '/auth.php';

Route::middleware(['auth'])->group(function () {

    // ================== DASHBOARD ==================
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:ver-dashboard')
        ->name('dashboard');

    // ================== BODEGAS ==================
    Route::resource('bodegas', WarehouseController::class);

    // ================== INVENTARIO ==================
    // NOTA: Las rutas específicas deben ir ANTES del resource route para evitar conflictos
    
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
    Route::post('/pedidos/{pedido}/checklist/{item}', [OrderController::class, 'toggleChecklist'])->name('pedidos.checklist.toggle');

    // Logística (asignación y escaneo)
    Route::post('/logistica/asignar', [DeliveryController::class, 'assign'])->name('logistica.assign');
    Route::post('/logistica/escanear', [DeliveryController::class, 'scan'])->name('logistica.scan');

    // Calendario de entregas
    Route::get('/calendario', [ScheduleController::class, 'index'])->name('calendario.index');
    Route::post('/calendario', [ScheduleController::class, 'store'])->name('calendario.store');

    // ================== FINANZAS ==================
    Route::middleware('permission:ver-finanzas')->group(function () {
        Route::get('/finanzas', [FinanceController::class, 'index'])->name('finanzas.index');
        Route::get('/finanzas/cierre-diario', [FinanceController::class, 'showDaily'])->name('finanzas.cierre-diario');
        Route::post('/finanzas/cierre-diario', [FinanceController::class, 'dailyClose'])->name('finanzas.dailyClose');
    });
    // (opcional) cierre semanal
    // Route::get('/finanzas/cierre-semanal', [FinanceController::class,'showWeekly'])->name('finanzas.cierre-semanal');
    // Route::post('/finanzas/cierre-semanal', [FinanceController::class,'weeklyClose'])->name('finanzas.weeklyClose');

    // ================== EMPLEADOS ==================
    Route::resource('empleados', EmployeeController::class);

    // ================== CLIENTES ==================
    Route::resource('clientes', CustomerController::class);

    // ================== POS ==================
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos', [PosController::class, 'store'])->name('pos.store');
    Route::get('/pos/{pos}', [PosController::class, 'show'])->name('pos.show');
    Route::delete('/pos/{pos}', [PosController::class, 'destroy'])->name('pos.destroy');

    // ================== RUTAS Y ENTREGAS ==================
    Route::get('/rutas', [RouteController::class, 'index'])->name('rutas.index');
    Route::get('/pedidos/{pedido}/ruta', [RouteController::class, 'compute'])->name('pedidos.ruta');

    // ================== ROLES / PERMISOS ==================
    Route::get('/roles/seed', [RoleController::class, 'seed'])->name('roles.seed');
    Route::post('/roles/assign', [RoleController::class, 'assign'])->name('roles.assign');
});

// Fallback 404 simple
Route::fallback(fn () => response()->view('errors.404', [], 404));
