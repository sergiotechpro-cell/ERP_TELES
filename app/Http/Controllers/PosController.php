<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Payment;
use App\Models\ChecklistItem;
use App\Models\Customer;
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

    // Clientes para selección
    $clientes = Customer::orderBy('nombre')->get(['id','nombre','telefono','email']);

    // Ventas recientes (últimas 15)
    $ventas = \App\Models\Sale::latest()
        ->with('customer')
        ->paginate(15);
    
    // Pedidos de entrega creados desde POS (últimos 10)
    $pedidosEntrega = \App\Models\Order::latest()
        ->limit(10)
        ->get();

    // (Opcional) KPIs rápidos
    $hoy = now()->toDateString();
    $totalesHoy = \App\Models\Sale::whereDate('created_at', $hoy)->sum('total');
    $conteoHoy  = \App\Models\Sale::whereDate('created_at', $hoy)->count();

    // Variables para entrega a domicilio
    $mapsKey = config('services.google.maps_key');
    $bodegas = \App\Models\Warehouse::orderBy('nombre')->get(['id','nombre']);
    
    // Obtener coordenadas de origen desde bodega principal
    $warehouse = \App\Models\Warehouse::whereNotNull('lat')
        ->whereNotNull('lng')
        ->orderBy('id')
        ->first();
    
    if ($warehouse) {
        $originLat = (float) $warehouse->lat;
        $originLng = (float) $warehouse->lng;
    } else {
        $originLat = (float) (env('WAREHOUSE_ORIGIN_LAT', 19.432608));
        $originLng = (float) (env('WAREHOUSE_ORIGIN_LNG', -99.133209));
    }

    return view('pos.index', compact('productos','clientes','ventas','totalesHoy','conteoHoy','mapsKey','bodegas','originLat','originLng','pedidosEntrega'));
}


    public function store(Request $r)
{
    $tipoVenta = $r->input('tipo_venta');
    
    // Si es entrega a domicilio, crear pedido
    if ($tipoVenta === 'entrega') {
        $data = $r->validate([
            'forma_pago'               => ['required','in:efectivo,transferencia'],
            'subtotal'                 => ['required','numeric','min:0'],
            'direccion_entrega'        => ['required','string','max:255'],
            'costo_envio'              => ['nullable','numeric','min:0'],
            'cliente_nombre'           => ['nullable','string','max:255'],
            'cliente_telefono'         => ['nullable','string','max:50'],
            'lat'                      => ['nullable','numeric'],
            'lng'                      => ['nullable','numeric'],
            'items'                    => ['required','array','min:1'],
            'items.*.product_id'       => ['required','integer','exists:products,id'],
            'items.*.cantidad'         => ['required','integer','min:1'],
            'items.*.precio_unitario'  => ['required','numeric','min:0'],
        ]);

        // Obtener primera bodega disponible para asignar productos
        $primeraBodega = \App\Models\Warehouse::orderBy('id')->first();
        if (!$primeraBodega) {
            return back()->withErrors(['error' => 'No hay bodegas registradas. Debes crear al menos una bodega primero.'])->withInput();
        }

        DB::transaction(function() use ($data, $primeraBodega, &$pedidoId) {
            $pedido = \App\Models\Order::create([
                'customer_id'       => null,
                'created_by'        => Auth::id(),
                'estado'            => 'capturado',
                'direccion_entrega' => $data['direccion_entrega'],
                'costo_envio'       => $data['costo_envio'] ?? 0,
                'lat'               => $data['lat'] ?? null,
                'lng'               => $data['lng'] ?? null,
            ]);
            $pedidoId = $pedido->id;

            $totalPedido = 0;
            foreach ($data['items'] as $it) {
                $prod = \App\Models\Product::select('id','costo_unitario')->find($it['product_id']);
                $costo = $prod?->costo_unitario ?? 0;
                
                $subtotal = (float) $it['precio_unitario'] * (int) $it['cantidad'];
                $totalPedido += $subtotal;

                \App\Models\OrderItem::create([
                    'order_id'        => $pedido->id,
                    'product_id'      => $it['product_id'],
                    'warehouse_id'    => $primeraBodega->id,
                    'cantidad'        => (int) $it['cantidad'],
                    'precio_unitario' => (float) $it['precio_unitario'],
                    'costo_unitario'  => (float) $costo,
                ]);
                
                // Crear checklist automático por producto
                ChecklistItem::create([
                    'order_id'    => $pedido->id,
                    'texto'       => 'Verificar ' . $prod->descripcion ?? 'producto',
                    'completado'  => false,
                ]);
            }
            
            // Calcular total con envío
            $totalConEnvio = $totalPedido + ($data['costo_envio'] ?? 0);
            
            // Crear pago automáticamente (mapear forma_pago al enum de payments)
            $formaPagoPayment = ($data['forma_pago'] === 'transferencia') ? 'transferencia' : 'efectivo';
            Payment::create([
                'order_id'    => $pedido->id,
                'sale_id'     => null,
                'forma_pago'  => $formaPagoPayment,
                'monto'       => $totalConEnvio,
                'estado'      => 'en_caja',
                'reportado_at' => now(),
            ]);
        });

        return redirect()->route('rutas.index')->with('ok', 'Pedido de entrega creado (#'.$pedidoId.'). Ahora puedes planificar la ruta.');
    }

    // Si es mostrador, crear venta normal
    $data = $r->validate([
        'customer_id'             => ['nullable','integer','exists:customers,id'],
        'forma_pago'               => ['required','in:efectivo,transferencia'],
        'subtotal'                 => ['required','numeric','min:0'],
        'items'                    => ['required','array','min:1'],
        'items.*.product_id'       => ['required','integer','exists:products,id'],
        'items.*.cantidad'         => ['required','integer','min:1'],
        'items.*.precio_unitario'  => ['required','numeric','min:0'],
    ]);

    DB::transaction(function() use ($data, &$saleId) {
        $sale = Sale::create([
            'customer_id' => $data['customer_id'] ?? null,
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
        
        // Crear pago automáticamente para la venta (mapear forma_pago al enum de payments)
        $formaPagoPayment = ($data['forma_pago'] === 'transferencia') ? 'transferencia' : 'efectivo';
        Payment::create([
            'order_id'    => null,
            'sale_id'     => $sale->id,
            'forma_pago'  => $formaPagoPayment,
            'monto'       => $data['subtotal'],
            'estado'      => 'en_caja',
            'reportado_at' => now(),
        ]);
    });

    return redirect()->route('pos.index')->with('ok', 'Venta registrada (#'.$saleId.').');
}

    public function show(Sale $pos)
    {
        $pos->load(['items.product', 'user', 'customer']);
        return view('pos.show', compact('pos'));
    }

    public function destroy(Sale $pos)
    {
        $pos->delete();
        return redirect()->route('pos.index')->with('ok', 'Venta eliminada.');
    }

}
