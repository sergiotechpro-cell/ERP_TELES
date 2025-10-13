<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $pedidos = Order::with('customer')->latest()->paginate(10);
        return view('pedidos.index', compact('pedidos'));
    }

    public function create()
    {
        // Datos base para selects
        $clientes  = Customer::orderBy('nombre')->get(['id','nombre','email','telefono']);
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

        return view('pedidos.create', compact('clientes','productos','bodegas','prodRows','bodRows'));
    }

    public function store(Request $r)
    {
        // Validación del payload del formulario
        $data = $r->validate([
            'cliente_id'        => ['required','exists:customers,id'],
            'direccion_entrega' => ['required','string','max:255'],
            'costo_envio'       => ['nullable','numeric','min:0'],

            'productos'               => ['required','array','min:1'],
            'productos.*.id'          => ['required','exists:products,id'],
            'productos.*.bodega_id'   => ['required','exists:warehouses,id'],
            'productos.*.cantidad'    => ['required','integer','min:1'],
            'productos.*.precio'      => ['required','numeric','min:0'],
            'productos.*.costo'       => ['nullable','numeric','min:0'],
        ]);

        DB::transaction(function () use ($data) {
            $pedido = Order::create([
                'customer_id'       => $data['cliente_id'],
                'created_by'        => Auth::id(),
                'estado'            => 'capturado',
                'direccion_entrega' => $data['direccion_entrega'],
                'costo_envio'       => $data['costo_envio'] ?? 0,
            ]);

            foreach ($data['productos'] as $p) {
                OrderItem::create([
                    'order_id'        => $pedido->id,
                    'product_id'      => $p['id'],
                    'warehouse_id'    => $p['bodega_id'],
                    'cantidad'        => (int) $p['cantidad'],
                    'precio_unitario' => (float) $p['precio'],
                    'costo_unitario'  => (float) ($p['costo'] ?? 0),
                ]);
            }
        });

        return redirect()->route('pedidos.index')->with('ok', 'Pedido registrado.');
    }

    public function show(Order $pedido)
    {
        $pedido->load(['items.product','customer']);
        return view('pedidos.show', compact('pedido'));
    }

    public function update(Request $r, Order $pedido)
    {
        $pedido->update($r->only(['estado']));
        return back()->with('ok', 'Estado de pedido actualizado.');
    }

    public function destroy(Order $pedido)
    {
        $pedido->delete();
        return back()->with('ok', 'Pedido eliminado.');
    }
}
