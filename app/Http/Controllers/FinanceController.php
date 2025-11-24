<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\OrderItem;
use App\Models\WarehouseProduct;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Order;
use App\Models\WarrantyClaim;
use App\Models\SerialNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceController extends Controller
{
    public function index()
    {
        Carbon::setLocale('es');
        $monthStart = now()->copy()->startOfMonth();
        $monthEnd   = now()->copy()->endOfMonth();

        // ======== INVENTARIO ========
        // Calcular costo total del inventario (stock * costo_unitario)
        $inventario = (float) (WarehouseProduct::query()
            ->join('products', 'products.id', '=', 'warehouse_product.product_id')
            ->where('warehouse_product.stock', '>', 0)
            ->selectRaw('COALESCE(SUM(warehouse_product.stock * products.costo_unitario), 0) as costo_total')
            ->value('costo_total') ?? 0);

        // Calcular utilidad proyectada (stock * (precio_venta - costo_unitario))
        $utilidadProyectada = (float) (WarehouseProduct::query()
            ->join('products', 'products.id', '=', 'warehouse_product.product_id')
            ->where('warehouse_product.stock', '>', 0)
            ->selectRaw("
                COALESCE(
                    SUM(
                        warehouse_product.stock * (
                            COALESCE(NULLIF(products.precio_mayoreo, 0), products.precio_venta)
                            - COALESCE(products.costo_unitario, 0)
                        )
                    ), 0
                ) as utilidad
            ")
            ->value('utilidad') ?? 0);

        // ======== SERIES MENSUALES EN PHP ========
        // POS: ventas por mes
        $posRaw = Sale::selectRaw("DATE_TRUNC('month', created_at) as mes, SUM(total) as total")
            ->groupBy('mes')->orderBy('mes')->get();

        // Pedidos: total por mes desde order_items
        $pedRaw = OrderItem::query()
            ->join('orders','orders.id','=','order_items.order_id')
            ->selectRaw("DATE_TRUNC('month', orders.created_at) as mes, SUM(order_items.cantidad * order_items.precio_unitario) as total")
            ->groupBy('mes')->orderBy('mes')->get();

        $envioRaw = Order::selectRaw("DATE_TRUNC('month', created_at) as mes, SUM(costo_envio) as total")
            ->groupBy('mes')->orderBy('mes')->get();

        // Utilidad POS por mes (ingreso - costo)
        $utilPosRaw = SaleItem::query()
            ->join('products','products.id','=','sale_items.product_id')
            ->join('sales','sales.id','=','sale_items.sale_id')
            ->selectRaw("
                DATE_TRUNC('month', sales.created_at) as mes,
                SUM( (sale_items.precio_unitario - products.costo_unitario) * sale_items.cantidad ) as utilidad
            ")
            ->groupBy('mes')->orderBy('mes')->get();

        // Normaliza a llaves 'Y-m' para fusionar
        $fmt = fn($dt) => Carbon::parse($dt)->format('Y-m');
        $labelFmt = fn($key) => Carbon::createFromFormat('Y-m', $key)->isoFormat('MMM YYYY');

        $months = collect();
        $pos    = collect();
        $ped    = collect();
        $util   = collect();

        foreach ($posRaw as $r) { $k = $fmt($r->mes); $months[$k] = true; $pos[$k]  = (float)$r->total; }
        foreach ($pedRaw as $r) { $k = $fmt($r->mes); $months[$k] = true; $ped[$k]  = (float)$r->total; }
        foreach ($envioRaw as $r) { $k = $fmt($r->mes); $months[$k] = true; $ped[$k] = ($ped[$k] ?? 0) + (float)$r->total; }
        foreach ($utilPosRaw as $r){$k = $fmt($r->mes); $months[$k] = true; $util[$k] = (float)$r->utilidad; }

        $months = $months->keys()->sort()->values(); // orden cronológico

        $chartLabels = $months->map(fn($k) => $labelFmt($k));
        $chartPOS    = $months->map(fn($k) => $pos[$k]  ?? 0.0);
        $chartPedidos= $months->map(fn($k) => $ped[$k]  ?? 0.0);
        $chartUtil   = $months->map(fn($k) => $util[$k] ?? 0.0);

        // ======== PAGOS Y VENTAS RECIENTES ========
        $pagos = Payment::with(['order', 'sale'])->latest()->paginate(20);
        $pagosUltimos7 = Payment::where('created_at','>=',now()->subDays(7))->count();
        $ventasPosRecientes = Sale::latest()->limit(5)->get();

        $ventasPosMesActual = (float) Sale::whereBetween('created_at', [$monthStart, $monthEnd])->sum('total');

        $ventasPedidosItemsMes = (float) OrderItem::whereHas('order', function ($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('created_at', [$monthStart, $monthEnd]);
            })
            ->selectRaw('COALESCE(SUM(cantidad * precio_unitario), 0) as total')
            ->value('total');

        $ventasPedidosEnvioMes = (float) Order::whereBetween('created_at', [$monthStart, $monthEnd])->sum('costo_envio');
        $ventasPedidosMesActual = $ventasPedidosItemsMes + $ventasPedidosEnvioMes;
        
        // Totales de pagos - usar entregado_caja_at si existe, sino created_at
        // HOY: pagos completados hoy o creados hoy
        $totalPagosHoy = (float) (Payment::where(function($q) {
                $q->whereDate('entregado_caja_at', now()->toDateString())
                  ->orWhere(function($q2) {
                      $q2->whereDate('created_at', now()->toDateString())
                         ->whereIn('estado', ['en_caja', 'depositado', 'completado']);
                  });
            })
            ->whereIn('estado', ['en_caja', 'depositado', 'completado'])
            ->sum('monto') ?? 0);
            
        // ÚLTIMOS 7 DÍAS: usar entregado_caja_at si existe, sino created_at
        $totalPagos7Dias = (float) (Payment::where(function($q) {
                $q->where('entregado_caja_at', '>=', now()->subDays(7))
                  ->orWhere(function($q2) {
                      $q2->where('created_at', '>=', now()->subDays(7))
                         ->whereNull('entregado_caja_at')
                         ->whereIn('estado', ['en_caja', 'depositado', 'completado']);
                  });
            })
            ->whereIn('estado', ['en_caja', 'depositado', 'completado'])
            ->sum('monto') ?? 0);
            
        // ESTE MES: usar entregado_caja_at si existe, sino created_at
        $totalPagosEsteMes = (float) (Payment::where(function($q) {
                $q->whereYear('entregado_caja_at', now()->year)
                  ->whereMonth('entregado_caja_at', now()->month)
                  ->orWhere(function($q2) {
                      $q2->whereYear('created_at', now()->year)
                         ->whereMonth('created_at', now()->month)
                         ->whereNull('entregado_caja_at')
                         ->whereIn('estado', ['en_caja', 'depositado', 'completado']);
                  });
            })
            ->whereIn('estado', ['en_caja', 'depositado', 'completado'])
            ->sum('monto') ?? 0);
        
        // Totales por método de pago (hoy) - pagos completados hoy o creados hoy
        $totalEfectivoHoy = (float) (Payment::where(function($q) {
                $q->whereDate('entregado_caja_at', now()->toDateString())
                  ->orWhere(function($q2) {
                      $q2->whereDate('created_at', now()->toDateString())
                         ->whereIn('estado', ['en_caja', 'depositado', 'completado']);
                  });
            })
            ->where('forma_pago', 'efectivo')
            ->whereIn('estado', ['en_caja', 'depositado', 'completado'])
            ->sum('monto') ?? 0);
        
        $totalTransferenciaHoy = (float) (Payment::where(function($q) {
                $q->whereDate('entregado_caja_at', now()->toDateString())
                  ->orWhere(function($q2) {
                      $q2->whereDate('created_at', now()->toDateString())
                         ->whereIn('estado', ['en_caja', 'depositado', 'completado']);
                  });
            })
            ->where('forma_pago', 'transferencia')
            ->whereIn('estado', ['en_caja', 'depositado', 'completado'])
            ->sum('monto') ?? 0);
        
        // Totales por método de pago (últimos 7 días) - usar entregado_caja_at si existe
        $totalEfectivo7Dias = (float) (Payment::where(function($q) {
                $q->where('entregado_caja_at', '>=', now()->subDays(7))
                  ->orWhere(function($q2) {
                      $q2->where('created_at', '>=', now()->subDays(7))
                         ->whereNull('entregado_caja_at')
                         ->whereIn('estado', ['en_caja', 'depositado', 'completado']);
                  });
            })
            ->where('forma_pago', 'efectivo')
            ->whereIn('estado', ['en_caja', 'depositado', 'completado'])
            ->sum('monto') ?? 0);
        
        $totalTransferencia7Dias = (float) (Payment::where(function($q) {
                $q->where('entregado_caja_at', '>=', now()->subDays(7))
                  ->orWhere(function($q2) {
                      $q2->where('created_at', '>=', now()->subDays(7))
                         ->whereNull('entregado_caja_at')
                         ->whereIn('estado', ['en_caja', 'depositado', 'completado']);
                  });
            })
            ->where('forma_pago', 'transferencia')
            ->whereIn('estado', ['en_caja', 'depositado', 'completado'])
            ->sum('monto') ?? 0);
        
        // Contar pagos por método (hoy) - pagos completados hoy o creados hoy
        $countEfectivoHoy = (int) (Payment::where(function($q) {
                $q->whereDate('entregado_caja_at', now()->toDateString())
                  ->orWhere(function($q2) {
                      $q2->whereDate('created_at', now()->toDateString())
                         ->whereIn('estado', ['en_caja', 'depositado', 'completado']);
                  });
            })
            ->where('forma_pago', 'efectivo')
            ->whereIn('estado', ['en_caja', 'depositado', 'completado'])
            ->count() ?? 0);
        
        $countTransferenciaHoy = (int) (Payment::where(function($q) {
                $q->whereDate('entregado_caja_at', now()->toDateString())
                  ->orWhere(function($q2) {
                      $q2->whereDate('created_at', now()->toDateString())
                         ->whereIn('estado', ['en_caja', 'depositado', 'completado']);
                  });
            })
            ->where('forma_pago', 'transferencia')
            ->whereIn('estado', ['en_caja', 'depositado', 'completado'])
            ->count() ?? 0);

        $garantiasAbiertas = WarrantyClaim::where('status','!=','cerrada')->count();
        $serialStats = SerialNumber::selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total','estado');

        $topProductosPos = SaleItem::with('product')
            ->selectRaw('product_id, SUM(cantidad) as unidades, SUM(cantidad * precio_unitario) as monto')
            ->groupBy('product_id')
            ->orderByDesc('monto')
            ->limit(6)
            ->get();

        $topPedidosIngresos = OrderItem::with('order.customer')
            ->selectRaw('order_id, SUM(cantidad * precio_unitario) as subtotal')
            ->groupBy('order_id')
            ->orderByDesc('subtotal')
            ->limit(6)
            ->get()
            ->map(function ($row) {
                $envio = $row->order?->costo_envio ?? 0;
                $row->total = (float) $row->subtotal + (float) $envio;
                return $row;
            });

        return view('finanzas.index', compact(
            'inventario',
            'utilidadProyectada',
            'pagos',
            'pagosUltimos7',
            'totalPagosHoy',
            'totalPagos7Dias',
            'totalPagosEsteMes',
            'totalEfectivoHoy',
            'totalTransferenciaHoy',
            'totalEfectivo7Dias',
            'totalTransferencia7Dias',
            'countEfectivoHoy',
            'countTransferenciaHoy',
            'chartLabels',
            'chartPOS',
            'chartPedidos',
            'chartUtil',
            'ventasPosRecientes',
            'ventasPosMesActual',
            'ventasPedidosMesActual',
            'garantiasAbiertas',
            'serialStats',
            'topProductosPos',
            'topPedidosIngresos'
        ));
    }

    public function showDaily()
    {
        return view('finanzas.cierre-diario');
    }

    public function dailyClose(Request $request)
    {
        return back()->with('ok','Cierre diario registrado.');
    }
}
