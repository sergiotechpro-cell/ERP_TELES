<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryAssignment;
use App\Models\Order;
use Illuminate\Http\Request;

class CourierController extends Controller
{
    /**
     * Listar pedidos asignados al chofer autenticado
     */
    public function assignments(Request $r)
    {
        $user = $r->user();

        $assignments = DeliveryAssignment::with([
            'order.items.product',
            'order.checklistItems'
        ])
            ->where('courier_id', $user->id)
            ->whereIn('estado', ['pendiente', 'en_ruta'])
            ->latest('asignado_at')
            ->get();

        return response()->json([
            'data' => $assignments->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'estado' => $assignment->estado,
                    'asignado_at' => $assignment->asignado_at,
                    'salida_at' => $assignment->salida_at,
                    'pedido' => [
                        'id' => $assignment->order->id,
                        'estado' => $assignment->order->estado,
                        'direccion_entrega' => $assignment->order->direccion_entrega,
                        'lat' => $assignment->order->lat,
                        'lng' => $assignment->order->lng,
                        'costo_envio' => $assignment->order->costo_envio,
                        'productos' => $assignment->order->items->map(function ($item) {
                            return [
                                'producto' => $item->product->descripcion,
                                'cantidad' => $item->cantidad,
                                'precio_unitario' => $item->precio_unitario,
                            ];
                        }),
                        'checklist' => $assignment->order->checklistItems->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'texto' => $item->texto,
                                'completado' => $item->completado,
                            ];
                        }),
                    ],
                ];
            })
        ]);
    }

    /**
     * Iniciar entrega (cambiar a en_ruta)
     */
    public function startAssignment(Request $r, DeliveryAssignment $assignment)
    {
        if ($assignment->courier_id !== $r->user()->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if ($assignment->estado !== 'pendiente') {
            return response()->json(['message' => 'La entrega ya fue iniciada'], 400);
        }

        $assignment->update([
            'estado' => 'en_ruta',
            'salida_at' => now(),
        ]);

        $assignment->order->update([
            'estado' => 'en_ruta'
        ]);

        return response()->json([
            'message' => 'Entrega iniciada',
            'assignment' => $assignment->fresh()
        ]);
    }

    /**
     * Completar entrega
     */
    public function completeAssignment(Request $r, DeliveryAssignment $assignment)
    {
        if ($assignment->courier_id !== $r->user()->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if ($assignment->estado !== 'en_ruta') {
            return response()->json(['message' => 'La entrega no ha sido iniciada'], 400);
        }

        $assignment->update([
            'estado' => 'entregado',
            'entregado_at' => now(),
        ]);

        $assignment->order->update([
            'estado' => 'entregado'
        ]);

        // Actualizar pagos del pedido a "completado"
        $assignment->order->payments()->update([
            'estado' => 'completado',
            'entregado_caja_at' => now()
        ]);

        return response()->json([
            'message' => 'Entrega completada',
            'assignment' => $assignment->fresh()
        ]);
    }

    /**
     * Ver detalle de una asignación específica
     */
    public function showAssignment(Request $r, DeliveryAssignment $assignment)
    {
        if ($assignment->courier_id !== $r->user()->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $assignment->load([
            'order.items.product',
            'order.checklistItems',
            'order.payments'
        ]);

        // Obtener coordenadas de origen desde bodega principal
        $warehouse = \App\Models\Warehouse::whereNotNull('lat')
            ->whereNotNull('lng')
            ->orderBy('id')
            ->first();
        
        if ($warehouse) {
            $origin = [
                'lat' => (float) $warehouse->lat,
                'lng' => (float) $warehouse->lng,
                'name' => $warehouse->nombre,
                'address' => $warehouse->direccion ?? 'Bodega Principal',
            ];
        } else {
            // Fallback a variables de entorno o CDMX por defecto
            $origin = [
                'lat' => (float) (env('WAREHOUSE_ORIGIN_LAT', 19.432608)),
                'lng' => (float) (env('WAREHOUSE_ORIGIN_LNG', -99.133209)),
                'name' => 'Bodega Principal',
                'address' => env('WAREHOUSE_ORIGIN_ADDRESS', 'Ciudad de México, México'),
            ];
        }

        return response()->json([
            'data' => [
                'id' => $assignment->id,
                'estado' => $assignment->estado,
                'asignado_at' => $assignment->asignado_at,
                'salida_at' => $assignment->salida_at,
                'entregado_at' => $assignment->entregado_at,
                'origen' => $origin,
                'pedido' => [
                    'id' => $assignment->order->id,
                    'estado' => $assignment->order->estado,
                    'direccion_entrega' => $assignment->order->direccion_entrega,
                    'lat' => $assignment->order->lat,
                    'lng' => $assignment->order->lng,
                    'costo_envio' => $assignment->order->costo_envio,
                    'productos' => $assignment->order->items->map(function ($item) {
                        return [
                            'producto' => $item->product->descripcion,
                            'cantidad' => $item->cantidad,
                            'precio_unitario' => $item->precio_unitario,
                        ];
                    }),
                    'checklist' => $assignment->order->checklistItems->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'texto' => $item->texto,
                            'completado' => $item->completado,
                        ];
                    }),
                    'pago' => $assignment->order->payments->first() ? [
                        'monto' => $assignment->order->payments->first()->monto,
                        'forma_pago' => $assignment->order->payments->first()->forma_pago,
                        'estado' => $assignment->order->payments->first()->estado,
                    ] : null,
                ],
            ]
        ]);
    }
}

