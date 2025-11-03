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
        // Ventas mensuales - solo pagos válidos
        $ventasMensuales = Payment::selectRaw("DATE_TRUNC('month', created_at) as mes, SUM(monto) as total")
            ->whereIn('estado', ['en_caja', 'depositado', 'completado'])
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        // KPIs principales
        $inventario = Product::count();
        $pedidos = Order::count();
        $clientes = Customer::count();
        $empleados = EmployeeProfile::count();
        $ventasHoy = (float) (Payment::whereDate('created_at', now()->toDateString())
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
        $pedidosEntregados = Order::where('estado', 'entregado')->count();

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
            'pedidosEntregados'
        ));
    }
}
