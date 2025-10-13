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
        Customer::create($r->only(['nombre','es_empresa','telefono','direccion_entrega','email']));
        return redirect()->route('clientes.index')->with('ok','Cliente agregado.');
    }

    public function show(Customer $cliente)
    {
        return view('clientes.show', compact('cliente'));
    }

    public function edit(Customer $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $r, Customer $cliente)
    {
        $cliente->update($r->only(['nombre','es_empresa','telefono','direccion_entrega','email']));
        return redirect()->route('clientes.index')->with('ok','Cliente actualizado.');
    }

    public function destroy(Customer $cliente)
    {
        $cliente->delete();
        return back()->with('ok','Cliente eliminado.');
    }
}
