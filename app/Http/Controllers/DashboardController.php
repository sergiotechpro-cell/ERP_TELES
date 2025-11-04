<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\EmployeeProfile;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Ventas mensuales - solo pagos válidos (usar entregado_caja_at si existe, sino created_at)
        $ventasMensuales = Payment::selectRaw("
                DATE_TRUNC('month', COALESCE(entregado_caja_at, created_at)) as mes, 
                SUM(monto) as total
            ")
            ->whereIn('estado', ['en_caja', 'depositado', 'completado'])
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        // KPIs principales
        $inventario = Product::count();
        $pedidos = Order::count();
        $clientes = Customer::count();
        $empleados = EmployeeProfile::count();
        
        // Ventas hoy: pagos completados hoy o creados hoy
        $ventasHoy = (float) (Payment::where(function($q) {
                $q->whereDate('entregado_caja_at', now()->toDateString())
                  ->orWhere(function($q2) {
                      $q2->whereDate('created_at', now()->toDateString())
                         ->whereIn('estado', ['en_caja', 'depositado', 'completado']);
                  });
            })
            ->whereIn('estado', ['en_caja', 'depositado', 'completado'])
            ->sum('monto') ?? 0);

        // Pedidos por estado
        $pedidosPorEstado = Order::selectRaw('estado, COUNT(*) as cantidad')
            ->groupBy('estado')
            ->orderBy('cantidad', 'desc')
            ->get()
            ->pluck('cantidad', 'estado');

        // Ventas recientes
        $ventasRecientes = Sale::with(['customer', 'user'])
            ->latest()
            ->limit(5)
            ->get();

        // Pedidos recientes
        $pedidosRecientes = Order::with(['customer', 'assignment.courier'])
            ->latest()
            ->limit(5)
            ->get();

        // Estadísticas adicionales
        $pedidosPendientes = Order::whereIn('estado', ['capturado', 'preparacion', 'asignado', 'en_ruta'])->count();
        $pedidosEntregados = Order::whereIn('estado', ['entregado', 'entregado_pendiente_pago', 'finalizado'])->count();
        $pedidosFinalizados = Order::where('estado', 'finalizado')->count();

        return view('dashboard', compact(
            'ventasMensuales',
            'inventario',
            'pedidos',
            'clientes',
            'empleados',
            'ventasHoy',
            'pedidosPorEstado',
            'ventasRecientes',
            'pedidosRecientes',
            'pedidosPendientes',
            'pedidosEntregados',
            'pedidosFinalizados'
        ));
    }
}
