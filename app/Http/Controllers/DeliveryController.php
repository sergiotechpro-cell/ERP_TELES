<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\SerialNumber;
use App\Models\ScanLog;
use App\Models\DeliveryAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeliveryController extends Controller
{
    public function assign(Request $r)
    {
        $pedido = Order::findOrFail($r->order_id);
        DeliveryAssignment::create([
            'order_id' => $pedido->id,
            'courier_id' => $r->courier_id,
            'asignado_at' => now(),
            'estado' => 'pendiente'
        ]);
        $pedido->update(['estado'=>'asignado']);
        return back()->with('ok','Repartidor asignado.');
    }

    public function scan(Request $r)
    {
        $serial = SerialNumber::where('numero_serie',$r->numero_serie)->firstOrFail();
        $tipo = $r->tipo;
        if($tipo=='salida_bodega' && $serial->estado=='disponible'){
            $serial->update(['estado'=>'apartado']);
        } elseif($tipo=='entrega_cliente' && $serial->estado=='apartado'){
            $serial->update(['estado'=>'entregado']);
        }

        ScanLog::create([
            'order_id' => $r->order_id,
            'serial_number_id' => $serial->id,
            'user_id' => Auth::id(),
            'tipo' => $tipo,
            'scanned_at' => now(),
            'meta' => $r->meta
        ]);
        return back()->with('ok','Escaneo registrado.');
    }
}
