@extends('layouts.erp')
@section('title','Clientes')

@section('content')
<x-flash />

<div class="container-fluid">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h2 class="fw-bold mb-0"><i class="bi bi-people me-2"></i> Clientes</h2>
    <a href="{{ route('clientes.create') }}" class="btn btn-primary"><i class="bi bi-plus"></i> Nuevo</a>
  </div>

  @if($clientes->count()===0)
    <x-empty icon="bi-person" title="Sin clientes" text="Agrega un cliente para iniciar." />
  @else
    <div class="card border-0 shadow-sm" style="border-radius:16px;">
      <div class="table-responsive">
        <table class="table align-middle">
          <thead class="table-light">
            <tr><th>Nombre</th><th>Empresa</th><th>Teléfono</th><th>Email</th><th>Dirección</th><th class="text-end">—</th></tr>
          </thead>
          <tbody>
          @foreach($clientes as $c)
            <tr>
              <td>{{ $c->nombre }}</td>
              <td>{{ $c->es_empresa ? 'Sí' : 'No' }}</td>
              <td>{{ $c->telefono ?? '—' }}</td>
              <td>{{ $c->email ?? '—' }}</td>
              <td class="text-truncate" style="max-width:360px">{{ $c->direccion_entrega ?? '—' }}</td>
              <td class="text-end">
                <a href="{{ route('clientes.edit',$c) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                <form action="{{ route('clientes.destroy',$c) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar cliente?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
      <div class="card-body">{{ $clientes->links() }}</div>
    </div>
  @endif
</div>
@endsection
