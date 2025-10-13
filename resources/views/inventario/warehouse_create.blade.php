@extends('layouts.erp')
@section('title','Nueva bodega')

@section('content')
<x-flash />

<div class="container" style="max-width: 820px;">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="fw-bold mb-0">
      <i class="bi bi-building-add me-2"></i>Nueva bodega
    </h3>
    <a href="{{ route('inventario.index') }}" class="btn btn-light">
      <i class="bi bi-arrow-left"></i> Volver
    </a>
  </div>

  <div class="card border-0 shadow-sm" style="border-radius:16px;">
    <div class="card-body">
      <form method="POST" action="{{ route('inventario.warehouses.store') }}" class="row g-3">
        @csrf

        <div class="col-md-6">
          <label class="form-label">Nombre de la bodega</label>
          <input name="nombre" class="form-control" required placeholder="Bodega Centro">
        </div>

        <div class="col-md-6">
          <label class="form-label">Dirección (opcional)</label>
          <input name="direccion" class="form-control" placeholder="Calle, número, colonia, ciudad">
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
          <a href="{{ route('inventario.index') }}" class="btn btn-light">Cancelar</a>
          <button class="btn btn-primary">
            <i class="bi bi-check2-circle"></i> Guardar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
