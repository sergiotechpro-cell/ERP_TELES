<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Payment;
use App\Models\ChecklistItem;
use App\Models\Customer;
use App\Models\DeliveryAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index()
{
    // Validar elementos necesarios
    $productos = \App\Models\Product::with('warehouses')
        ->orderBy('descripcion')
        ->get(['id','descripcion','precio_venta','precio_mayoreo','costo_unitario','price_tier']);
    
    // Calcular stock total para cada producto
    $productos = $productos->map(function($p) {
        $p->stock_total = $p->warehouses->sum(fn($w) => $w->pivot->stock ?? 0);
        return $p;
    });
    
    $clientes = Customer::orderBy('nombre')->get(['id','nombre','telefono','email']);
    $bodegas = \App\Models\Warehouse::orderBy('nombre')->get(['id','nombre']);
    
    // Verificar choferes (usuarios con EmployeeProfile)
    $choferes = \App\Models\User::whereHas('employeeProfile')->get(['id','name','email']);
    
    // Variables de validación para mostrar alertas
    $hasProductos = $productos->count() > 0;
    $hasClientes = $clientes->count() > 0;
    $hasBodegas = $bodegas->count() > 0;
    $hasChoferes = $choferes->count() > 0;
    $canProceed = $hasProductos && $hasBodegas; // Cliente y chofer son opcionales pero recomendados

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

    return view('pos.index', compact(
        'productos','clientes','ventas','totalesHoy','conteoHoy','mapsKey','bodegas',
        'originLat','originLng','pedidosEntrega','choferes',
        'hasProductos','hasClientes','hasBodegas','hasChoferes','canProceed'
    ));
}


    public function store(Request $r)
{
    // Validar elementos necesarios antes de proceder
    $productosCount = \App\Models\Product::count();
    $bodegasCount = \App\Models\Warehouse::count();
    $choferesCount = \App\Models\User::whereHas('employeeProfile')->count();
    
    if ($productosCount === 0) {
        return back()->withErrors(['error' => 'No hay productos registrados. Debes crear al menos un producto primero.'])->withInput();
    }
    
    if ($bodegasCount === 0) {
        return back()->withErrors(['error' => 'No hay bodegas registradas. Debes crear al menos una bodega primero.'])->withInput();
    }
    
    $tipoVenta = $r->input('tipo_venta');
    
    // Si es entrega a domicilio, crear pedido
    if ($tipoVenta === 'entrega') {
        // Para entregas, es recomendable tener choferes
        if ($choferesCount === 0) {
            return back()->withErrors(['error' => 'No hay choferes registrados. Debes crear al menos un empleado chofer primero.'])->withInput();
        }
        
        $data = $r->validate([
            'forma_pago'               => ['required','in:efectivo,tarjeta,transferencia'],
            'subtotal'                 => ['required','numeric','min:0'],
            'direccion_entrega'        => ['required','string','max:255'],
            'costo_envio'              => ['required','numeric','min:0'],
            'cliente_nombre'           => ['required_without:customer_id','string','max:255'],
            'cliente_telefono'         => ['required_without:customer_id','string','max:50'],
            'courier_id'               => ['required','integer','exists:users,id'],
            'lat'                      => ['required','numeric'],
            'lng'                      => ['required','numeric'],
            'items'                    => ['required','array','min:1'],
            'items.*.product_id'       => ['required','integer','exists:products,id'],
            'items.*.cantidad'         => ['required','integer','min:1'],
            'items.*.precio_unitario'  => ['required','numeric','min:0'],
        ], [
            'direccion_entrega.required' => 'La dirección de entrega es obligatoria.',
            'costo_envio.required' => 'El costo de envío es obligatorio.',
            'cliente_nombre.required_without' => 'El nombre del cliente es obligatorio si no seleccionas un cliente existente.',
            'cliente_telefono.required_without' => 'El teléfono del cliente es obligatorio si no seleccionas un cliente existente.',
            'courier_id.required' => 'El chofer es obligatorio para entregas a domicilio.',
            'courier_id.exists' => 'El chofer seleccionado no es válido.',
            'lat.required' => 'Las coordenadas son obligatorias. Selecciona una dirección válida.',
            'lng.required' => 'Las coordenadas son obligatorias. Selecciona una dirección válida.',
            'items.required' => 'Debes agregar al menos un producto.',
            'items.min' => 'Debes agregar al menos un producto.',
        ]);

        // Obtener primera bodega disponible para asignar productos
        $primeraBodega = \App\Models\Warehouse::orderBy('id')->first();
        if (!$primeraBodega) {
            return back()->withErrors(['error' => 'No hay bodegas registradas. Debes crear al menos una bodega primero.'])->withInput();
        }

        DB::transaction(function() use ($data, $primeraBodega, &$pedidoId, &$choferAsignado) {
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
                $prod = \App\Models\Product::with('warehouses')->find($it['product_id']);
                if (!$prod) {
                    throw new \Exception("Producto no encontrado: {$it['product_id']}");
                }
                
                // Calcular stock disponible
                $stockDisponible = $prod->warehouses->sum(fn($w) => $w->pivot->stock ?? 0);
                $cantidadSolicitada = (int) $it['cantidad'];
                
                if ($cantidadSolicitada > $stockDisponible) {
                    throw new \Exception("Stock insuficiente para {$prod->descripcion}. Disponible: {$stockDisponible}, Solicitado: {$cantidadSolicitada}");
                }
                
                $costo = $prod->costo_unitario ?? 0;
                $subtotal = (float) $it['precio_unitario'] * $cantidadSolicitada;
                $totalPedido += $subtotal;

                \App\Models\OrderItem::create([
                    'order_id'        => $pedido->id,
                    'product_id'      => $it['product_id'],
                    'warehouse_id'    => $primeraBodega->id,
                    'cantidad'        => $cantidadSolicitada,
                    'precio_unitario' => (float) $it['precio_unitario'],
                    'costo_unitario'  => (float) $costo,
                ]);
                
                // Descontar stock del inventario
                $cantidadRestante = $cantidadSolicitada;
                foreach ($prod->warehouses as $warehouse) {
                    if ($cantidadRestante <= 0) break;
                    
                    $stockActual = $warehouse->pivot->stock ?? 0;
                    if ($stockActual > 0) {
                        $cantidadADescontar = min($stockActual, $cantidadRestante);
                        DB::table('warehouse_product')
                            ->where('warehouse_id', $warehouse->id)
                            ->where('product_id', $prod->id)
                            ->decrement('stock', $cantidadADescontar);
                        $cantidadRestante -= $cantidadADescontar;
                    }
                }
                
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
            $formaPagoPayment = match($data['forma_pago']) {
                'tarjeta' => 'tarjeta',
                'transferencia' => 'transferencia',
                default => 'efectivo'
            };
            Payment::create([
                'order_id'    => $pedido->id,
                'sale_id'     => null,
                'forma_pago'  => $formaPagoPayment,
                'monto'       => $totalConEnvio,
                'estado'      => 'en_caja',
                'reportado_at' => now(),
            ]);
            
            // Asignar chofer (obligatorio)
            if (\App\Models\User::whereHas('employeeProfile')->where('id', $data['courier_id'])->exists()) {
                DeliveryAssignment::create([
                    'order_id' => $pedido->id,
                    'courier_id' => $data['courier_id'],
                    'asignado_at' => now(),
                    'estado' => 'pendiente'
                ]);
                $pedido->update(['estado' => 'asignado']);
            }
        });

        $mensaje = "Pedido de entrega creado (#{$pedidoId}) con chofer asignado. Ya aparece en el calendario.";
        
        return redirect()->route('rutas.index')->with('ok', $mensaje);
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
        // Obtener usuario vendedor y calcular comisión
        $user = Auth::user();
        $commissionPercentage = $user->commission_percentage ?? 0;
        $commissionAmount = 0;
        
        if ($commissionPercentage > 0) {
            $commissionAmount = ($data['subtotal'] * $commissionPercentage) / 100;
        }
        
        $sale = Sale::create([
            'customer_id' => $data['customer_id'] ?? null,
            'user_id'     => Auth::id(),
            'subtotal'    => $data['subtotal'],
            'total'       => $data['subtotal'],   // sin impuestos por ahora
            'forma_pago'  => $data['forma_pago'],
            'status'      => 'pagada',
            'commission_amount' => $commissionAmount,
            'commission_percentage' => $commissionPercentage > 0 ? $commissionPercentage : null,
        ]);
        $saleId = $sale->id;

        foreach ($data['items'] as $it) {
            // Trae el producto con stock
            $prod = \App\Models\Product::with('warehouses')->find($it['product_id']);
            if (!$prod) {
                throw new \Exception("Producto no encontrado: {$it['product_id']}");
            }
            
            // Validar stock disponible
            $stockDisponible = $prod->warehouses->sum(fn($w) => $w->pivot->stock ?? 0);
            $cantidadSolicitada = (int) $it['cantidad'];
            
            if ($cantidadSolicitada > $stockDisponible) {
                throw new \Exception("Stock insuficiente para {$prod->descripcion}. Disponible: {$stockDisponible}, Solicitado: {$cantidadSolicitada}");
            }
            
            $costo = $prod->costo_unitario ?? 0;

            SaleItem::create([
                'sale_id'         => $sale->id,
                'product_id'      => $it['product_id'],
                'cantidad'        => $cantidadSolicitada,
                'precio_unitario' => $it['precio_unitario'],
                'costo_unitario'  => $costo,
            ]);
            
            // Descontar stock del inventario
            $cantidadRestante = $cantidadSolicitada;
            foreach ($prod->warehouses as $warehouse) {
                if ($cantidadRestante <= 0) break;
                
                $stockActual = $warehouse->pivot->stock ?? 0;
                if ($stockActual > 0) {
                    $cantidadADescontar = min($stockActual, $cantidadRestante);
                    DB::table('warehouse_product')
                        ->where('warehouse_id', $warehouse->id)
                        ->where('product_id', $prod->id)
                        ->decrement('stock', $cantidadADescontar);
                    $cantidadRestante -= $cantidadADescontar;
                }
            }
        }
        
        // Crear pago automáticamente para la venta (mapear forma_pago al enum de payments)
        $formaPagoPayment = match($data['forma_pago']) {
            'tarjeta' => 'tarjeta',
            'transferencia' => 'transferencia',
            default => 'efectivo'
        };
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
