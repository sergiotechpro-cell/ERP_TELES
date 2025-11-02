<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $clientes = Customer::paginate(15);
        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'es_empresa' => ['nullable', 'boolean'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'direccion_entrega' => ['nullable', 'string', 'max:500'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);
        
        Customer::create($data);
        return redirect()->route('clientes.index')->with('ok','Cliente agregado.');
    }

    public function show(Customer $cliente)
    {
        $cliente->load([
            'orders' => function($q) {
                $q->latest()->limit(10)->with('items');
            },
            'sales' => function($q) {
                $q->latest()->limit(10);
            }
        ]);
        return view('clientes.show', compact('cliente'));
    }

    public function edit(Customer $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $r, Customer $cliente)
    {
        $data = $r->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'es_empresa' => ['nullable', 'boolean'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'direccion_entrega' => ['nullable', 'string', 'max:500'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);
        
        $cliente->update($data);
        return redirect()->route('clientes.index')->with('ok','Cliente actualizado.');
    }

    public function destroy(Customer $cliente)
    {
        $cliente->delete();
        return back()->with('ok','Cliente eliminado.');
    }
}
