<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\EmployeeProfile;
use App\Models\Sale;
use App\Models\OrderItem;
use App\Models\WarehouseProduct;
use App\Models\SerialNumber;
use App\Models\WarrantyClaim;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $monthStart = now()->copy()->startOfMonth();
        $monthEnd = now()->copy()->endOfMonth();

        // Ventas POS y pedidos del día
        $ventasPosHoy = (float) Sale::whereDate('created_at', $today)->sum('total');

        $ventasPedidosItemsHoy = (float) OrderItem::whereHas('order', function ($q) use ($today) {
                $q->whereDate('created_at', $today);
            })
            ->selectRaw('COALESCE(SUM(cantidad * precio_unitario), 0) as total')
            ->value('total');

        $ventasPedidosEnvioHoy = (float) Order::whereDate('created_at', $today)->sum('costo_envio');
        $ventasPedidosHoy = $ventasPedidosItemsHoy + $ventasPedidosEnvioHoy;
        $ventasHoy = $ventasPosHoy + $ventasPedidosHoy;

        // KPIs principales
        $inventario = Product::count();
        $pedidos = Order::count();
        $clientes = Customer::count();
        $empleados = EmployeeProfile::count();

        $valorInventario = (float) WarehouseProduct::join('products', 'products.id', '=', 'warehouse_product.product_id')
            ->selectRaw('COALESCE(SUM(warehouse_product.stock * products.costo_unitario), 0) as total')
            ->value('total');

        $utilidadInventario = (float) WarehouseProduct::join('products', 'products.id', '=', 'warehouse_product.product_id')
            ->selectRaw("
                COALESCE(SUM(
                    warehouse_product.stock * (
                        COALESCE(NULLIF(products.precio_mayoreo, 0), products.precio_venta)
                        - COALESCE(products.costo_unitario, 0)
                    )
                ), 0) as utilidad")
            ->value('utilidad');

        $serialStats = SerialNumber::selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $serialDisponibles = (int) ($serialStats['disponible'] ?? 0);
        $serialApartados = (int) ($serialStats['apartado'] ?? 0);
        $serialEntregados = (int) ($serialStats['entregado'] ?? 0);

        $garantiasAbiertas = WarrantyClaim::where('status', '!=', 'cerrada')->count();
        $garantiasCerradasMes = WarrantyClaim::where('status', 'cerrada')
            ->whereBetween('updated_at', [$monthStart, $monthEnd])
            ->count();

        // Ventas mensuales (POS y pedidos)
        $ventasPosMensuales = Sale::selectRaw("DATE_TRUNC('month', created_at) as mes, SUM(total) as total")
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $ventasPedidosMensuales = OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
            ->selectRaw("DATE_TRUNC('month', orders.created_at) as mes, SUM(order_items.cantidad * order_items.precio_unitario) as total")
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $enviosMensuales = Order::selectRaw("DATE_TRUNC('month', created_at) as mes, SUM(costo_envio) as total")
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $months = collect();
        $fmtKey = fn($date) => Carbon::parse($date)->format('Y-m');
        $fmtLabel = fn($key) => Carbon::createFromFormat('Y-m', $key)->isoFormat('MMM YYYY');

        $posSeries = collect();
        foreach ($ventasPosMensuales as $row) {
            $key = $fmtKey($row->mes);
            $months[$key] = true;
            $posSeries[$key] = (float) $row->total;
        }

        $pedSeries = collect();
        foreach ($ventasPedidosMensuales as $row) {
            $key = $fmtKey($row->mes);
            $months[$key] = true;
            $pedSeries[$key] = (float) $row->total;
        }
        foreach ($enviosMensuales as $row) {
            $key = $fmtKey($row->mes);
            $months[$key] = true;
            $pedSeries[$key] = ($pedSeries[$key] ?? 0) + (float) $row->total;
        }

        $months = $months->keys()->sort()->values();
        $chartLabels = $months->map(fn($key) => $fmtLabel($key));
        $chartPos = $months->map(fn($key) => $posSeries[$key] ?? 0.0);
        $chartPedidos = $months->map(fn($key) => $pedSeries[$key] ?? 0.0);
        
        // Ingresos registrados en pagos hoy
        $ingresosPagosHoy = (float) (Payment::where(function($q) {
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

        $topProductos = SaleItem::with('product')
            ->selectRaw('product_id, SUM(cantidad) as unidades, SUM(cantidad * precio_unitario) as monto')
            ->groupBy('product_id')
            ->orderByDesc('monto')
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
            'ventasPosHoy',
            'ventasPedidosHoy',
            'pedidosPorEstado',
            'ventasRecientes',
            'pedidosRecientes',
            'pedidosPendientes',
            'pedidosEntregados',
            'pedidosFinalizados',
            'ingresosPagosHoy',
            'chartLabels',
            'chartPos',
            'chartPedidos',
            'valorInventario',
            'utilidadInventario',
            'serialDisponibles',
            'serialApartados',
            'serialEntregados',
            'garantiasAbiertas',
            'garantiasCerradasMes',
            'topProductos'
        ));
    }
}
