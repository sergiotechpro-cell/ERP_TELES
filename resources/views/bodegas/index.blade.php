@extends('layouts.erp')
@section('title','Bodegas y Almacenes')

@section('content')
<x-flash />

<div class="container-fluid">
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <h2 class="fw-bold mb-0"><i class="bi bi-building me-2"></i> Bodegas y Almacenes</h2>
    <a href="{{ route('bodegas.create') }}" class="btn btn-primary">
      <i class="bi bi-plus-lg"></i> Nueva Bodega
    </a>
  </div>

  @if($bodegas->count() === 0)
    <x-empty 
      icon="bi-building" 
      title="Sin bodegas registradas" 
      text="Crea tu primera bodega para empezar a gestionar inventario por ubicación."
    />
  @else
    <div class="card border-0 shadow-sm" style="border-radius:16px;">
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Nombre</th>
              <th>Dirección</th>
              <th>Coordenadas</th>
              <th>Productos</th>
              <th class="text-end">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @foreach($bodegas as $bodega)
              <tr>
                <td class="fw-semibold">{{ $bodega->nombre }}</td>
                <td class="text-truncate" style="max-width: 300px">
                  {{ $bodega->direccion ?? '—' }}
                </td>
                <td>
                  @if($bodega->lat && $bodega->lng)
                    <small class="text-secondary">
                      {{ number_format($bodega->lat, 6) }}, {{ number_format($bodega->lng, 6) }}
                    </small>
                  @else
                    <span class="badge text-bg-warning">Sin coordenadas</span>
                  @endif
                </td>
                <td>
                  <span class="badge text-bg-light">
                    {{ $bodega->warehouse_products_count ?? 0 }} producto(s)
                  </span>
                </td>
                <td class="text-end">
                  <div class="btn-group" role="group">
                    <a href="{{ route('bodegas.edit', $bodega) }}" 
                       class="btn btn-sm btn-outline-primary" 
                       title="Editar">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('bodegas.destroy', $bodega) }}" 
                          method="POST" 
                          class="d-inline"
                          onsubmit="return confirm('¿Eliminar bodega {{ $bodega->nombre }}?')">
                      @csrf @method('DELETE')
                      <button class="btn btn-sm btn-outline-danger" title="Eliminar">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="card-body">
        {{ $bodegas->links() }}
      </div>
    </div>

    <div class="mt-3">
      <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        <strong>Nota:</strong> El sistema usará automáticamente la primera bodega con coordenadas como punto de origen para calcular rutas de entrega.
      </div>
    </div>
  @endif
</div>
@endsection

