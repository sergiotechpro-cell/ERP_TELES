<?php

namespace App\Http\Controllers;

use App\Models\Order;

class RouteController extends Controller
{
    public function compute(Order $pedido)
    {
        // Requiere que el pedido tenga lat/lng
        // Si tus orders no tienen columnas lat/lng, dímelo y te paso una migración para agregarlas.
        $pedido->load('customer');

        $origin = [
            'lat' => (float) (env('WAREHOUSE_ORIGIN_LAT', 19.432608)),
            'lng' => (float) (env('WAREHOUSE_ORIGIN_LNG', -99.133209)),
        ];

        $dest = [
            'lat' => (float) ($pedido->lat ?? 0),
            'lng' => (float) ($pedido->lng ?? 0),
            'address' => $pedido->direccion_entrega,
        ];

        $mapsKey = config('services.google.maps_key');

        return view('rutas.show', compact('pedido','origin','dest','mapsKey'));
    }
}
