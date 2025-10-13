<?php
namespace App\Http\Controllers;

use App\Models\{DeliverySchedule, Order};
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
  public function index() {
    $events = DeliverySchedule::with('order')->get()->map(function($e){
      return [
        'id'=>$e->id,
        'title'=>$e->titulo ?? ('Pedido #'.$e->order_id),
        'start'=>$e->fecha->toDateString().($e->hora?('T'.$e->hora):''),
        'url'=>route('pedidos.show',$e->order_id),
      ];
    });
    return view('logistica.calendario', ['events'=>$events]);
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
