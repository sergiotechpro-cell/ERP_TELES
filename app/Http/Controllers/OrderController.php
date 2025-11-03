<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\ChecklistItem;
use App\Models\Customer;
use App\Models\DeliveryAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $pedidos = Order::with('assignment.courier')->latest()->paginate(10);
        return view('pedidos.index', compact('pedidos'));
    }

    public function create()
    {
        // Validar elementos necesarios
        $productos = Product::with('warehouses')->orderBy('descripcion')->get([
            'id','descripcion','precio_venta','costo_unitario','price_tier','precio_mayoreo'
        ]);
        
        // Calcular stock total para cada producto
        $productos = $productos->map(function($p) {
            $p->stock_total = $p->warehouses->sum(fn($w) => $w->pivot->stock ?? 0);
            return $p;
        });
        
        $bodegas   = Warehouse::orderBy('nombre')->get(['id','nombre']);
        
        // Verificar choferes y clientes
        $choferes = \App\Models\User::whereHas('employeeProfile')->get(['id','name','email']);
        $clientes = Customer::orderBy('nombre')->get(['id','nombre','telefono','email','direccion_entrega']);
        
        // Variables de validación
        $hasProductos = $productos->count() > 0;
        $hasBodegas = $bodegas->count() > 0;
        $hasChoferes = $choferes->count() > 0;
        $hasClientes = $clientes->count() > 0;
        $canProceed = $hasProductos && $hasBodegas; // Mínimo requerido

        // PREPARAMOS arrays "planos" para JS (evita @php en el blade)
        $prodRows = $productos->map(function ($p) {
            $precio = ($p->price_tier === 'mayoreo' && $p->precio_mayoreo)
                ? $p->precio_mayoreo
                : $p->precio_venta;

            return [
                'id'          => $p->id,
                'descripcion' => $p->descripcion,
                'precio'      => (float) $precio,
                'costo'       => (float) $p->costo_unitario,
                'stock'       => (int) $p->stock_total,
            ];
        })->values();

        $bodRows = $bodegas->map(function ($b) {
            return ['id' => $b->id, 'nombre' => $b->nombre];
        })->values();
        
        $cliRows = $clientes->map(function ($c) {
            return [
                'id' => $c->id,
                'nombre' => $c->nombre,
                'telefono' => $c->telefono ?? '',
                'email' => $c->email ?? '',
                'direccion' => $c->direccion_entrega ?? '',
            ];
        })->values();

        // API Key para Google Maps/Places
        $mapsKey = config('services.google.maps_key');
        
        // Obtener coordenadas de origen desde bodega principal o variables de entorno
        $warehouse = Warehouse::whereNotNull('lat')
            ->whereNotNull('lng')
            ->orderBy('id')
            ->first();
        
        if ($warehouse) {
            $originLat = (float) $warehouse->lat;
            $originLng = (float) $warehouse->lng;
            $originName = $warehouse->nombre;
        } else {
            // Fallback a variables de entorno o CDMX por defecto
            $originLat = (float) (env('WAREHOUSE_ORIGIN_LAT', 19.432608));
            $originLng = (float) (env('WAREHOUSE_ORIGIN_LNG', -99.133209));
            $originName = 'Bodega Principal';
        }

        return view('pedidos.create', compact(
            'productos','bodegas','prodRows','bodRows','cliRows','choferes','clientes','mapsKey',
            'originLat','originLng','originName',
            'hasProductos','hasBodegas','hasChoferes','hasClientes','canProceed'
        ));
    }

    public function store(Request $r)
    {
        // Validar elementos necesarios antes de proceder
        $productosCount = Product::count();
        $bodegasCount = Warehouse::count();
        $choferesCount = \App\Models\User::whereHas('employeeProfile')->count();
        
        if ($productosCount === 0) {
            return back()->withErrors(['error' => 'No hay productos registrados. Debes crear al menos un producto primero.'])->withInput();
        }
        
        if ($bodegasCount === 0) {
            return back()->withErrors(['error' => 'No hay bodegas registradas. Debes crear al menos una bodega primero.'])->withInput();
        }
        
        // Para pedidos, es recomendable tener choferes
        if ($choferesCount === 0) {
            return back()->withErrors(['error' => 'No hay choferes registrados. Debes crear al menos un empleado chofer primero.'])->withInput();
        }
        
        // Validación del payload del formulario
        $data = $r->validate([
            'customer_id'       => ['nullable','integer','exists:customers,id'],
            'direccion_entrega' => ['required','string','max:255'],
            'costo_envio'       => ['nullable','numeric','min:0'],
            'cliente_nombre'    => ['nullable','string','max:255'],
            'cliente_telefono'  => ['nullable','string','max:50'],
            'courier_id'        => ['nullable','integer','exists:users,id'],
            'lat'               => ['nullable','numeric'],
            'lng'               => ['nullable','numeric'],

            'productos'               => ['required','array','min:1'],
            'productos.*.id'          => ['required','exists:products,id'],
            'productos.*.bodega_id'   => ['required','exists:warehouses,id'],
            'productos.*.cantidad'    => ['required','integer','min:1'],
            'productos.*.precio'      => ['required','numeric','min:0'],
            'productos.*.costo'       => ['nullable','numeric','min:0'],
        ]);

        // Obtener primera bodega disponible para asignar productos
        $primeraBodega = Warehouse::orderBy('id')->first();
        if (!$primeraBodega) {
            return back()->withErrors(['error' => 'No hay bodegas registradas.'])->withInput();
        }

        DB::transaction(function () use ($data, $primeraBodega, &$pedidoId, &$choferAsignado) {
            $pedido = Order::create([
                'customer_id'       => $data['customer_id'] ?? null,
                'created_by'        => Auth::id(),
                'estado'            => 'capturado',
                'direccion_entrega' => $data['direccion_entrega'],
                'costo_envio'       => $data['costo_envio'] ?? 0,
                'lat'               => $data['lat'] ?? null,
                'lng'               => $data['lng'] ?? null,
            ]);
            $pedidoId = $pedido->id;

            foreach ($data['productos'] as $p) {
                $prod = Product::with('warehouses')->find($p['id']);
                
                // Validar stock antes de crear el item
                $stockDisponible = $prod->warehouses->sum(fn($w) => $w->pivot->stock ?? 0);
                $cantidadSolicitada = (int) $p['cantidad'];
                
                if ($cantidadSolicitada > $stockDisponible) {
                    throw new \Exception("Stock insuficiente para {$prod->descripcion}. Disponible: {$stockDisponible}, Solicitado: {$cantidadSolicitada}");
                }
                
                OrderItem::create([
                    'order_id'        => $pedido->id,
                    'product_id'      => $p['id'],
                    'warehouse_id'    => $p['bodega_id'] ?? $primeraBodega->id,
                    'cantidad'        => $cantidadSolicitada,
                    'precio_unitario' => (float) $p['precio'],
                    'costo_unitario'  => (float) ($p['costo'] ?? 0),
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
                    'texto'       => 'Verificar ' . ($prod->descripcion ?? 'producto'),
                    'completado'  => false,
                ]);
            }
            
            // Asignar chofer si se proporcionó
            $choferAsignado = false;
            if (!empty($data['courier_id']) && \App\Models\User::whereHas('employeeProfile')->where('id', $data['courier_id'])->exists()) {
                DeliveryAssignment::create([
                    'order_id' => $pedido->id,
                    'courier_id' => $data['courier_id'],
                    'asignado_at' => now(),
                    'estado' => 'pendiente'
                ]);
                $pedido->update(['estado' => 'asignado']);
                $choferAsignado = true;
            }
        });

        $mensaje = isset($choferAsignado) && $choferAsignado 
            ? "Pedido creado (#{$pedidoId}) con chofer asignado. Ya aparece en el calendario."
            : "Pedido creado (#{$pedidoId}). Asigna un chofer desde el detalle del pedido para planificar la ruta.";
        
        return redirect()->route('pedidos.index')->with('ok', $mensaje);
    }

    public function show(Order $pedido)
    {
        $pedido->load(['items.product', 'items.warehouse', 'checklistItems', 'payments', 'assignment.courier']);
        
        // Empleados disponibles para asignar
        $empleados = \App\Models\User::orderBy('name')->get(['id', 'name', 'email']);
        
        return view('pedidos.show', compact('pedido', 'empleados'));
    }

    public function update(Request $r, Order $pedido)
    {
        $pedido->update($r->only(['estado']));
        return back()->with('ok', 'Estado de pedido actualizado.');
    }

    public function toggleChecklist(Request $r, Order $pedido, ChecklistItem $item)
    {
        $item->update([
            'completado' => !$item->completado,
            'completed_at' => !$item->completado ? now() : null,
            'completed_by' => !$item->completado ? Auth::id() : null,
        ]);
        
        return back()->with('ok', 'Checklist actualizado.');
    }

    public function destroy(Order $pedido)
    {
        $pedido->delete();
        return back()->with('ok', 'Pedido eliminado.');
    }
}
