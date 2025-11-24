<?php

namespace App\Http\Controllers;

use App\Models\WarrantyClaim;
use App\Models\SerialNumber;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WarrantyController extends Controller
{
    public function index()
    {
        $claims = WarrantyClaim::with(['order','product','serialNumber'])
            ->latest()->paginate(15);

        return view('garantias.index', compact('claims'));
    }

    public function create()
    {
        // Para evitar listas gigantes: sólo pedidos recientes
        $pedidos   = Order::latest()->limit(50)->get(['id','customer_id','direccion_entrega','created_at']);
        $productos = Product::orderBy('descripcion')->get(['id','descripcion']);
        $seriales  = collect(); // Se llenan por JS según producto seleccionado

        return view('garantias.create', compact('pedidos','productos','seriales'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'order_id'          => ['required','integer','exists:orders,id'],
            'product_id'        => ['required','integer','exists:products,id'],
            'serial_number_id'  => ['nullable','integer','exists:serial_numbers,id'],
            'numero_serie'      => ['nullable','string','max:100'],
            'motivo'            => ['required','string','max:255'],
            'condicion'         => ['nullable','string','max:120'],
            'fecha_compra'      => ['required','date'], // <-- importante
        ]);

        // Si no vino serial_number_id pero sí numero_serie (texto), lo resolvemos a ID
        if (empty($data['serial_number_id']) && !empty($data['numero_serie'])) {
            $sn = SerialNumber::where('numero_serie', $data['numero_serie'])->first();
            if (!$sn) {
                return back()
                    ->withErrors(['numero_serie' => 'El número de serie no existe.'])
                    ->withInput();
            }
            $data['serial_number_id'] = $sn->id;
        }
        unset($data['numero_serie']);

        $claim = WarrantyClaim::create($data + ['status' => 'abierta']);

        return redirect()
            ->route('garantias.index')
            ->with('ok', 'Garantía registrada (#'.$claim->id.').');
    }

    public function module(Request $request)
    {
        $serialQuery = strtoupper(trim($request->input('serial', '')));
        $serialResult = null;

        if ($serialQuery !== '') {
            $serial = SerialNumber::with('warehouseProduct.product')
                ->where('numero_serie', $serialQuery)
                ->first();

            if ($serial) {
                $lastClaim = WarrantyClaim::with(['order.customer','product'])
                    ->where('serial_number_id', $serial->id)
                    ->latest()
                    ->first();

                $serialResult = [
                    'found' => true,
                    'serial' => $serial,
                    'claim' => $lastClaim,
                ];
            } else {
                $serialResult = [
                    'found' => false,
                    'serial' => null,
                    'claim' => null,
                ];
            }
        }

        $openClaims = WarrantyClaim::with(['product','serialNumber','order.customer'])
            ->where('status', '!=', 'cerrada')
            ->latest()
            ->limit(10)
            ->get();

        return view('garantias.module', [
            'serialQuery' => $serialQuery,
            'serialResult' => $serialResult,
            'openClaims' => $openClaims,
        ]);
    }

    public function close(WarrantyClaim $garantia)
    {
        if ($garantia->status === 'cerrada') {
            throw ValidationException::withMessages([
                'garantia' => 'Esta garantía ya está cerrada.',
            ]);
        }

        $garantia->update(['status' => 'cerrada']);

        return back()->with('ok', 'Garantía #' . $garantia->id . ' cerrada correctamente.');
    }
}
