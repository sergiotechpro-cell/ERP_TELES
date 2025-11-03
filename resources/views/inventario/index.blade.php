@extends('layouts.erp')
@section('title','Inventario')

@section('content')
<x-flash />

<div class="container-fluid">
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <h2 class="fw-bold mb-0"><i class="bi bi-boxes me-2"></i> Inventario</h2>
    <div class="d-flex gap-2">
      <x-searchbar :action="route('inventario.index')" />
      <a href="{{ route('inventario.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Nuevo Producto
      </a>
    </div>
  </div>

  @if($productos->count() === 0)
    <x-empty icon="bi-inboxes" title="Aún no tienes productos" text="Crea tu primer SKU para empezar a gestionar inventario." />
  @else
    <div class="card border-0 shadow-sm" style="border-radius:16px;">
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Producto</th>
              <th>Costo</th>
              <th>Precio</th>
              <th>Stock Total</th>
              <th class="text-end">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @foreach($productos as $p)
              <tr>
                <td class="fw-medium">{{ $p->descripcion }}</td>
                <td>${{ number_format($p->costo_unitario,2) }}</td>
                <td>${{ number_format($p->precio_venta,2) }}</td>
                <td>
                  <span class="badge bg-primary">{{ $p->warehouses->sum(fn($w)=>$w->pivot->stock) }} unidades</span>
                </td>
                <td class="text-end">
                  <a class="btn btn-sm btn-outline-secondary" href="{{ route('inventario.show',$p) }}">
                    <i class="bi bi-eye"></i>
                  </a>
                  <a class="btn btn-sm btn-outline-primary" href="{{ route('inventario.edit',$p) }}">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <form action="{{ route('inventario.destroy',$p) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('¿Eliminar producto?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="card-body">
        {{ $productos->withQueryString()->links() }}
      </div>
    </div>
  @endif
</div>
@endsection
