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
        // Filtrar almacenes vacíos antes de validar
        $almacenes = $r->input('almacenes', []);
        $almacenesFiltrados = array_filter($almacenes, function($alm) {
            return !empty($alm['warehouse_id']) && !empty($alm['cantidad']);
        });
        $r->merge(['almacenes' => array_values($almacenesFiltrados)]);

        $data = $r->validate([
            'descripcion'      => ['required','string','max:255'],
            'costo_unitario'   => ['required','numeric','min:0'],
            'precio_venta'     => ['required','numeric','min:0'],
            'precio_mayoreo'   => ['required','numeric','min:0'],
            'price_tier'       => ['required','in:menudeo,mayoreo'],
            'almacenes'        => ['required','array','min:1'],
            'almacenes.*.warehouse_id' => ['required','exists:warehouses,id'],
            'almacenes.*.cantidad'     => ['required','integer','min:1'],
        ], [
            'descripcion.required' => 'La descripción del producto es obligatoria.',
            'costo_unitario.required' => 'El costo unitario es obligatorio.',
            'precio_venta.required' => 'El precio de venta es obligatorio.',
            'precio_mayoreo.required' => 'El precio de mayoreo es obligatorio.',
            'price_tier.required' => 'Debes seleccionar el tipo de precio (menudeo o mayoreo).',
            'almacenes.required' => 'Debes asignar el producto a al menos una bodega.',
            'almacenes.min' => 'Debes asignar el producto a al menos una bodega.',
            'almacenes.*.warehouse_id.required' => 'Debes seleccionar una bodega.',
            'almacenes.*.cantidad.required' => 'La cantidad es obligatoria.',
        ]);

        DB::transaction(function () use ($data) {

            $producto = Product::create([
                'descripcion'     => $data['descripcion'],
                'costo_unitario'  => $data['costo_unitario'],
                'precio_venta'    => $data['precio_venta'],
                'precio_mayoreo'  => $data['precio_mayoreo'] ?? null,
                'price_tier'      => $data['price_tier'] ?? 'menudeo',
            ]);

            // Stock inicial en múltiples almacenes
            if (!empty($data['almacenes'])) {
                foreach ($data['almacenes'] as $almacen) {
                    $bodegaId = $almacen['warehouse_id'];
                    $cantidad = (int)($almacen['cantidad'] ?? 0);

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
                }
            }
        });

        $crearOtro = $r->input('crear_otro', false);
        if ($crearOtro) {
            return redirect()->route('inventario.create')->with('ok', 'Producto agregado correctamente. Puedes crear otro producto.');
        }

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
            } else {
                // Si no hay seriales, crear automáticamente
                for ($i = 0; $i < (int)$data['cantidad']; $i++) {
                    SerialNumber::create([
                        'warehouse_product_id' => $wp->id,
                        'numero_serie'         => strtoupper(uniqid('SN-')),
                    ]);
                }
            }
        });

        return redirect()->route('inventario.index')->with('ok','Stock agregado.');
    }

    /* ========= Agregar unidades a producto existente ========= */
    
    public function addStock(Product $inventario)
    {
        $bodegas = Warehouse::orderBy('nombre')->get();
        return view('inventario.add_stock', compact('inventario', 'bodegas'));
    }

    public function storeAddStock(Request $r, Product $inventario)
    {
        $data = $r->validate([
            'warehouse_id' => ['required','exists:warehouses,id'],
            'cantidad'     => ['required','integer','min:1'],
            'seriales'     => ['nullable','string'],
        ]);

        DB::transaction(function () use ($data, $inventario) {
            $wp = WarehouseProduct::firstOrCreate(
                ['warehouse_id' => $data['warehouse_id'], 'product_id' => $inventario->id],
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
            } else {
                // Si no hay seriales, crear automáticamente
                for ($i = 0; $i < (int)$data['cantidad']; $i++) {
                    SerialNumber::create([
                        'warehouse_product_id' => $wp->id,
                        'numero_serie'         => strtoupper(uniqid('SN-')),
                    ]);
                }
            }
        });

        return redirect()->route('inventario.show', $inventario)->with('ok','Unidades agregadas al producto.');
    }
}
