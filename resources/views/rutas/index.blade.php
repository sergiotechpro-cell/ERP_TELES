@extends('layouts.erp')
@section('title','Rutas y Entregas')

@section('content')
<x-flash />

<div class="container-fluid">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h2 class="fw-bold mb-0">
      <i class="bi bi-map me-2"></i> Rutas y Entregas
    </h2>
    <a href="{{ route('pedidos.index') }}" class="btn btn-light">
      <i class="bi bi-arrow-left"></i> Volver a Pedidos
    </a>
  </div>

  @if($pedidos->count() === 0)
    <x-empty
      icon="bi-map"
      title="Sin pedidos para entrega"
      text="Crea pedidos con dirección de entrega para visualizar rutas."
    />
  @else
    <div class="card border-0 shadow-sm" style="border-radius:16px;">
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width:70px">#</th>
              <th>Dirección de entrega</th>
              <th>Estado</th>
              <th>Productos</th>
              <th style="width:170px">Creado</th>
              <th class="text-end" style="width:180px">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @foreach($pedidos as $pedido)
              <tr>
                <td class="fw-semibold">#{{ $pedido->id }}</td>
                <td class="text-truncate" style="max-width: 300px">
                  {{ $pedido->direccion_entrega ?: '—' }}
                </td>
                <td>
                  <x-status-badge :status="$pedido->estado" />
                </td>
                <td>
                  <small class="text-secondary">
                    {{ $pedido->items->count() }} producto(s)
                  </small>
                </td>
                <td>{{ $pedido->created_at?->format('d M Y H:i') }}</td>
                <td class="text-end">
                  <div class="btn-group" role="group">
                    @if($mapsKey)
                      <a href="{{ route('pedidos.ruta', $pedido) }}"
                         class="btn btn-sm btn-primary"
                         title="Ver ruta en mapa">
                        <i class="bi bi-map"></i> Ver Ruta
                      </a>
                    @else
                      <span class="btn btn-sm btn-secondary" 
                            title="Configure GOOGLE_MAPS_API_KEY en .env para ver rutas">
                        <i class="bi bi-exclamation-triangle"></i> API Key no configurada
                      </span>
                    @endif
                    <a href="{{ route('pedidos.show', $pedido) }}"
                       class="btn btn-sm btn-outline-secondary"
                       title="Ver detalles">
                      <i class="bi bi-eye"></i>
                    </a>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="card-body">
        {{ $pedidos->withQueryString()->links() }}
      </div>
    </div>
  @endif
</div>
@endsection

