<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index()
{
    // Productos para el POS
    $productos = \App\Models\Product::orderBy('descripcion')
        ->get(['id','descripcion','precio_venta','precio_mayoreo','costo_unitario','price_tier']);

    // Ventas recientes (últimas 15)
    $ventas = \App\Models\Sale::latest()
        ->paginate(15);

    // (Opcional) KPIs rápidos
    $hoy = now()->toDateString();
    $totalesHoy = \App\Models\Sale::whereDate('created_at', $hoy)->sum('total');
    $conteoHoy  = \App\Models\Sale::whereDate('created_at', $hoy)->count();

    return view('pos.index', compact('productos','ventas','totalesHoy','conteoHoy'));
}


    public function store(Request $r)
{
    $data = $r->validate([
        'forma_pago'               => ['required','in:efectivo,transferencia'],
        'subtotal'                 => ['required','numeric','min:0'],
        'items'                    => ['required','array','min:1'],
        'items.*.product_id'       => ['required','integer','exists:products,id'],
        'items.*.cantidad'         => ['required','integer','min:1'],
        'items.*.precio_unitario'  => ['required','numeric','min:0'],
    ]);

    DB::transaction(function() use ($data, &$saleId) {
        $sale = Sale::create([
            'customer_id' => null,
            'user_id'     => Auth::id(),
            'subtotal'    => $data['subtotal'],
            'total'       => $data['subtotal'],   // sin impuestos por ahora
            'forma_pago'  => $data['forma_pago'],
            'status'      => 'pagada',
        ]);
        $saleId = $sale->id;

        foreach ($data['items'] as $it) {
            // Trae el costo del producto desde BD (no del cliente)
            $prod = \App\Models\Product::select('id','costo_unitario')->find($it['product_id']);
            $costo = $prod?->costo_unitario ?? 0;

            SaleItem::create([
                'sale_id'         => $sale->id,
                'product_id'      => $it['product_id'],
                'cantidad'        => $it['cantidad'],
                'precio_unitario' => $it['precio_unitario'],
                'costo_unitario'  => $costo, // <-- CLAVE: evita el NOT NULL
            ]);
        }
    });

    return redirect()->route('pos.index')->with('ok', 'Venta registrada (#'.$saleId.').');
}

}
