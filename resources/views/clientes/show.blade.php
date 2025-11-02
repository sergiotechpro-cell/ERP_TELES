@extends('layouts.erp')
@section('title','Detalle de cliente')

@section('content')
<x-flash />

<div class="container-fluid">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="fw-bold mb-0"><i class="bi bi-person me-2"></i> {{ $cliente->nombre }}</h3>
    <div class="d-flex gap-2">
      <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-primary">
        <i class="bi bi-pencil"></i> Editar
      </a>
      <form action="{{ route('clientes.destroy', $cliente) }}" method="POST" 
            onsubmit="return confirm('¿Eliminar cliente {{ $cliente->nombre }}?')">
        @csrf @method('DELETE')
        <button class="btn btn-outline-danger" type="submit">
          <i class="bi bi-trash"></i> Eliminar
        </button>
      </form>
      <a href="{{ route('clientes.index') }}" class="btn btn-light">
        <i class="bi bi-arrow-left"></i> Volver
      </a>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-lg-7">
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2"></i>Información del cliente</h6>
          
          <div class="row g-3">
            <div class="col-md-6">
              <div class="text-secondary small">Nombre / Razón social</div>
              <div class="fw-medium">{{ $cliente->nombre }}</div>
            </div>
            
            <div class="col-md-6">
              <div class="text-secondary small">Tipo</div>
              <div class="fw-medium">
                @if($cliente->es_empresa)
                  <span class="badge text-bg-primary">Empresa</span>
                @else
                  <span class="badge text-bg-secondary">Persona</span>
                @endif
              </div>
            </div>

            @if($cliente->telefono)
            <div class="col-md-6">
              <div class="text-secondary small">Teléfono</div>
              <div class="fw-medium">{{ $cliente->telefono }}</div>
            </div>
            @endif

            @if($cliente->email)
            <div class="col-md-6">
              <div class="text-secondary small">Email</div>
              <div class="fw-medium">{{ $cliente->email }}</div>
            </div>
            @endif

            @if($cliente->direccion_entrega)
            <div class="col-12">
              <div class="text-secondary small">Dirección de entrega</div>
              <div class="fw-medium">{{ $cliente->direccion_entrega }}</div>
            </div>
            @endif
          </div>

          <hr>

          <div class="row g-2 text-center">
            <div class="col-md-4">
              <div class="p-3 bg-light rounded">
                <div class="display-6 fw-bold text-primary">{{ $cliente->orders->count() }}</div>
                <div class="text-secondary small">Pedidos</div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="p-3 bg-light rounded">
                <div class="display-6 fw-bold text-success">
                  @php
                    $totalPedidos = $cliente->orders->sum(function($o) { 
                      return $o->items->sum(function($i) { 
                        return $i->precio_unitario * $i->cantidad; 
                      }); 
                    });
                    $totalVentas = $cliente->sales->sum('total');
                    $totalHistorico = $totalPedidos + $totalVentas;
                  @endphp
                  ${{ number_format($totalHistorico, 2) }}
                </div>
                <div class="text-secondary small">Total histórico</div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="p-3 bg-light rounded">
                <div class="display-6 fw-bold text-info">
                  {{ $cliente->orders->where('estado', 'entregado')->count() }}
                </div>
                <div class="text-secondary small">Entregados</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-5">
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <h6 class="fw-bold mb-3"><i class="bi bi-cart-check me-2"></i>Pedidos recientes</h6>
          
          @if($cliente->orders->count() === 0)
            <x-empty icon="bi-cart" title="Sin pedidos" text="Este cliente aún no tiene pedidos registrados." />
          @else
            <div class="list-group list-group-flush">
              @foreach($cliente->orders as $pedido)
                <div class="list-group-item px-0">
                  <div class="d-flex justify-content-between align-items-start">
                    <div>
                      <div class="fw-semibold">Pedido #{{ $pedido->id }}</div>
                      <div class="text-secondary small">{{ $pedido->created_at->format('d M Y H:i') }}</div>
                      <div class="mt-1">
                        <x-status-badge :status="$pedido->estado" />
                      </div>
                    </div>
                    <div class="text-end">
                      <div class="fw-bold">
                        ${{ number_format($pedido->items->sum(function($i) { return $i->precio_unitario * $i->cantidad; }), 2) }}
                      </div>
                    </div>
                  </div>
                  <div class="mt-2">
                    <a href="{{ route('pedidos.show', $pedido) }}" class="btn btn-sm btn-outline-primary">
                      <i class="bi bi-eye"></i> Ver detalle
                    </a>
                  </div>
                </div>
              @endforeach
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

