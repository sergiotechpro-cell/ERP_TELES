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
            'precio_mayoreo'   => ['nullable','numeric','min:0'],
            'price_tier'       => ['nullable','in:menudeo,mayoreo'],
            'almacenes'        => ['nullable','array'],
            'almacenes.*.warehouse_id' => ['required_with:almacenes','exists:warehouses,id'],
            'almacenes.*.cantidad'     => ['required_with:almacenes','integer','min:1'],
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

    /* ========= Gestión de Bodegas ========= */

    public function indexWarehouses()
    {
        $bodegas = Warehouse::orderBy('nombre')->get();
        return view('inventario.warehouses.index', compact('bodegas'));
    }

    public function createWarehouse()
    {
        $mapsKey = config('services.google.maps_key');
        return view('inventario.warehouses.create', compact('mapsKey'));
    }

    public function storeWarehouse(Request $r)
    {
        $data = $r->validate([
            'nombre'    => ['required','string','max:100'],
            'direccion' => ['required','string','max:255'],
            'lat'       => ['required','numeric'],
            'lng'       => ['required','numeric'],
        ], [
            'direccion.required' => 'La dirección de la bodega es obligatoria.',
            'lat.required' => 'Las coordenadas son obligatorias. Selecciona una dirección válida del autocompletado.',
            'lng.required' => 'Las coordenadas son obligatorias. Selecciona una dirección válida del autocompletado.',
        ]);

        Warehouse::create($data);
        
        $crearOtro = $r->input('crear_otro', false);
        if ($crearOtro) {
            return redirect()->route('inventario.warehouses.create')->with('ok','Bodega creada con coordenadas. Puedes crear otra bodega.');
        }
        
        return redirect()->route('inventario.warehouses.index')->with('ok','Bodega creada con coordenadas.');
    }

    public function editWarehouse(Warehouse $warehouse)
    {
        $mapsKey = config('services.google.maps_key');
        return view('inventario.warehouses.edit', compact('warehouse', 'mapsKey'));
    }

    public function updateWarehouse(Request $r, Warehouse $warehouse)
    {
        $data = $r->validate([
            'nombre'    => ['required','string','max:100'],
            'direccion' => ['required','string','max:255'],
            'lat'       => ['required','numeric'],
            'lng'       => ['required','numeric'],
        ], [
            'direccion.required' => 'La dirección de la bodega es obligatoria.',
            'lat.required' => 'Las coordenadas son obligatorias. Selecciona una dirección válida del autocompletado.',
            'lng.required' => 'Las coordenadas son obligatorias. Selecciona una dirección válida del autocompletado.',
        ]);

        $warehouse->update($data);
        return redirect()->route('inventario.warehouses.index')->with('ok','Bodega actualizada.');
    }

    public function destroyWarehouse(Warehouse $warehouse)
    {
        // Verificar si la bodega tiene productos asignados
        $productosCount = $warehouse->warehouseProducts()->count();
        
        if ($productosCount > 0) {
            return back()->with('error', "No se puede eliminar la bodega. Tiene {$productosCount} producto(s) asignado(s).");
        }

        $warehouse->delete();
        return redirect()->route('inventario.warehouses.index')->with('ok','Bodega eliminada.');
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
