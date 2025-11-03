<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        $bodegas = Warehouse::withCount('warehouseProducts')
            ->orderBy('nombre')
            ->paginate(15);
        
        return view('bodegas.index', compact('bodegas'));
    }

    public function create()
    {
        $mapsKey = config('services.google.maps_key');
        return view('bodegas.create', compact('mapsKey'));
    }

    public function store(Request $r)
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
            return redirect()->route('bodegas.create')->with('ok','Bodega creada con coordenadas. Puedes crear otra bodega.');
        }
        
        return redirect()->route('bodegas.index')->with('ok','Bodega creada con coordenadas.');
    }

    public function show(Warehouse $bodega)
    {
        $bodega->loadCount('warehouseProducts');
        return view('bodegas.show', compact('bodega'));
    }

    public function edit(Warehouse $bodega)
    {
        $mapsKey = config('services.google.maps_key');
        return view('bodegas.edit', compact('bodega', 'mapsKey'));
    }

    public function update(Request $r, Warehouse $bodega)
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

        $bodega->update($data);
        return redirect()->route('bodegas.index')->with('ok','Bodega actualizada.');
    }

    public function destroy(Warehouse $bodega)
    {
        // Verificar si la bodega tiene productos asignados
        $productosCount = $bodega->warehouseProducts()->count();
        
        if ($productosCount > 0) {
            return back()->with('error', "No se puede eliminar la bodega. Tiene {$productosCount} producto(s) asignado(s).");
        }

        $bodega->delete();
        return redirect()->route('bodegas.index')->with('ok','Bodega eliminada.');
    }
}

