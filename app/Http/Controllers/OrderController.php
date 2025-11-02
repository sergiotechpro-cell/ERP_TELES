<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\ChecklistItem;
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
        // Datos base para selects
        $productos = Product::orderBy('descripcion')->get([
            'id','descripcion','precio_venta','costo_unitario','price_tier','precio_mayoreo'
        ]);
        $bodegas   = Warehouse::orderBy('nombre')->get(['id','nombre']);

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
            ];
        })->values();

        $bodRows = $bodegas->map(function ($b) {
            return ['id' => $b->id, 'nombre' => $b->nombre];
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

        return view('pedidos.create', compact('productos','bodegas','prodRows','bodRows','mapsKey','originLat','originLng','originName'));
    }

    public function store(Request $r)
    {
        // Validación del payload del formulario
        $data = $r->validate([
            'direccion_entrega' => ['required','string','max:255'],
            'costo_envio'       => ['nullable','numeric','min:0'],
            'cliente_nombre'    => ['nullable','string','max:255'],
            'cliente_telefono'  => ['nullable','string','max:50'],
            'lat'               => ['nullable','numeric'],
            'lng'               => ['nullable','numeric'],

            'productos'               => ['required','array','min:1'],
            'productos.*.id'          => ['required','exists:products,id'],
            'productos.*.bodega_id'   => ['required','exists:warehouses,id'],
            'productos.*.cantidad'    => ['required','integer','min:1'],
            'productos.*.precio'      => ['required','numeric','min:0'],
            'productos.*.costo'       => ['nullable','numeric','min:0'],
        ]);

        DB::transaction(function () use ($data) {
            $pedido = Order::create([
                'customer_id'       => null,
                'created_by'        => Auth::id(),
                'estado'            => 'capturado',
                'direccion_entrega' => $data['direccion_entrega'],
                'costo_envio'       => $data['costo_envio'] ?? 0,
                'lat'               => $data['lat'] ?? null,
                'lng'               => $data['lng'] ?? null,
            ]);

            foreach ($data['productos'] as $p) {
                $prod = Product::find($p['id']);
                
                OrderItem::create([
                    'order_id'        => $pedido->id,
                    'product_id'      => $p['id'],
                    'warehouse_id'    => $p['bodega_id'],
                    'cantidad'        => (int) $p['cantidad'],
                    'precio_unitario' => (float) $p['precio'],
                    'costo_unitario'  => (float) ($p['costo'] ?? 0),
                ]);
                
                // Crear checklist automático por producto
                ChecklistItem::create([
                    'order_id'    => $pedido->id,
                    'texto'       => 'Verificar ' . ($prod->descripcion ?? 'producto'),
                    'completado'  => false,
                ]);
            }
        });

        return redirect()->route('pedidos.index')->with('ok', 'Pedido registrado.');
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
