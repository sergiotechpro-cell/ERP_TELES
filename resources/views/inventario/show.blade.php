@extends('layouts.erp')
@section('title','Detalle de producto')

@section('content')
<x-flash />

<div class="container-fluid">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="fw-bold mb-0"><i class="bi bi-box-seam me-2"></i> {{ $producto->descripcion }}</h3>
    <a href="{{ route('inventario.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Volver</a>
  </div>

  <div class="row g-3">
    <div class="col-12 col-lg-6">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
        <div class="card-body">
          <h6 class="text-secondary">Información</h6>
          <div class="row mt-2">
            <div class="col-6">
              <div class="text-secondary">Costo unitario</div>
              <div class="fw-bold">${{ number_format($producto->costo_unitario,2) }}</div>
            </div>
            <div class="col-6">
              <div class="text-secondary">Precio venta</div>
              <div class="fw-bold">${{ number_format($producto->precio_venta,2) }}</div>
            </div>
          </div>
          <hr>
          <h6 class="text-secondary">Stock por bodega</h6>
          @forelse($producto->warehouses as $w)
            <div class="d-flex justify-content-between py-1">
              <span>{{ $w->nombre }}</span>
              <span class="badge text-bg-light">{{ $w->pivot->stock }}</span>
            </div>
          @empty
            <p class="text-secondary mb-0">Sin stock registrado.</p>
          @endforelse
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
        <div class="card-body">
          <h6 class="text-secondary">Números de serie</h6>
          @php
            $seriales = $producto->warehouseProducts()->with('serials')->get()->flatMap->serials;
          @endphp
          @if($seriales->isEmpty())
            <x-empty icon="bi-upc-scan" title="Sin números de serie" text="Registra entradas para generar series." />
          @else
            <div class="list-group list-group-flush">
              @foreach($seriales as $sn)
                <div class="list-group-item d-flex align-items-center justify-content-between">
                  <div>
                    <span class="fw-medium">{{ $sn->numero_serie }}</span>
                    <small class="text-secondary ms-2">({{ $sn->estado }})</small>
                  </div>
                  <span class="badge text-bg-light">ID: {{ $sn->id }}</span>
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
