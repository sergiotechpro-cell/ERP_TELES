<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use App\Models\SerialNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index()
    {
        $productos = Product::with(['warehouses' => fn($q) => $q->withPivot('stock')])
            ->orderBy('descripcion')
            ->paginate(10);

        return view('inventario.index', compact('productos'));
    }

    public function create()
    {
        // Traemos bodegas para el select
        $bodegas = Warehouse::orderBy('nombre')->get();
        return view('inventario.create', compact('bodegas'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'descripcion'      => ['required','string','max:255'],
            'costo_unitario'   => ['required','numeric','min:0'],
            'precio_venta'     => ['required','numeric','min:0'],
            'precio_mayoreo'   => ['nullable','numeric','min:0'],
            'price_tier'       => ['nullable','in:menudeo,mayoreo'],
            'bodega_id'        => ['nullable','exists:warehouses,id'],
            'cantidad'         => ['nullable','integer','min:0'],
        ]);

        DB::transaction(function () use ($data) {

            $producto = Product::create([
                'descripcion'     => $data['descripcion'],
                'costo_unitario'  => $data['costo_unitario'],
                'precio_venta'    => $data['precio_venta'],
                'precio_mayoreo'  => $data['precio_mayoreo'] ?? null,
                'price_tier'      => $data['price_tier'] ?? 'menudeo',
            ]);

            // Stock inicial (opcional)
            $cantidad = (int)($data['cantidad'] ?? 0);
            $bodegaId = $data['bodega_id'] ?? null;

            if ($bodegaId && $cantidad > 0) {
                $wp = WarehouseProduct::firstOrCreate(
                    ['warehouse_id' => $bodegaId, 'product_id' => $producto->id],
                    ['stock' => 0]
                );
                $wp->increment('stock', $cantidad);

                // Crear números de serie automáticos
                for ($i=0; $i<$cantidad; $i++) {
                    SerialNumber::create([
                        'warehouse_product_id' => $wp->id,
                        'numero_serie'         => strtoupper(uniqid('SN-')),
                    ]);
                }
            }
        });

        return redirect()->route('inventario.index')->with('ok', 'Producto agregado correctamente.');
    }

    public function show(Product $inventario)
    {
        $producto = $inventario->load(['warehouses' => fn($q) => $q->withPivot('stock')]);
        return view('inventario.show', compact('producto'));
    }

    public function edit(Product $inventario)
    {
        $bodegas = Warehouse::orderBy('nombre')->get();
        return view('inventario.edit', compact('inventario','bodegas'));
    }

    public function update(Request $r, Product $inventario)
    {
        $data = $r->validate([
            'descripcion'     => ['required','string','max:255'],
            'costo_unitario'  => ['required','numeric','min:0'],
            'precio_venta'    => ['required','numeric','min:0'],
            'precio_mayoreo'  => ['nullable','numeric','min:0'],
            'price_tier'      => ['nullable','in:menudeo,mayoreo'],
        ]);

        $inventario->update($data);
        return redirect()->route('inventario.index')->with('ok','Producto actualizado.');
    }

    public function destroy(Product $inventario)
    {
        $inventario->delete();
        return back()->with('ok','Producto eliminado.');
    }

    /* ========= Alta de Bodegas desde la UI ========= */

    public function createWarehouse()
    {
        return view('inventario.warehouse_create');
    }

    public function storeWarehouse(Request $r)
    {
        $data = $r->validate([
            'nombre'    => ['required','string','max:100'],
            'direccion' => ['nullable','string','max:255'],
        ]);

        Warehouse::create($data);
        return redirect()->route('inventario.index')->with('ok','Bodega creada.');
    }

    /* ========= Alta de Stock desde la UI (opcional) ========= */

    public function createStock()
    {
        $productos = Product::orderBy('descripcion')->get();
        $bodegas   = Warehouse::orderBy('nombre')->get();
        return view('inventario.stock_create', compact('productos','bodegas'));
    }

    public function storeStock(Request $r)
    {
        $data = $r->validate([
            'product_id'   => ['required','exists:products,id'],
            'warehouse_id' => ['required','exists:warehouses,id'],
            'cantidad'     => ['required','integer','min:1'],
            'seriales'     => ['nullable','string'],
        ]);

        DB::transaction(function () use ($data) {
            $wp = WarehouseProduct::firstOrCreate(
                ['warehouse_id' => $data['warehouse_id'], 'product_id' => $data['product_id']],
                ['stock' => 0]
            );
            $wp->increment('stock', (int)$data['cantidad']);

            // series (uno por línea, si se enviaron)
            if (!empty($data['seriales'])) {
                $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $data['seriales']))));
                foreach ($lines as $sn) {
                    SerialNumber::create([
                        'warehouse_product_id' => $wp->id,
                        'numero_serie'         => strtoupper($sn),
                    ]);
                }
            }
        });

        return redirect()->route('inventario.index')->with('ok','Stock agregado.');
    }
}
