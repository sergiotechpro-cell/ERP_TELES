@extends('layouts.erp')
@section('title','Pedidos')

@section('content')
<x-flash />

<div class="container-fluid">
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <h2 class="fw-bold mb-0">
      <i class="bi bi-bag-check me-2"></i> Pedidos
    </h2>

    <div class="d-flex gap-2">
      {{-- barra de búsqueda opcional, si no la usas puedes quitarla --}}
      <x-searchbar :action="route('pedidos.index')" />

      <a href="{{ route('pedidos.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Nuevo Pedido
      </a>
    </div>
  </div>

  @if($pedidos->count() === 0)
    <x-empty
      icon="bi-receipt"
      title="Sin pedidos"
      text="Crea tu primer pedido para comenzar."
    />
  @else
    <div class="card border-0 shadow-sm" style="border-radius:16px;">
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width:70px">#</th>
              <th>Cliente</th>
              <th>Dirección</th>
              <th style="width:140px">Estado</th>
              <th style="width:170px">Creado</th>
              <th class="text-end" style="width:220px">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @foreach($pedidos as $o)
              <tr>
                <td class="fw-semibold">#{{ $o->id }}</td>
                <td>{{ $o->customer->nombre ?? '—' }}</td>
                <td class="text-truncate" style="max-width: 340px">
                  {{ $o->direccion_entrega ?: '—' }}
                </td>
                <td>
                  <x-status-badge :status="$o->estado" />
                </td>
                <td>{{ $o->created_at?->format('d M Y H:i') }}</td>
                <td class="text-end">
                  <div class="btn-group" role="group">
                    <a href="{{ route('pedidos.show',$o) }}"
                       class="btn btn-sm btn-outline-secondary"
                       title="Ver detalles">
                      <i class="bi bi-eye"></i>
                    </a>

                    {{-- Botón de ruta (mapa) — requiere que el pedido tenga lat/lng --}}
                    <a href="{{ route('pedidos.ruta',$o) }}"
                       class="btn btn-sm btn-outline-primary"
                       title="Ver ruta en mapa">
                      <i class="bi bi-map"></i>
                    </a>

                    {{-- (Opcional) imprimir / pdf
                    <a href="{{ route('pedidos.print',$o) }}"
                       class="btn btn-sm btn-outline-info"
                       title="Imprimir">
                      <i class="bi bi-printer"></i>
                    </a>
                    --}}

                    <form action="{{ route('pedidos.destroy',$o) }}"
                          method="POST"
                          class="d-inline"
                          onsubmit="return confirm('¿Eliminar pedido?');">
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
        {{ $pedidos->withQueryString()->links() }}
      </div>
    </div>
  @endif
</div>
@endsection
