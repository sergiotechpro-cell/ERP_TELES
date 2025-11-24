<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DriverLocation;

class TrackingController extends Controller
{
    /**
     * Mostrar el mapa de tracking en tiempo real
     */
    public function map()
    {
        return view('tracking.map');
    }

    /**
     * Obtener historial de ubicaciones de un chofer
     */
    public function getDriverHistory($driverId)
    {
        $locations = DriverLocation::where('user_id', $driverId)
            ->where('created_at', '>=', now()->subHours(2)) // Últimas 2 horas
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $locations->map(function ($location) {
                return [
                    'latitude' => (float) $location->latitude,
                    'longitude' => (float) $location->longitude,
                    'timestamp' => $location->created_at->toIso8601String(),
                ];
            })
        ]);
    }

    /**
     * Obtener todas las ubicaciones activas de choferes (para web)
     */
    public function getActiveDrivers()
    {
        $locations = DriverLocation::with('user', 'order')
            ->where('is_active', true)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->get()
            ->groupBy('user_id')
            ->map(function ($userLocations) {
                return $userLocations->sortByDesc('created_at')->first();
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $locations->map(function ($location) {
                $order = $location->order;
                return [
                    'driver_id' => $location->user_id,
                    'driver_name' => $location->user->name,
                    'order_id' => $location->order_id,
                    'order_lat' => $order ? (float) $order->lat : null,
                    'order_lng' => $order ? (float) $order->lng : null,
                    'order_address' => $order ? $order->direccion_entrega : null,
                    'latitude' => (float) $location->latitude,
                    'longitude' => (float) $location->longitude,
                    'speed' => $location->speed ? (float) $location->speed : null,
                    'heading' => $location->heading ? (float) $location->heading : null,
                    'accuracy' => $location->accuracy ? (float) $location->accuracy : null,
                    'timestamp' => $location->created_at->toIso8601String(),
                ];
            })
        ]);
    }
}
