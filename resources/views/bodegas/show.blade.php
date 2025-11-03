@extends('layouts.erp')
@section('title','Detalles de Bodega')

@section('content')
<x-flash />

<div class="container-fluid">
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <h2 class="fw-bold mb-0">
      <i class="bi bi-building me-2"></i>{{ $bodega->nombre }}
    </h2>
    <div class="d-flex gap-2">
      <a href="{{ route('bodegas.edit', $bodega) }}" class="btn btn-outline-primary">
        <i class="bi bi-pencil"></i> Editar
      </a>
      <a href="{{ route('bodegas.index') }}" class="btn btn-light">
        <i class="bi bi-arrow-left"></i> Volver
      </a>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-md-8">
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <h5 class="fw-bold mb-3">Información General</h5>
          
          <div class="mb-3">
            <label class="text-secondary small">Nombre</label>
            <div class="fw-semibold">{{ $bodega->nombre }}</div>
          </div>

          <div class="mb-3">
            <label class="text-secondary small">Dirección</label>
            <div class="fw-semibold">{{ $bodega->direccion ?? '—' }}</div>
          </div>

          @if($bodega->lat && $bodega->lng)
          <div class="mb-3">
            <label class="text-secondary small">Coordenadas</label>
            <div class="fw-semibold">
              {{ number_format($bodega->lat, 6) }}, {{ number_format($bodega->lng, 6) }}
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <h5 class="fw-bold mb-3">Estadísticas</h5>
          
          <div class="mb-3">
            <label class="text-secondary small">Productos en bodega</label>
            <div class="display-6 fw-bold">{{ $bodega->warehouse_products_count ?? 0 }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

