<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\RoutePlan;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function index()
    {
        // Lista todos los pedidos con rutas calculadas o pendientes
        $pedidos = Order::whereNotNull('direccion_entrega')
            ->with(['routePlan', 'items.product'])
            ->latest()
            ->paginate(15);

        $mapsKey = config('services.google.maps_key');

        // Si no hay API key, mostrar advertencia pero no bloquear
        if (empty($mapsKey)) {
            \Illuminate\Support\Facades\Log::warning('Google Maps API key no configurada en rutas/index');
        }

        return view('rutas.index', compact('pedidos', 'mapsKey'));
    }

    public function compute(Order $pedido)
    {
        // Requiere que el pedido tenga lat/lng o al menos dirección para geocodificar
        $origin = [
            'lat' => (float) (env('WAREHOUSE_ORIGIN_LAT', 19.432608)),
            'lng' => (float) (env('WAREHOUSE_ORIGIN_LNG', -99.133209)),
        ];

        // Si no tiene coordenadas, intentar usar la dirección para geocodificar
        $dest = [
            'lat' => (float) ($pedido->lat ?? 0),
            'lng' => (float) ($pedido->lng ?? 0),
            'address' => $pedido->direccion_entrega ?? '',
        ];

        $mapsKey = config('services.google.maps_key');

        if (empty($mapsKey)) {
            return redirect()->route('pedidos.show', $pedido)
                ->with('error', 'La API key de Google Maps no está configurada. Por favor, configure GOOGLE_MAPS_API_KEY en el archivo .env');
        }

        return view('rutas.show', compact('pedido','origin','dest','mapsKey'));
    }
}
