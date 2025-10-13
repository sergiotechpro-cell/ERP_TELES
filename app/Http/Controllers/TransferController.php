<?php
namespace App\Http\Controllers;

use App\Models\{Transfer, TransferItem, Warehouse, WarehouseProduct, SerialNumber, Product};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
  public function index() {
    $traspasos = Transfer::with(['from','to'])->latest()->paginate(15);
    return view('inventario.traspasos.index', compact('traspasos'));
  }

  public function create() {
    return view('inventario.traspasos.create', [
      'bodegas'=>Warehouse::orderBy('nombre')->get(),
      'productos'=>Product::orderBy('descripcion')->get()
    ]);
  }

  public function store(Request $r) {
    DB::transaction(function() use ($r){
      $t = Transfer::create([
        'warehouse_from_id'=>$r->bodega_origen,
        'warehouse_to_id'=>$r->bodega_destino,
        'created_by'=>Auth::id(),
        'estado'=>'en_transito',
        'enviado_at'=>now(),
        'nota'=>$r->nota
      ]);

      foreach($r->items as $it){
        TransferItem::create([
          'transfer_id'=>$t->id,
          'product_id'=>$it['product_id'],
          'cantidad'=>$it['cantidad'],
          'seriales'=>$it['seriales'] ?? []
        ]);

        // descuenta stock en origen
        $wp = WarehouseProduct::where('warehouse_id',$r->bodega_origen)->where('product_id',$it['product_id'])->lockForUpdate()->first();
        if($wp){ $wp->decrement('stock', (int)$it['cantidad']); }

        // marca seriales como "apartado"
        if(!empty($it['seriales'])){
          SerialNumber::whereIn('numero_serie',$it['seriales'])->update(['estado'=>'apartado']);
        }
      }
    });
    return redirect()->route('traspasos.index')->with('ok','Traspaso creado y enviado.');
  }

  public function receive(Transfer $traspaso) {
    DB::transaction(function() use ($traspaso){
      foreach($traspaso->items as $it){
        $wp = WarehouseProduct::firstOrCreate([
          'warehouse_id'=>$traspaso->warehouse_to_id,
          'product_id'=>$it->product_id
        ],['stock'=>0]);
        $wp->increment('stock', $it->cantidad);

        if($it->seriales){
          SerialNumber::whereIn('numero_serie',$it->seriales)->update(['estado'=>'disponible']);
        }
      }
      $traspaso->update(['estado'=>'completado','recibido_at'=>now()]);
    });
    return back()->with('ok','Traspaso recibido en bodega destino.');
  }
}
