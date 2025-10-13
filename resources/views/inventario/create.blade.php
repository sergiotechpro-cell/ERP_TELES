@extends('layouts.erp')
@section('title','Nuevo producto')

@section('content')
<x-flash />

<div class="container" style="max-width: 980px;">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="fw-bold mb-0"><i class="bi bi-plus-square me-2"></i>Nuevo producto</h3>
    <div class="d-flex gap-2">
      <a href="{{ route('inventario.warehouses.create') }}" class="btn btn-light">
        <i class="bi bi-building-add"></i> Nueva bodega
      </a>
      <a href="{{ route('inventario.index') }}" class="btn btn-light">
        <i class="bi bi-arrow-left"></i> Volver
      </a>
    </div>
  </div>

  @if($bodegas->isEmpty())
    <div class="alert alert-warning d-flex align-items-center" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      <div>
        No hay bodegas aún. Crea al menos una bodega para cargar stock inicial. 
        <a href="{{ route('inventario.warehouses.create') }}" class="alert-link">Crear bodega</a>.
      </div>
    </div>
  @endif

  <div class="card border-0 shadow-sm" style="border-radius:16px;">
    <div class="card-body">
      <form method="POST" action="{{ route('inventario.store') }}" class="row g-3">
        @csrf

        <div class="col-12">
          <label class="form-label">Descripción</label>
          <input name="descripcion" class="form-control" required placeholder="TV 55'' 4K UHD">
        </div>

        <div class="col-md-4">
          <label class="form-label">Costo unitario</label>
          <input name="costo_unitario" type="number" step="0.01" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Precio de venta (menudeo)</label>
          <input name="precio_venta" type="number" step="0.01" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Precio mayoreo (opcional)</label>
          <input name="precio_mayoreo" type="number" step="0.01" class="form-control">
        </div>

        <div class="col-md-4">
          <label class="form-label">Lista de precios</label>
          <select name="price_tier" class="form-select">
            <option value="menudeo">Menudeo</option>
            <option value="mayoreo">Mayoreo</option>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">Bodega origen</label>
          <select name="bodega_id" class="form-select" @if($bodegas->isEmpty()) disabled @endif>
            <option value="">Selecciona bodega...</option>
            @foreach($bodegas as $b)
              <option value="{{ $b->id }}">{{ $b->nombre }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">Cantidad inicial</label>
          <input name="cantidad" type="number" min="0" class="form-control" value="0" @if($bodegas->isEmpty()) disabled @endif>
          <small class="text-secondary">Si es &gt; 0, se crearán números de serie automáticamente.</small>
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
