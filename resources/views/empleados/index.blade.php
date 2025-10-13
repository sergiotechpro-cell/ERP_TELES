@extends('layouts.erp')
@section('title','Empleados')

@section('content')
<x-flash />
<div class="container-fluid">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h2 class="fw-bold mb-0"><i class="bi bi-person-badge me-2"></i> Empleados</h2>
    <a href="{{ route('empleados.create') }}" class="btn btn-primary"><i class="bi bi-plus"></i> Nuevo</a>
  </div>

  @if($empleados->count()===0)
    <x-empty icon="bi-person" title="Sin empleados" text="Crea el primer empleado." />
  @else
    <div class="card border-0 shadow-sm" style="border-radius:16px;">
      <div class="table-responsive">
        <table class="table align-middle">
          <thead class="table-light"><tr><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Dirección</th><th class="text-end">—</th></tr></thead>
          <tbody>
            @foreach($empleados as $e)
              <tr>
                <td>{{ $e->user->name }}</td>
                <td>{{ $e->user->email }}</td>
                <td>{{ $e->telefono ?? '—' }}</td>
                <td class="text-truncate" style="max-width:360px">{{ $e->direccion ?? '—' }}</td>
                <td class="text-end">
                  <a href="{{ route('empleados.edit',$e) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                  <form action="{{ route('empleados.destroy',$e) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar empleado?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="card-body">{{ $empleados->links() }}</div>
    </div>
  @endif
</div>
@endsection
