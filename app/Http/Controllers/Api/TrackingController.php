<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DriverLocation;
use App\Events\DriverLocationUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TrackingController extends Controller
{
    /**
     * Actualizar la ubicación del chofer
     */
    public function updateLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'speed' => 'nullable|numeric|min:0',
            'heading' => 'nullable|numeric|between:0,360',
            'accuracy' => 'nullable|numeric|min:0',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Desactivar ubicaciones anteriores del chofer
        DriverLocation::where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        // Crear nueva ubicación
        $location = DriverLocation::create([
            'user_id' => $request->user()->id,
            'order_id' => $request->order_id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'speed' => $request->speed,
            'heading' => $request->heading,
            'accuracy' => $request->accuracy,
            'is_active' => true,
        ]);

        // Cargar la relación del usuario para el evento
        $location->load('user');

        // Transmitir el evento en tiempo real
        broadcast(new DriverLocationUpdated($location))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Ubicación actualizada correctamente',
            'data' => [
                'id' => $location->id,
                'timestamp' => $location->created_at->toIso8601String(),
            ]
        ]);
    }

    /**
     * Obtener la última ubicación de un chofer
     */
    public function getDriverLocation($driverId)
    {
        $location = DriverLocation::with('user', 'order')
            ->latestForDriver($driverId)
            ->active()
            ->recent()
            ->first();

        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró ubicación reciente para este chofer'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'driver_id' => $location->user_id,
                'driver_name' => $location->user->name,
                'order_id' => $location->order_id,
                'latitude' => (float) $location->latitude,
                'longitude' => (float) $location->longitude,
                'speed' => $location->speed ? (float) $location->speed : null,
                'heading' => $location->heading ? (float) $location->heading : null,
                'accuracy' => $location->accuracy ? (float) $location->accuracy : null,
                'timestamp' => $location->created_at->toIso8601String(),
            ]
        ]);
    }

    /**
     * Obtener todas las ubicaciones activas de choferes
     */
    public function getAllActiveDrivers()
    {
        $locations = DriverLocation::with('user', 'order')
            ->active()
            ->recent()
            ->get()
            ->groupBy('user_id')
            ->map(function ($userLocations) {
                return $userLocations->sortByDesc('created_at')->first();
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $locations->map(function ($location) {
                return [
                    'driver_id' => $location->user_id,
                    'driver_name' => $location->user->name,
                    'order_id' => $location->order_id,
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

    /**
     * Desactivar el tracking (cuando el chofer termina su jornada)
     */
    public function stopTracking(Request $request)
    {
        DriverLocation::where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Tracking desactivado correctamente'
        ]);
    }
}
