<?php
namespace App\Http\Controllers;

use App\Models\{DeliverySchedule, Order, DeliveryAssignment};
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
  public function index() {
    // Eventos de entregas programadas (DeliverySchedule)
    $scheduledEvents = DeliverySchedule::with(['order', 'courier'])
      ->get()
      ->map(function($e){
        $start = $e->fecha->toDateString();
        if ($e->hora) {
          $start .= 'T' . $e->hora;
        } else {
          $start .= 'T09:00:00'; // Hora por defecto si no hay
        }
        
        return [
          'id' => 'schedule_' . $e->id,
          'title' => $e->titulo ?? ('Pedido #'.$e->order_id),
          'start' => $start,
          'url' => route('pedidos.show', $e->order_id),
          'backgroundColor' => '#3b82f6', // Azul para programados
          'borderColor' => '#2563eb',
          'extendedProps' => [
            'type' => 'programado',
            'courier' => $e->courier->name ?? 'Sin asignar',
            'order_id' => $e->order_id,
          ]
        ];
      });

    // Eventos de entregas asignadas (DeliveryAssignment) - automáticos
    $assignedEvents = DeliveryAssignment::with(['order', 'courier'])
      ->whereIn('estado', ['pendiente', 'en_ruta'])
      ->get()
      ->map(function($a){
        // Usar fecha de asignación o created_at del pedido
        $fecha = $a->asignado_at ? $a->asignado_at->toDateString() : $a->order->created_at->toDateString();
        $hora = $a->asignado_at ? $a->asignado_at->format('H:i:s') : '09:00:00';
        
        $direccion = $a->order->direccion_entrega ? 
          (strlen($a->order->direccion_entrega) > 40 ? 
            substr($a->order->direccion_entrega, 0, 40) . '...' : 
            $a->order->direccion_entrega) : 
          'Sin dirección';
        
        $color = $a->estado === 'en_ruta' ? '#10b981' : '#f59e0b'; // Verde si en ruta, amarillo si pendiente
        
        return [
          'id' => 'assignment_' . $a->id,
          'title' => 'Pedido #' . $a->order->id . ' - ' . ($a->courier->name ?? 'Sin chofer'),
          'start' => $fecha . 'T' . $hora,
          'url' => route('pedidos.show', $a->order_id),
          'backgroundColor' => $color,
          'borderColor' => $color,
          'extendedProps' => [
            'type' => 'asignado',
            'courier' => $a->courier->name ?? 'Sin asignar',
            'order_id' => $a->order_id,
            'estado' => $a->estado,
            'direccion' => $direccion,
          ]
        ];
      });

    // Combinar ambos tipos de eventos
    $events = $scheduledEvents->merge($assignedEvents)->values();
    
    // Choferes para filtro
    $choferes = \App\Models\User::whereHas('employeeProfile')->get(['id', 'name']);
    
    return view('calendario.index', [
      'events' => $events,
      'choferes' => $choferes
    ]);
  }

  public function store(Request $r) {
    $data = $r->validate([
      'order_id'=>'required|exists:orders,id',
      'courier_id'=>'required|exists:users,id',
      'fecha'=>'required|date',
      'hora'=>'nullable'
    ]);
    DeliverySchedule::create($data + ['titulo'=>$r->titulo, 'meta'=>$r->meta]);
    Order::where('id',$r->order_id)->update(['estado'=>'asignado']);
    return back()->with('ok','Entrega programada en calendario.');
  }
}
