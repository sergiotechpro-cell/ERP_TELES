<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\OrderItem;
use App\Models\WarehouseProduct;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceController extends Controller
{
    public function index()
    {
        Carbon::setLocale('es');

        // ======== INVENTARIO ========
        $inventario = WarehouseProduct::query()
            ->join('products','products.id','=','warehouse_product.product_id')
            ->selectRaw('COALESCE(SUM(warehouse_product.stock * products.costo_unitario),0) as costo_total')
            ->value('costo_total');

        $utilidadProyectada = WarehouseProduct::query()
            ->join('products','products.id','=','warehouse_product.product_id')
            ->selectRaw("
                COALESCE(
                    SUM(
                        warehouse_product.stock * (
                            COALESCE(NULLIF(products.precio_mayoreo,0), products.precio_venta)
                            - products.costo_unitario
                        )
                    ), 0
                ) as utilidad
            ")
            ->value('utilidad');

        // ======== SERIES MENSUALES EN PHP ========
        // POS: ventas por mes
        $posRaw = Sale::selectRaw("DATE_TRUNC('month', created_at) as mes, SUM(total) as total")
            ->groupBy('mes')->orderBy('mes')->get();

        // Pedidos: total por mes desde order_items
        $pedRaw = OrderItem::query()
            ->join('orders','orders.id','=','order_items.order_id')
            ->selectRaw("DATE_TRUNC('month', orders.created_at) as mes, SUM(order_items.cantidad * order_items.precio_unitario) as total")
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
        foreach ($utilPosRaw as $r){$k = $fmt($r->mes); $months[$k] = true; $util[$k] = (float)$r->utilidad; }

        $months = $months->keys()->sort()->values(); // orden cronológico

        $chartLabels = $months->map(fn($k) => $labelFmt($k));
        $chartPOS    = $months->map(fn($k) => $pos[$k]  ?? 0.0);
        $chartPedidos= $months->map(fn($k) => $ped[$k]  ?? 0.0);
        $chartUtil   = $months->map(fn($k) => $util[$k] ?? 0.0);

        // ======== PAGOS Y VENTAS RECIENTES ========
        $pagos = Payment::with('order')->latest()->paginate(10);
        $pagosUltimos7 = Payment::where('created_at','>=',now()->subDays(7))->count();
        $ventasPosRecientes = Sale::latest()->limit(5)->get();

        return view('finanzas.index', compact(
            'inventario',
            'utilidadProyectada',
            'pagos',
            'pagosUltimos7',
            'chartLabels',
            'chartPOS',
            'chartPedidos',
            'chartUtil',
            'ventasPosRecientes'
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
